<?php

use App\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;
use Laravel\Passport\Passport;
use function Tests\passportActingAs;

uses(DatabaseMigrations::class);
uses(InteractsWithExceptionHandling::class);

it('can login a user from a resource object', function () {

    // $this->withoutExceptionHandling();

    // setup the dev environment
    $this->artisan('dev:setup');

    // register a new user
    $user = factory(User::class)->create([
        'name' => 'Shamim Hossain',
        'email' => 'hossains159@gmail.com',
        'password' => bcrypt('secret@123')
    ]);

    // make call
    $response = $this->json('POST', '/api/v1/users/login', [
        'data' => [
            'type' => 'users',
            'attributes' => [
                'email' => 'hossains159@gmail.com',
                'password' => 'secret@123'
            ]
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ]);

    // get the created and updated at time
    $createdTime = $response->response['data']['attributes']['created_at'];
    $updatedTime = $response->response['data']['attributes']['updated_at'];
    $token = $response->response['data']['attributes']['token'];
    $userId = $response->response['data']['id'];

    // assert
    $response->seeStatusCode(200)
        ->seeJsonEquals([
            'data' => [
                'type' => 'users',
                'id' => $userId,
                'attributes' => [
                    'name' => 'Shamim Hossain',
                    'email' => 'hossains159@gmail.com',
                    'created_at' => $createdTime,
                    'updated_at' => $updatedTime,
                    'token' => $token
                ]
            ]
        ]);
});


it('can register a user from a resource object', function () {

    // setup the dev environment
    $this->artisan('dev:setup');

    // make call
    $response = $this->json('POST', '/api/v1/users/register', [
        'data' => [
            'type' => 'users',
            'attributes' => [
                'name' => 'Shamim Hossain',
                'email' => 'hossains159@gmail.com',
                'password' => 'secret@123',
                'password_confirmation' => 'secret@123',
            ]
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ]);

    // get the created and updated at time
    $createdTime = $response->response['data']['attributes']['created_at'];
    $updatedTime = $response->response['data']['attributes']['updated_at'];
    $token = $response->response['data']['attributes']['token'];
    $userId = $response->response['data']['id'];

    // assert
    $response->seeStatusCode(201)
        ->seeJsonEquals([
            'data' => [
                'type' => 'users',
                'id' => $userId,
                'attributes' => [
                    'name' => 'Shamim Hossain',
                    'email' => 'hossains159@gmail.com',
                    'created_at' => $createdTime,
                    'updated_at' => $updatedTime,
                    'token' => $token
                ]
            ]
        ]);

    // assert the database has the user's record
    $this->seeInDatabase('users', [
        'name' => 'Shamim Hossain',
        'email' => 'hossains159@gmail.com'
    ]);

    // assert the password is correct
    assertTrue(Hash::check(
        'secret@123',
        User::whereEmail('hossains159@gmail.com')
            ->first()->password
    ));
});


it('validates that the type member is given when registering a user', function () {
    // make call
    $this->json('POST', '/api/v1/users/register', [
        'data' => [
            'type' => '',
            'attributes' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'secret@123'
            ]
        ]
    ], [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.type field is required.',
                    'source' => [
                        'pointer' => '/data/type'
                    ]
                ]
            ]
        ]);

    // assert the database has the user's record
    $this->missingFromDatabase('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
})->group('validate_register_users');


it('validates that the type member is given when logging a user', function () {
    // make call
    $this->json('POST', '/api/v1/users/login', [
        'data' => [
            'type' => '',
            'attributes' => [
                'email' => 'john@example.com',
                'password' => 'secret@123'
            ]
        ]
    ], [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.type field is required.',
                    'source' => [
                        'pointer' => '/data/type'
                    ]
                ]
            ]
        ]);
})->group('validate_login_users');


it('validates that the type member has the value of users when creating a user', function () {
    // assert
    $this->json('POST', '/api/v1/users/register', [
        'data' => [
            'type' => 'user',
            'attributes' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'secret@123'
            ]
        ]
    ], [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The selected data.type is invalid.',
                    'source' => [
                        'pointer' => '/data/type'
                    ]
                ]
            ]
        ]);

    // assert the database has the user's record
    $this->missingFromDatabase('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
})->group('validate_register_users');


