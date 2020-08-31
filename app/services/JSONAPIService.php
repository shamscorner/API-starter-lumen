<?php

namespace app\Services;

use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Resources\JSONAPIResource;
use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\JSONAPICollection;
use App\Http\Resources\JSONAPIIdentifierResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class JSONAPIService
{
    /**
     * fetch a resource
     * 
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param Integer/String $id
     * @param String $type
     * 
     * @return \App\Http\Resources\JSONAPIResource
     */
    public function fetchResource(string $model, $id = 0, string $type = ''): JSONAPIResource
    {
        if ($model instanceof Model) {
            return new JSONAPIResource($model);
        }

        // TODO: fix allowed includes
        $query = QueryBuilder::for($model::where('id', $id))
            // ->allowedIncludes(config("jsonapi.resources.{$type}.allowedIncludes"))
            ->firstOrFail();

        // dd($query);

        return new JSONAPIResource($query);
    }

    /**
     * fetch resources with sort and paginate supported format including relationships
     * 
     * @param String $modelClass
     * @param String $type
     * 
     * @return App\Http\Resources\JSONAPICollection
     */
    public function fetchResources(string $modelClass, string $type): JSONAPICollection
    {
        // TODO: fix allowed includes
        $models = QueryBuilder::for($modelClass)
            ->allowedSorts(config("jsonapi.resources.{$type}.allowedSorts"))
            // ->allowedIncludes(config("jsonapi.resources.{$type}.allowedIncludes"))
            ->jsonPaginate();

        return new JSONAPICollection($models);
    }

    /**
     * create a resource
     * 
     * @param String $modelClass
     * @param Array $attributes
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function createResource(string $modelClass, array $attributes): JsonResponse
    {
        $model = $modelClass::create($attributes);

        return (new JSONAPIResource($model))
            ->response()
            ->header('Location', route("{$model->type()}.show", [
                Str::of($model->type())->replace('-', '_')->singular()->__toString() => $model,
            ]));
    }

    /**
     * update a resource
     * 
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param Array $attributes
     * 
     * @return App\Http\Resources\JSONAPIResource
     */
    public function updateResource(Model $model, array $attributes): JSONAPIResource
    {
        $model->update($attributes);
        return new JSONAPIResource($model);
    }

    /**
     * delete a resource
     * 
     * @param \Illuminate\Database\Eloquent\Model $model
     * 
     * @return \Illuminate\Http\Response
     */
    public function deleteResource(Model $model): Response
    {
        $model->delete();
        return response(null, 204);
    }

    /**
     * fetch relationships 
     * 
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param String $relationship
     * 
     * @return \App\Http\Resources\JSONAPIIdentifierResource or collection
     */
    public function fetchRelationship(Model $model, string $relationship)
    {
        // convert relationship text from kabab case to camel case
        $relationship = $this->convertToCamel($relationship);

        if ($model->$relationship instanceof Model) {
            return new JSONAPIIdentifierResource($model->$relationship);
        }

        return JSONAPIIdentifierResource::collection($model->$relationship);
    }

    /**
     * update many to many relationships
     * 
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param String $relationship
     * @param Array $ids
     * 
     * @return \Illuminate\Http\Response
     */
    public function updateManyToManyRelationships(Model $model, string $relationship, array $ids): Response
    {
        // convert relationship text from kabab case to camel case
        $relationship = $this->convertToCamel($relationship);

        $model->$relationship()->sync($ids);
        return response(null, 204);
    }

    /**
     * update to one relationship
     * 
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param String $relationship
     * @param $id
     * 
     * @return \Illuminate\Http\Response
     */
    public function updateToOneRelationship(Model $model, string $relationship, $id): Response
    {
        $relatedModel = $model->$relationship()->getRelated();

        $model->$relationship()->dissociate();

        if ($id) {
            $newModel = $relatedModel->newQuery()->findOrFail($id);
            $model->$relationship()->associate($newModel);
        }
        $model->save();
        return response(null, 204);
    }

    /**
     * fetch related data
     * 
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param String $relationship
     * 
     */
    public function fetchRelated(Model $model, string $relationship)
    {
        // convert relationship text from kabab case to camel case
        $relationship = $this->convertToCamel($relationship);

        if ($model->$relationship instanceof Model) {
            return new JSONAPIResource($model->$relationship);
        }

        return new JSONAPICollection($model->$relationship);
    }

    /**
     * convert text to camel case
     * 
     * @param string $text
     * 
     * @return string
     */
    private function convertToCamel(string $text): string
    {
        return Str::of($text)->camel()->__toString();
    }
}
