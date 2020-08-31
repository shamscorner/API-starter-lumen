<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class JSONAPIRequest extends Controller
{
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->validate($request, $this->rules());

        parent::__construct($request);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    private function rules()
    {
        // get the default parent rules
        $rules = [
            'data' => 'required|array',
            'data.id' => ($this->request->method() === 'PATCH') ? 'required|string' : 'string',
            'data.type' => ['required', Rule::in(array_keys(config('jsonapi.resources')))],
            'data.attributes' => 'required|array',
        ];

        // merge with other custom specified rules from json api config
        return $this->mergeConfigRules($rules);
    }

    /**
     * merge config rules from json api config
     * 
     * @param Array $rules
     * 
     * @return Array
     */
    private function mergeConfigRules(array $rules): array
    {
        $type = $this->request->input('data.type');
        if ($type && config("jsonapi.resources.{$type}")) {
            switch ($this->request->method) {
                case 'PATCH':
                    $rules = array_merge($rules, config("jsonapi.resources.{$type}.validationRules.update"));
                    break;
                case 'POST':
                default:
                    $rules = array_merge($rules, config("jsonapi.resources.{$type}.validationRules.create"));
                    break;
            }
        }
        return $rules;
    }
}
