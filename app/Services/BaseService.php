<?php

namespace App\Services;

use App\Console\Helpers\ErrorHelper;
use Closure;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;

/** @template TModel */
abstract class BaseService
{
    /** @var TModel */
    protected $model;

    /** Resource class name */
    protected $resourceClass = null;

    /** Relations for eager loading */
    public array $relations = [];

    /** Conditions using "where" clause */
    public array $conditions = [];

    public array $relationLikableFields = [];

    /** Filtering with like fields */
    public array $likableFields = [];

    /** Filtering with equaling */
    public array $equalableFields = [];

    /** Filtering by value interval*/
    public array $numericIntervalFields = [];

    /** Filtering by date interval */
    public array $dateIntervalFields = [];

    /** Sorting by column */
    public array $defaultOrder = [['column' => 'id', 'direction' => 'desc']];

    /** Primary column  */
    protected $id = 'id';

    protected bool $joined = false;

    /**
     * @param (Closure(Action, \Illuminate\Database\Eloquent\Builder<TModel>|\Illuminate\Database\Query\Builder<TModel>, \Illuminate\Http\Request)) $callback
     * @return \App\Services\Action<TModel>
     */
    public function action($callback = null)
    {
        return Action::from($this->model, $callback);
    }

    public function message($messsage, $status = 500)
    {
        $status = (int)$status;
        if ($status < 200 || $status > 600) $status = 500;
        return response()->json(['message' => ErrorHelper::getMessage($messsage)], $status);
    }

    /** @return TModel|null */
    public function findBy($key, $value, $exception = null)
    {
        $model = $this->model->query()->where($key, $value)->first();
        return $model ?? ($exception ? (throw $exception) : null);
    }

    /** @return TModel|null */
    public function findById($value, $exception = null)
    {
        return $this->findBy('id', $value, $exception);
    }

    /******************************************** MAIN SECTION START ************************************************/

    /** List action */
    public function index()
    {
        if (request()->query->has('report')) {
            return $this->reportable(fn($item) => $item);
        }
        return $this->action(function ($self, $query, $request) {
            $query->with($this->relations);
            $this->specialFilter($query, $request);
            $this->specialSort($query, $request);
            $this->sort($query, $request);
            $this->filterAndSearch($query, $request);
            if (($pg = $request->get('pg')) && $pg == 'list') {
                $self->setResult($this->toResource($query->get(), true));
            } else {
                $data = $query->paginate(perPage: $request->get('rows', 10), page: $request->get('page', 1));
                $self->setResult($this->toResource($data, true));
            }
        })->onFailed(function ($th) {
            Log::error($th->getMessage() . '. ' . static::class . "::index()");
        });
    }

    public function reportable(Closure $callback)
    {
        /** @var \Illuminate\Database\Eloquent\Builder<TModel>|\Illuminate\Database\Query\Builder<TModel> */
        $query = $this->model->query();
        $query->with($this->relations);
        $this->specialFilter($query, request());
        $this->specialSort($query, request());
        $this->sort($query, request());
        $this->filterAndSearch($query, request());

        return LazyCollection::make(function () use ($query, $callback) {
            $count = $query->count();
            $limit = 400;
            $page = 0;
            while ($count > $limit) {
                $data = $query
                    ->limit($limit)
                    ->offset($page * $limit)
                    ->get();
                foreach ($data as $item) {
                    yield $callback($item);
                }
                $count -= $limit;
                $page++;
            }
            if ($count > 0) {
                $data = $query
                    ->limit($count)
                    ->offset($page * $limit)
                    ->get();
                foreach ($data as $item) {
                    yield $callback($item);
                }
            }
        });
    }

    /** Show action */
    public function show(int|string $id)
    {
        return $this->action(function ($self, $query, $request) use ($id) {
            $query->with($this->relations);
            if ($model = $query->find($id)) {
                $self->found($model);
                $self->setResult($this->toResource($model));
            } else {
                $messsage = Str::singular($this->model->getTable()) . ' is not found';
                $self->setResult(response()->json(data: ['message' => ErrorHelper::getMessage($messsage)], status: 404));
            }
        })
            ->onFailed(function ($th) use ($id) {
                Log::error($th->getMessage() . '. ' . static::class . "::show($id)");
            });
    }

