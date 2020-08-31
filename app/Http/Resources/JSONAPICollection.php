<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\MissingValue;
use Illuminate\Http\Resources\Json\ResourceCollection;

class JSONAPICollection extends ResourceCollection
{
    public $collects = JSONAPIResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
            'included' => $this->mergeIncludedRelations($request)
        ];
    }

    /**
     * merge included relations to this collection
     * 
     * @param \Illuminate\Http\Request  $request
     * 
     * @return \Illuminate\Support\Collection $includes or missing value 
     */
    private function mergeIncludedRelations($request)
    {
        $includes = $this->collection->flatMap->included($request)->unique()->values();
        return $includes->isNotEmpty() ? $includes : new MissingValue();
    }
}
