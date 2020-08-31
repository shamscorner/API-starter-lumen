<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\MissingValue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JSONAPIResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => (string) $this->id,
            'type' => $this->type(),
            'attributes' => $this->allowedAttributes(),
            'relationships' => $this->prepareRelationships(),
        ];
    }

    /**
     * prepare relationships from the config/jsonapi resources
     * 
     * @return \Illuminate\Support\Collection
     */
    private function prepareRelationships()
    {
        $collection = collect(config("jsonapi.resources.{$this->type()}.relationships"))
            ->flatMap(function ($related) {
                $relatedType = $related['type'];
                $relationship = $related['method'];
                $routeId = $related['route_id'];
                return [
                    $relatedType => [
                        'links' => [
                            'self' => route(
                                "{$this->type()}.relationships.{$relatedType}",
                                [$routeId => $this->id]
                            ),
                            'related' => route(
                                "{$this->type()}.{$relatedType}",
                                [$routeId => $this->id]
                            ),
                        ],
                        'data' => $this->prepareRelationshipData($relatedType, $relationship)
                    ],
                ];
            });

        return $collection->count() > 0 ? $collection : new MissingValue();
    }

    /**
     * prepare the relationship data for the related type
     * 
     * @param string $relatedType
     * @param string $relationship
     * 
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    private function prepareRelationshipData($relatedType, $relationship)
    {
        if ($this->whenLoaded($relationship) instanceof MissingValue) {
            return new MissingValue();
        }
        if ($this->$relationship() instanceof BelongsTo) {
            return new JSONAPIIdentifierResource($this->$relationship);
        }
        return JSONAPIIdentifierResource::collection($this->$relationship);
    }

    /**
     * override the with method of this model
     * 
     * @param Request $request
     * 
     * @return Array $shops
     */
    public function with($request)
    {
        $with = [];

        if ($this->included($request)->isNotEmpty()) {
            $with['included'] = $this->included($request);
        }

        return $with;
    }

    /**
     * override the included method for this model
     * 
     * @param Request $request
     * 
     * @return Collection $shops included
     */
    public function included($request)
    {
        return collect($this->relations())
            ->filter(function ($resource) {
                return $resource->collection !== null;
            })->flatMap->toArray($request);
    }

    /**
     * return the relations for this model
     * 
     * @return Collection SellersResource
     */
    private function relations()
    {
        return collect(config("jsonapi.resources.{$this->type()}.relationships"))
            ->map(function ($relation) {
                $modelOrCollection = $this->whenLoaded($relation['method']);

                if ($modelOrCollection instanceof Model) {
                    $modelOrCollection = collect([
                        new JSONAPIResource($modelOrCollection)
                    ]);
                }

                return JSONAPIResource::collection($modelOrCollection);
            });
    }
}
