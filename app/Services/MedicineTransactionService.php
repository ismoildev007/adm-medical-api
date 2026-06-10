<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\MedicineTransaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MedicineTransactionService
{
    public function list(array $filters): LengthAwarePaginator
    {
        $query = MedicineTransaction::query()->with(['medicine', 'createdBy']);

        if ($medicineId = $filters['medicine_id'] ?? null) {
            $query->where('medicine_id', $medicineId);
        }

        if ($type = $filters['type'] ?? null) {
            $query->where('type', $type);
        }

        $sortDir = strtolower($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy('created_at', $sortDir);

        return $query->paginate(
            perPage: $filters['rows'] ?? 20,
            page: $filters['page'] ?? 1,
        );
    }

    public function findById(int $id): ?MedicineTransaction
    {
        return MedicineTransaction::with(['medicine', 'createdBy'])->find($id);
    }

    public function store(array $data, int $userId): MedicineTransaction
    {
        return DB::transaction(function () use ($data, $userId) {
            $medicine = Medicine::lockForUpdate()->findOrFail($data['medicine_id']);

            $qty = (int) $data['quantity'];

            if ($data['type'] === 'income') {
                $medicine->increment('quantity', $qty);
            } else {
                if ($medicine->quantity < $qty) {
                    abort(422, "Omborda yetarli dori yo'q. Mavjud: {$medicine->quantity}");
                }
                $medicine->decrement('quantity', $qty);
            }

            return MedicineTransaction::create([
                'medicine_id' => $data['medicine_id'],
                'type'        => $data['type'],
                'quantity'    => $qty,
                'comment'     => $data['comment'] ?? null,
                'created_by'  => $userId,
            ]);
        });
    }

    public function delete(MedicineTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            // Tranzaksiya o'chirilganda miqdorni teskari qaytarish
            $medicine = Medicine::lockForUpdate()->findOrFail($transaction->medicine_id);

            if ($transaction->type === 'income') {
                $medicine->decrement('quantity', $transaction->quantity);
            } else {
                $medicine->increment('quantity', $transaction->quantity);
            }

            $transaction->delete();
        });
    }
}