it('validates that the type member has the value of users when logging a user', function () {
    // assert
    $this->json('POST', '/api/v1/users/login', [
        'data' => [
            'type' => 'user',
            'attributes' => [
                'email' => 'john@example.com',
                'password' => 'secret@123'
            ]
        ]
    ], [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The selected data.type is invalid.',
                    'source' => [
                        'pointer' => '/data/type'
                    ]
                ]
            ]
        ]);
})->group('validate_login_users');


it('validates that the attributes member has been given when creating a user', function () {
    // assert
    $this->json('POST', '/api/v1/users/register', [
        'data' => [
            'type' => 'users'
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes field is required.',
                    'source' => [
                        'pointer' => '/data/attributes'
                    ]
                ],
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.email field is required.',
                    'source' => [
                        'pointer' => '/data/attributes/email'
                    ]
                ],
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.name field is required.',
                    'source' => [
                        'pointer' => '/data/attributes/name'
                    ]
                ],
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.password field is required.',
                    'source' => [
                        'pointer' => '/data/attributes/password'
                    ]
                ],
            ]
        ]);

    // assert the database has the user's record
    $this->missingFromDatabase('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
})->group('validate_register_users');


it('validates that the attributes member has been given when logging a user', function () {
    // assert
    $this->json('POST', '/api/v1/users/login', [
        'data' => [
            'type' => 'users'
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes field is required.',
                    'source' => [
                        'pointer' => '/data/attributes'
                    ]
                ],
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.email field is required.',
                    'source' => [
                        'pointer' => '/data/attributes/email'
                    ]
                ],
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.password field is required.',
                    'source' => [
                        'pointer' => '/data/attributes/password'
                    ]
                ],
            ]
        ]);
})->group('validate_login_users');


it('validates that the attributes member is an object given when creating a user', function () {
    // assert
    $this->json('POST', '/api/v1/users/register', [
        'data' => [
            'type' => 'users',
            'attributes' => 'not an object'
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes must be an array.',
                    'source' => [
                        'pointer' => '/data/attributes'
                    ]
                ],
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.email field is required.',
                    'source' => [
                        'pointer' => '/data/attributes/email'
                    ]
                ],
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.name field is required.',
                    'source' => [
                        'pointer' => '/data/attributes/name'
                    ]
                ],
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.password field is required.',
                    'source' => [
                        'pointer' => '/data/attributes/password'
                    ]
                ],
            ]
        ]);

    // assert the database has the user's record
    $this->missingFromDatabase('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
})->group('validate_register_users');


it('validates that the attributes member is an object given when logging a user', function () {
    // assert
    $this->json('POST', '/api/v1/users/login', [
        'data' => [
            'type' => 'users',
            'attributes' => 'not an object'
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes must be an array.',
                    'source' => [
                        'pointer' => '/data/attributes'
                    ]
                ],
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.email field is required.',
                    'source' => [
                        'pointer' => '/data/attributes/email'
                    ]
                ],
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.password field is required.',
                    'source' => [
                        'pointer' => '/data/attributes/password'
                    ]
                ],
            ]
        ]);
})->group('validate_login_users');


it('validates that a name attribute is given when creating a user', function () {
    // assert
    $this->json('POST', '/api/v1/users/register', [
        'data' => [
            'type' => 'users',
            'attributes' => [
                'name' => '',
                'email' => 'john@example.com',
                'password' => 'secret@123',
                'password_confirmation' => 'secret@123'
            ]
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.name field is required.',
                    'source' => [
                        'pointer' => '/data/attributes/name'
                    ]
                ]
            ]
        ]);

    // assert the database has the user's record
    $this->missingFromDatabase('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
})->group('validate_register_users');