    /** Store action */
    public function store(array $data)
    {
        return $this->action()
            ->transaction(function ($self, $query, $request) use ($data) {
                if ($self->checkExistsResolve()) return;
                $data['created_by'] = $request?->user()?->id;
                $model = $query->create($data);
                $self->succed($model, $data);
                $self->setResult($model);
            })
            ->onFailed(function ($th) use ($data) {
                Log::error($th->getMessage() . '. ' . static::class . '::store(' . json_encode($data) . ')');
            });
    }

    /** Update action */
    public function update(int|string $id, array $data)
    {
        return $this->action()
            ->transaction(function ($self, $query, $request) use ($id, $data) {
                if ($self->checkExistsResolve()) return;
                $query->with($this->relations);
                if (! ($model = $query->find($id))) {
                    $messsage = Str::singular($this->model->getTable()) . ' is not found';
                    return $self->setResult(response()->json(data: ['message' => ErrorHelper::getMessage($messsage)], status: 404));
                }
                $data['updated_by'] = $request?->user()?->id;
                $self->found($model, $data, $id);
                $model->update($data);
                $model->refresh();
                $self->succed($model, $data);
                $self->setResult($model);
            })
            ->onFailed(function ($th) use ($id, $data) {
                Log::error($th->getMessage() . '. ' . static::class . "::update($id, " . json_encode($data) . ')');
            });
    }

    /** Delete action */
    public function destroy(int|string $id)
    {
        return $this->action()
            ->transaction(function ($self, $query, $request) use ($id) {
                $query->with($this->relations);
                if (! ($model = $query->find($id))) {
                    $messsage = Str::singular($this->model->getTable()) . ' is not found';
                    return $self->setResult(response()->json(data: ['message' => ErrorHelper::getMessage($messsage)], status: 404));
                }
                $self->found($model, $id);
                $model->delete();
                $self->succed($model);
                $self->setResult($model);
            })
            ->onFailed(function ($th) use ($id) {
                Log::error($th->getMessage() . '. ' . static::class . "::delete($id)");
            });
    }

    /******************************************** MAIN SECTION END ************************************************/



    /******************************************** RESOURCE SECTION START ************************************************/

    /** Converts data to resource class */
    protected function toResource($data, $isCollection = false): mixed
    {
        try {
            if ($data instanceof LengthAwarePaginator) {
                return $this->resolvePagination($data, $this->resourceClass);
            }
            if ($this->resourceClass) {
                if ($isCollection) return $this->resourceClass::collection($data);
                return $this->resourceClass::make($data);
            } else return $data;
        } catch (\Throwable $th) {
            notify_error($th, __METHOD__, 'Making resource error');
            return response()->json(data: ['message' => 'Resource error. ' . $th->getMessage()], status: 500);
        }
    }

    public function withResource(string $resourceClass): static
    {
        $this->resourceClass = $resourceClass;
        return $this;
    }

    /** @param  \Illuminate\Pagination\LengthAwarePaginator $data*/
    protected function resolvePagination($data, $resourceClass = null)
    {
        return [
            'data' => $resourceClass ? $resourceClass::collection($data->getCollection()) : $data->getCollection(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'first_page_url' => $data->url(1),
                'from' => $data->firstItem(),
                'last_page' => $data->lastPage(),
                'last_page_url' => $data->url($data->lastPage()),
                'links' => $data->linkCollection()->toArray(),
                'next_page_url' => $data->nextPageUrl(),
                'path' => $data->path(),
                'per_page' => $data->perPage(),
                'prev_page_url' => $data->previousPageUrl(),
                'to' => $data->lastItem(),
                'total' => $data->total(),
            ]
        ];
    }

    /******************************************** RESOURCE SECTION END ************************************************/


    /******************************************** action SECTION START ************************************************/
    /** For eager loading relations */
    public function withRelations(array $relations = []): static
    {
        foreach ($relations as $key => $value) {
            $this->relations[$key] = $value;
        }
        $this->relations = $this->parseToRelation($this->relations);
        return $this;
    }

