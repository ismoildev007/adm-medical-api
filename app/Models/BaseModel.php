<?php

namespace App\Models;

use App\Jobs\ModelChangedLogJob;
use App\Services\Action;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    public static function boot()
    {
        parent::boot();

        // self::saved(function (Model $model) {
        //     ModelChangedLogJob::dispatch(static::makeLogData($model));
        // });

        // self::deleted(function (Model $model) {
        //     ModelChangedLogJob::dispatch(static::makeLogData($model));
        // });
    }

    protected static function makeLogData(Model $model)
    {
        $data = [
            'timestamp' => format_date(),
            'user_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => request()->user()?->id,
            'user_username' => request()->user()?->username,
            'user_firstname' => request()->user()?->firstname,
            'user_lastname' => request()->user()?->lastname,
            'request_method' => request()->getMethod(),
            'request_endpoint' => request()->getPathInfo(),
            'request_body' => request()->request->all(),
            'request_query' => request()->query->all(),
            'model_table' => $model->getTable(),
            'model_old' => $model->getOriginal(),
            'model_new' => $model->getDirty()
        ];
        return $data;
    }


    /**
     * @param (\Closure(\App\Services\Action, \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder, \Illuminate\Http\Request)) $callback
     * @param bool $withTransaction
     */
    public static function action($callback, $withTransaction = false)
    {
        return Action::from(new static, $callback, $withTransaction);
    }


    public static function find($id, $columns = ['*']): ?static
    {
        return self::query()->where('id', $id)->first($columns);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