it('validates that a name attribute is a string when creating a user', function () {
    // assert
    $this->json('POST', '/api/v1/users/register', [
        'data' => [
            'type' => 'users',
            'attributes' => [
                'name' => 33,
                'email' => 'john@example.com',
                'password' => 'secret@123',
                'password_confirmation' => 'secret@123'
            ]
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.name must be a string.',
                    'source' => [
                        'pointer' => '/data/attributes/name'
                    ]
                ]
            ]
        ]);

    // assert the database has the user's record
    $this->missingFromDatabase('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
})->group('validate_register_users');


it('validates that a name attribute is not more than 255 characters when creating a user', function () {
    // assert
    $this->json('POST', '/api/v1/users/register', [
        'data' => [
            'type' => 'users',
            'attributes' => [
                'name' => 'It is a long established fact that a reader will be distracted by the readable' .
                    'content of a page when looking at its layout. The point of using Lorem Ipsum is that it ' .
                    'content of a page when looking at its layout. The point of using Lorem Ipsum is that it ' .
                    'content of a page when looking at its layout. The point of using Lorem Ipsum is that it ' .
                    'has a more-or-less normal distribution of letters',
                'email' => 'john@example.com',
                'password' => 'secret@123',
                'password_confirmation' => 'secret@123'
            ]
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.name may not be greater than 255 characters.',
                    'source' => [
                        'pointer' => '/data/attributes/name'
                    ]
                ]
            ]
        ]);

    // assert the database has the user's record
    $this->missingFromDatabase('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
})->group('validate_register_users');


it('validates that an email attribute is given when creating a user', function () {
    // assert
    $this->json('POST', '/api/v1/users/register', [
        'data' => [
            'type' => 'users',
            'attributes' => [
                'name' => 'John Doe',
                'email' => '',
                'password' => 'secret@123',
                'password_confirmation' => 'secret@123'
            ]
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.email field is required.',
                    'source' => [
                        'pointer' => '/data/attributes/email'
                    ]
                ]
            ]
        ]);

    // assert the database has the user's record
    $this->missingFromDatabase('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
})->group('validate_register_users');


it('validates that an email attribute is an email when creating a user', function () {
    // assert
    $this->json('POST', '/api/v1/users/register', [
        'data' => [
            'type' => 'users',
            'attributes' => [
                'name' => 'John Doe',
                'email' => 'johndoeatexampledotcom',
                'password' => 'secret@123',
                'password_confirmation' => 'secret@123'
            ]
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.email must be a valid email address.',
                    'source' => [
                        'pointer' => '/data/attributes/email'
                    ]
                ]
            ]
        ]);

    // assert the database has the user's record
    $this->missingFromDatabase('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
})->group('validate_register_users');


it('validates that a password attribute is given when creating a user', function () {
    // assert
    $this->json('POST', '/api/v1/users/register', [
        'data' => [
            'type' => 'users',
            'attributes' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => ''
            ]
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.password field is required.',
                    'source' => [
                        'pointer' => '/data/attributes/password'
                    ]
                ]
            ]
        ]);

    // assert the database has the user's record
    $this->missingFromDatabase('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
})->group('validate_register_users');


it('validates that a password attribute is a string when creating a user', function () {
    // assert
    $this->json('POST', '/api/v1/users/register', [
        'data' => [
            'type' => 'users',
            'attributes' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 33
            ]
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.password must be a string.',
                    'source' => [
                        'pointer' => '/data/attributes/password'
                    ]
                ]
            ]
        ]);

    // assert the database has the user's record
    $this->missingFromDatabase('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
})->group('validate_register_users');


it('validates that the length of a password attribute is greater than 8 characters when creating a user', function () {
    // assert
    $this->json('POST', '/api/v1/users/register', [
        'data' => [
            'type' => 'users',
            'attributes' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'this'
            ]
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.password must be at least 8 characters.',
                    'source' => [
                        'pointer' => '/data/attributes/password'
                    ]
                ]
            ]
        ]);

    // assert the database has the user's record
    $this->missingFromDatabase('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
})->group('validate_register_users');