    /** 
     * Adding new sort rules in child service class list action
     * @param \Illuminate\Database\Eloquent\Builder<TModel>|\Illuminate\Database\Query\Builder<TModel> $builder
     * @param \Illuminate\Http\Request $request
     */
    public function specialSort(&$builder, $request): void {}

    /** 
     * Adding new filter rules in child service class list action
     * @param \Illuminate\Database\Eloquent\Builder<TModel>|\Illuminate\Database\Query\Builder<TModel> $builder
     * @param \Illuminate\Http\Request $request
     */
    public function specialFilter(&$builder, $request): void {}

    /** 
     * Sort in list action
     * @param \Illuminate\Database\Eloquent\Builder<TModel>|\Illuminate\Database\Query\Builder<TModel> $builder
     * @param \Illuminate\Http\Request $request
     */
    protected function sort(&$builder, $request): static
    {
        if ($sort = $request->get('sort')) {
            if (str_contains($sort, '.')) {
                $this->joined = true;
                $desc = str_starts_with($sort, '-');
                $relations = explode('.', ($desc ? substr($sort, 1) : $sort));
                $column = array_pop($relations);
                $model = $this->model;
                foreach ($relations as $relation) {
                    $r = $model->{$relation}();
                    $related = $r->getRelated();
                    $table = $related->getTable();
                    $first = $table . '.' . $r->getOwnerKeyName();
                    $second = $model->getTable() . '.' . $r->getForeignKeyName();
                    $builder->leftJoin($table, $first, '=', $second);
                    $model = $related;
                }
                $builder->orderByRaw($table . '.' . $column . ' ' . ($desc ? 'DESC NULLS LAST' : 'ASC NULLS LAST'));
                $builder->select($this->model->getTable() . '.*');
            } else {
                foreach (explode(',', $sort) as $s) {
                    $desc = str_starts_with($s, '-');
                    $field = $desc ? substr($s, 1) : $s;
                    $builder->orderByRaw($this->model->getTable() . ".$field " . ($desc ? 'DESC NULLS LAST' : 'ASC NULLS LAST'));
                }
            }
        } else if (!empty($this->defaultOrder)) {
            foreach ($this->defaultOrder as $order) {
                $builder->orderByRaw($this->model->getTable() . '.' . $order['column'] . ' ' . $order['direction'] . ' NULLS LAST');
            }
        }
        // dd($builder->toSql());
        // dd(get_class_methods($builder));
        return $this;
    }

    /** 
     * Filter and search in list action
     * @param \Illuminate\Database\Eloquent\Builder<TModel>|\Illuminate\Database\Query\Builder<TModel> $builder
     * @param \Illuminate\Http\Request $request
     */
    protected function relationFilter(&$builder, $request): static
    {
        foreach ($this->relationLikableFields as $relationField) {
            if ($value = $request->get($relationField)) {
                $relations = explode('.', $relationField);
                $field = array_pop($relations);
                $builder->whereHas(implode('.', $relations), function ($q) use ($field, $value) {
                    $q->where($field, 'ilike', "%$value%");
                });
            }
        }
        return $this;
    }

    /** 
     * Filter and search in list action
     * @param \Illuminate\Database\Eloquent\Builder<TModel>|\Illuminate\Database\Query\Builder<TModel> $builder
     * @param \Illuminate\Http\Request $request
     */
    protected function filterAndSearch(&$builder, $request): static
    {
        // global search
        $table = $this->joined ? $this->model->getTable() . '.' : '';
        if ($s = request('s')) {
            $builder->where(function ($q) use ($table, $s) {
                foreach ($this->likableFields as $field) {
                    $q->orWhere($table . $field, 'ilike', "%$s%");
                }
            });
        }
        // likeable filters
        foreach ($this->likableFields as $lkField) {
            if ($value = $request->get($lkField)) {
                $builder->where($table . $lkField, 'ilike', "%$value%");
            }
        }
        // exact equal filters
        foreach ($this->equalableFields as $eqField) {
            if ($value = $request->get($eqField)) {
                $builder->whereIn($table . $eqField, explode(',', $value));
            }
        }
        // numeric interval filters
        foreach ($this->numericIntervalFields as $nField) {
            if (($value = $request->get($nField)) && str_contains($value, '|')) {
                $builder->where(function ($q) use ($nField, $value, $table) {
                    [$from, $to] = explode('|', $value);
                    if ($from) $q->where($table . $nField, '>=', $from);
                    if ($to) $q->where($table . $nField, '<=', $to);
                });
            }
        }
        // date interval filters
        foreach ($this->dateIntervalFields as $dField) {
            if (($value = $request->get($dField)) && str_contains($value, '|')) {
                $builder->where(function ($q) use ($dField, $value, $table) {
                    [$from, $to] = explode('|', $value);
                    if ($from) $q->where($table . $dField, '>=', $from);
                    if ($to) $q->where($table . $dField, '<=', $to);
                });
            }
        }
        $this->relationFilter($builder, $request);
        return $this;
    }

