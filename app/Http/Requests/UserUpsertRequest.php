<?php

namespace App\Http\Requests;

use App\Rules\CheckUsernameRule;


class UserUpsertRequest extends BaseRequest
{
    public function rules()
    {
        $required = ($this->isMethod('put')) ? 'nullable' : 'required';
        return [
            'username' => [$required, 'string', new CheckUsernameRule],
            'roles' => ['nullable', 'array'],
            'firstname' => ['nullable', 'string'],
            'lastname' => ['nullable', 'string']
        ];
    }
}