it('validates that the length of a password attribute is not more than 255 characters when creating a user', function () {
    // assert
    $this->json('POST', '/api/v1/users/register', [
        'data' => [
            'type' => 'users',
            'attributes' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'It is a long established fact that a reader will be distracted by the readable' .
                    'content of a page when looking at its layout. The point of using Lorem Ipsum is that it ' .
                    'content of a page when looking at its layout. The point of using Lorem Ipsum is that it ' .
                    'content of a page when looking at its layout. The point of using Lorem Ipsum is that it ' .
                    'has a more-or-less normal distribution of letters'
            ]
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.password may not be greater than 255 characters.',
                    'source' => [
                        'pointer' => '/data/attributes/password'
                    ]
                ]
            ]
        ]);

    // assert the database has the user's record
    $this->missingFromDatabase('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
})->group('validate_register_users');


it('validates that an email attribute is given when logging a user', function () {
    // assert
    $this->json('POST', '/api/v1/users/login', [
        'data' => [
            'type' => 'users',
            'attributes' => [
                'email' => '',
                'password' => 'secret@123'
            ]
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.email field is required.',
                    'source' => [
                        'pointer' => '/data/attributes/email'
                    ]
                ]
            ]
        ]);
})->group('validate_login_users');


it('validates that an email attribute is an email when logging a user', function () {
    // assert
    $this->json('POST', '/api/v1/users/login', [
        'data' => [
            'type' => 'users',
            'attributes' => [
                'email' => 'johndoeatexampledotcom',
                'password' => 'secret@123'
            ]
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.email must be a valid email address.',
                    'source' => [
                        'pointer' => '/data/attributes/email'
                    ]
                ]
            ]
        ]);
})->group('validate_login_users');


it('validates that a password attribute is given when logging a user', function () {
    // assert
    $this->json('POST', '/api/v1/users/login', [
        'data' => [
            'type' => 'users',
            'attributes' => [
                'email' => 'john@example.com',
                'password' => ''
            ]
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.password field is required.',
                    'source' => [
                        'pointer' => '/data/attributes/password'
                    ]
                ]
            ]
        ]);
})->group('validate_login_users');


it('validates that a password attribute is a string when logging a user', function () {
    // assert
    $this->json('POST', '/api/v1/users/login', [
        'data' => [
            'type' => 'users',
            'attributes' => [
                'email' => 'john@example.com',
                'password' => 33
            ]
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.password must be a string.',
                    'source' => [
                        'pointer' => '/data/attributes/password'
                    ]
                ]
            ]
        ]);
})->group('validate_login_users');


it('validates that the length of a password attribute is greater than 8 characters when logging a user', function () {
    // assert
    $this->json('POST', '/api/v1/users/login', [
        'data' => [
            'type' => 'users',
            'attributes' => [
                'email' => 'john@example.com',
                'password' => 'this'
            ]
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.password must be at least 8 characters.',
                    'source' => [
                        'pointer' => '/data/attributes/password'
                    ]
                ]
            ]
        ]);
})->group('validate_register_users');


it('validates that the length of a password attribute is not more than 255 characters when logging a user', function () {
    // assert
    $this->json('POST', '/api/v1/users/login', [
        'data' => [
            'type' => 'users',
            'attributes' => [
                'email' => 'john@example.com',
                'password' => 'It is a long established fact that a reader will be distracted by the readable' .
                    'content of a page when looking at its layout. The point of using Lorem Ipsum is that it ' .
                    'content of a page when looking at its layout. The point of using Lorem Ipsum is that it ' .
                    'content of a page when looking at its layout. The point of using Lorem Ipsum is that it ' .
                    'has a more-or-less normal distribution of letters'
            ]
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.attributes.password may not be greater than 255 characters.',
                    'source' => [
                        'pointer' => '/data/attributes/password'
                    ]
                ]
            ]
        ]);
})->group('validate_login_users');


it('validates that the password is correct when logging users', function () {

    // setup the dev environment
    $this->artisan('dev:setup');

    // register a new user
    $user = factory(User::class)->create([
        'name' => 'Michel Doe',
        'email' => 'michel@example.com',
        'password' => bcrypt('secret@123')
    ]);

    // assert
    $this->json('POST', '/api/v1/users/login', [
        'data' => [
            'type' => 'users',
            'attributes' => [
                'email' => $user->email,
                'password' => 'secret@1234',
            ]
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'Password is incorrect.',
                    'source' => [
                        'pointer' => '/password'
                    ]
                ]
            ]
        ]);
})->group('validate_login_users');