    /** Parse array value for Eloquent eager loading */
    protected function parseToRelation(array $relations = []): array
    {
        $parsed = [];
        foreach ($relations as $key => $relation) {
            if (is_string($key)) {
                if ($relation instanceof Closure) $parsed[$key] = $relation;
                else if (is_array($relation)) $parsed[$key] = $this->makeWithRelationaction($relation);
            } else $parsed[] = $relation;
        }
        return $parsed;
    }

    /** Converts array to use eager loading recursively */
    protected function makeWithRelationaction(array $relation): array|Closure
    {
        $selects = [];
        $withs = [];
        if (empty($relation)) return fn($q) => $q->select('*');
        $order = false;
        $closures = [];
        foreach ($relation as $key => $value) {
            if (is_numeric($key) && $value instanceof Closure) {
                $closures[] = $value;
                continue;
            }
            if ($key === 'order') {
                if (is_string($value) && !empty($value)) $order = $value;
                continue;
            }
            if (is_string($key)) {
                if ($value instanceof Closure) $withs[$key] = $value;
                else if (is_array($value)) $withs[$key] = $this->makeWithRelationaction($value);
            } else {
                if (str_contains($value, '|')) {
                    $selects[] = explode("|", $value)[0];
                    $order = $value;
                } else $selects[] = $value;
            }
        }
        return function ($q) use ($selects, $withs, $order, $closures) {
            if ($order) {
                $sort = explode('|', $order);
                $use = isset($sort[1]) && (strtolower($sort[1]) === 'asc' || strtolower($sort[1]) === 'desc');
                $q->orderBy($sort[0], $use ? strtolower($sort[1]) : 'asc');
            }
            if (empty($selects)) $q->select('*');
            else $q->selectRaw(implode(',', $selects));

            if (!empty($withs)) $q->with($withs);
            foreach ($closures as $closure) $closure($q);
        };
    }
    /******************************************** action SECTION END ************************************************/


    /**
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     */
    public function moveFile($file, $abs = null, $rel = null, $name = null, $format = 'Ymd_His')
    {
        $rel ??= Str::snake(class_basename($this->model));
        $abs ??= storage_path('app/public');
        $ext = $file->getClientOriginalExtension();
        if (str_contains($rel, ".$ext")) {
            $rel_array = explode('/', $rel);
            $name = array_pop($rel_array);
            $rel = implode('/', $rel_array);
        } else {
            $name = date($format) . ".$ext";
        }
        if ($file->getClientOriginalName() != 'template.xlsx') {
            $file->move("$abs/$rel", $name);
        } else {
            File::copy($file->getRealPath(), "$abs/$rel/$name");
        }
        return "$rel/$name";
    }

    /**
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder
     * @param ?string $name
     * @param string $disk
     * @return string
     */
    public function writeFile($file, $folder, $name = null, $disk = 'public')
    {
        $extension = $file->getClientOriginalExtension();
        $name = date('YmdHis') . ($name ? "$name.$extension" : $file->getClientOriginalName());
        Storage::disk($disk)->putFileAs($folder, $file, $name);
        return "$folder/$name";
    }

    public function deleteFile($path, $disk = 'public')
    {
        $path && Storage::disk($disk)->delete($path);
    }

    /**
     * @throws Exception
     */
    public function throw($messsage, $code)
    {
        throw new Exception($messsage, $code);
    }
}
