<?php

namespace App\Services;

use App\Models\Position;
use Illuminate\Pagination\LengthAwarePaginator;

class PositionService
{
    public function list(array $filters): LengthAwarePaginator
    {
        $query = Position::query()->with(['translations']);

        if ($search = $filters['s'] ?? null) {
            $query->whereHas('translations', fn($q) => $q->where('name', 'ilike', "%{$search}%"));
        }

        if ($typeId = $filters['position_type_id'] ?? null) {
            $query->where('position_type_id', $typeId);
        }

        return $query->orderBy('id')->paginate(
            perPage: $filters['rows'] ?? 20,
            page: $filters['page'] ?? 1,
        );
    }

    public function findById(int $id): ?Position
    {
        return Position::with(['translations'])->find($id);
    }
}
