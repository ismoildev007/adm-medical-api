<?php

namespace App\Http\Requests\Warehouse;

use App\Http\Requests\BaseRequest;

class StoreMedicineRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
c            'name'        => 'required|string|max:255',
            'unit'        => 'required|string|max:50',
            'quantity'    => 'required|integer|min:0',
            'expire_date' => 'nullable|date',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ];
    }
}