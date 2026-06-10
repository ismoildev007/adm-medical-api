<?php

namespace App\Http\Requests\Warehouse;

use App\Http\Requests\BaseRequest;

class StoreMedicineTransactionRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'medicine_id' => 'required|integer|exists:medicines,id',
            'type'        => 'required|in:income,outcome,write_off',
            'quantity'    => 'required|integer|min:1',
            'comment'     => 'nullable|string',
        ];
    }
}
