<?php

return [
    'resources' => [
        'users' => [
            'allowedSorts' => [
                'name',
                'email',
                'created_at',
                'updated_at'
            ],
            'allowedIncludes' => [],
            'allowedFilters' => [],
            'validationRules' => [
                'create' => [
                    'data.attributes.name' => 'required|string|max:255',
                    'data.attributes.email' => 'required|email',
                    'data.attributes.password' => 'required|string|min:8|max:255|confirmed',
                ],
                'update' => [
                    'data.attributes.name' => 'sometimes|required|string|max:255',
                    'data.attributes.email' => 'sometimes|required|email',
                    'data.attributes.password' => 'sometimes|required|string|min:8|max:255|confirmed'
                ]
            ],
            'relationships' => []
        ]
    ]
];
