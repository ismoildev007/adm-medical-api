<?php

namespace App\Services;

use App\Models\Medicine;
use Illuminate\Pagination\LengthAwarePaginator;

class MedicineService
{
    private const SORTABLE = ['name', 'quantity', 'expire_date'];

    public function list(array $filters): LengthAwarePaginator
    {
        $query = Medicine::query();

        if ($search = $filters['s'] ?? null) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        // miqdor oraliq filter: ?quantity=10|500
        if ($qty = $filters['quantity'] ?? null) {
            [$from, $to] = array_pad(explode('|', $qty), 2, null);
            if ($from !== null && $from !== '') $query->where('quantity', '>=', (int) $from);
            if ($to   !== null && $to   !== '') $query->where('quantity', '<=', (int) $to);
        }

        // srok oraliq filter: ?expire_date=2026-01-01|2026-12-31
        if ($exp = $filters['expire_date'] ?? null) {
            [$from, $to] = array_pad(explode('|', $exp), 2, null);
            if ($from !== null && $from !== '') $query->where('expire_date', '>=', $from);
            if ($to   !== null && $to   !== '') $query->where('expire_date', '<=', $to);
        }

        $sortBy  = in_array($filters['sort_by'] ?? '', self::SORTABLE) ? $filters['sort_by'] : 'name';
        $sortDir = strtolower($filters['sort_dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate(
            perPage: $filters['rows'] ?? 20,
            page: $filters['page'] ?? 1,
        );
    }

    public function findById(int $id): ?Medicine
    {
        return Medicine::find($id);
    }

    public function store(array $data): Medicine
    {
        return Medicine::create($data);
    }

    public function update(Medicine $medicine, array $data): Medicine
    {
        $medicine->update($data);
        return $medicine->fresh();
    }

    public function delete(Medicine $medicine): void
    {
        $medicine->delete();
    }
}