<?php


namespace App\Http\Requests;

use Abdphyr\Swagger\Attributes\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest
{
    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */

    public function authorize()
    {
        return true;
    }

    protected function passedValidation()
    {
        foreach ($this->validated() as $key => $value) {
            $this->{$key} = $value;
        }
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ], 422));
    }

    public function rules()
    {
        $requestClass = new \ReflectionClass(static::class);
        $publicProperties = $requestClass->getProperties(\ReflectionProperty::IS_PUBLIC);
        $properties = array_filter($publicProperties, fn($property) => $property->getAttributes(Rule::class));
        $rules = [];
        foreach ($properties as $property) {
            $param = $property->getAttributes(Rule::class)[0];
            $rules[$property->getName()] = $param->newInstance()->rule;
        }
        return $rules;
    }
}