it('validates that the user exists when logging users', function () {

    // setup the dev environment
    $this->artisan('dev:setup');

    // register a new user
    $user = factory(User::class)->create([
        'name' => 'Michel Doe',
        'email' => 'michel@example.com',
        'password' => bcrypt('secret@123')
    ]);

    // assert
    $this->json('POST', '/api/v1/users/login', [
        'data' => [
            'type' => 'users',
            'attributes' => [
                'email' => 'another@example.com',
                'password' => 'secret@1234',
            ]
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'User does not exist.',
                    'source' => [
                        'pointer' => '/user'
                    ]
                ]
            ]
        ]);
})->group('validate_login_users');


it('can logout a user', function () {
    // setup the dev environment
    $this->artisan('dev:setup');

    // register a new user
    $user = factory(User::class)->create([
        'name' => 'Shamim Hossain',
        'email' => 'hossains159@gmail.com',
        'password' => bcrypt('secret@123')
    ]);

    Passport::actingAs($user);

    // make call
    $response = $this->json('POST', '/api/v1/users/login', [
        'data' => [
            'type' => 'users',
            'attributes' => [
                'email' => 'hossains159@gmail.com',
                'password' => 'secret@123'
            ]
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ]);

    // get the created and updated at time
    $createdTime = $response->response['data']['attributes']['created_at'];
    $updatedTime = $response->response['data']['attributes']['updated_at'];
    $token = $response->response['data']['attributes']['token'];
    $userId = $response->response['data']['id'];

    // assert
    $response->seeStatusCode(200)
        ->seeJsonEquals([
            'data' => [
                'type' => 'users',
                'id' => $userId,
                'attributes' => [
                    'name' => 'Shamim Hossain',
                    'email' => 'hossains159@gmail.com',
                    'created_at' => $createdTime,
                    'updated_at' => $updatedTime,
                    'token' => $token
                ]
            ]
        ]);

    // logout
    $response = $this->json('POST', '/api/v1/users/logout', [
        'data' => [
            'type' => 'users',
            'id' => $userId
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])->seeStatusCode(200);
});


it('validates that the type member is given when logout a user', function () {

    // authenticate
    passportActingAs();

    // make call
    $this->json('POST', '/api/v1/users/logout', [
        'data' => [
            'type' => '',
            'id' => '40329u40feofjoeifj'
        ]
    ], [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.type field is required.',
                    'source' => [
                        'pointer' => '/data/type'
                    ]
                ]
            ]
        ]);
})->group('validate_logout_users');


it('validates that the type member has the value of users when logout a user', function () {

    // authenticate
    passportActingAs();

    // assert
    $this->json('POST', '/api/v1/users/logout', [
        'data' => [
            'type' => 'user',
            'id' => '40329u40feofjoeifj'
        ]
    ], [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The selected data.type is invalid.',
                    'source' => [
                        'pointer' => '/data/type'
                    ]
                ]
            ]
        ]);
})->group('validate_logout_users');


it('validates that an id is given when logout a user', function () {

    // authenticate
    passportActingAs();

    // assert
    $this->json("POST", "/api/v1/users/logout", [
        'data' => [
            'type' => 'users'
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.id field is required.',
                    'source' => [
                        'pointer' => '/data/id'
                    ]
                ]
            ]
        ]);
})->group('validate_logout_users');


it('validates that an id is string when logout a user', function () {

    // authenticate
    passportActingAs();

    // assert
    $this->json("POST", "/api/v1/users/logout", [
        'data' => [
            'type' => 'users',
            'id' => 1,
        ]
    ],  [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title' => 'Validation Error',
                    'details' => 'The data.id must be a string.',
                    'source' => [
                        'pointer' => '/data/id'
                    ]
                ]
            ]
        ]);
})->group('validate_logout_users');
