<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
{
    public function relation_counts($relations = [])
    {
        $data = [];
        foreach ($relations as $key => $relation) {
            $data[$key] = $this->{$relation}?->count();
        }
        return $data;
    }
}
