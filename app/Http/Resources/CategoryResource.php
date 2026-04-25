<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ulid,
            'name' => $this->name,
            'type' => $this->type,
            'additional_info' => $this->additional_info,
            'parent_id' => $this->when($this->parent, $this->parent?->ulid),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

