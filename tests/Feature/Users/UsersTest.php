<?php

use App\User;
use Laravel\Passport\Passport;
use function Tests\passportActingAs;

use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;

uses(DatabaseMigrations::class);
uses(InteractsWithExceptionHandling::class);


beforeEach(function () {
    // authenticated user
    $this->user = passportActingAs();
});


it('a user ID is a UUID instead of an integer', function () {
    // assert user id is not an integer
    assertFalse(is_integer($this->user->id));

    // assert the length of the ID is 36 character
    assertEquals(36, strlen($this->user->id));
});


it('returns a user as a resource object', function () {
    // assert
    $this->get("/api/v1/users/{$this->user->id}", [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json'
    ])
        ->seeStatusCode(200)
        ->seeJson([
            'data' => [
                'id' => $this->user->id,
                'type' => 'users',
                'attributes' => [
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'created_at' => $this->user->created_at->toJSON(),
                    'updated_at' => $this->user->updated_at->toJSON()
                ]
            ]
        ]);
});


it('returns all users as a collection of resource objects', function () {
    // delete the passport user
    $this->user->delete();

    // create 3 users
    $users = factory(User::class, 3)->create();

    // reset the passport user
    Passport::actingAs($users->first());

    // assert
    $this->get('/api/v1/users', [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json'
    ])
        ->seeStatusCode(200)
        ->seeJson([
            'data' => [
                [
                    "id" => $users[0]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[0]->name,
                        'email' => $users[0]->email,
                        'created_at' => $users[0]->created_at->toJSON(),
                        'updated_at' => $users[0]->updated_at->toJSON()
                    ]
                ],
                [
                    "id" => $users[1]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[1]->name,
                        'email' => $users[1]->email,
                        'created_at' => $users[1]->created_at->toJSON(),
                        'updated_at' => $users[1]->updated_at->toJSON()
                    ]
                ],
                [
                    "id" => $users[2]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[2]->name,
                        'email' => $users[2]->email,
                        'created_at' => $users[2]->created_at->toJSON(),
                        'updated_at' => $users[2]->updated_at->toJSON()
                    ]
                ]
            ]
        ]);
});

// TODO: fix http and https issue
it('can sort users by name through a sort query parameter', function () {
    // delete the passport user
    $this->user->delete();

    // create some users with names
    $users = factory(User::class, 3)->create();

    // sort them
    $users = $users->sortBy(function ($item) {
        return $item->name;
    })->values();

    // passport
    Passport::actingAs($users->first());

    // assert
    $this->get("/api/v1/users?sort=name", [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json'
    ])
        ->seeStatusCode(200)
        ->seeJsonEquals([
            "data" => [
                [
                    "id" => $users[0]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[0]->name,
                        'email' => $users[0]->email,
                        'created_at' => $users[0]->created_at->toJSON(),
                        'updated_at' => $users[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users[1]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[1]->name,
                        'email' => $users[1]->email,
                        'created_at' => $users[1]->created_at->toJSON(),
                        'updated_at' => $users[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users[2]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[2]->name,
                        'email' => $users[2]->email,
                        'created_at' => $users[2]->created_at->toJSON(),
                        'updated_at' => $users[2]->updated_at->toJSON(),
                    ]
                ],
            ],
            'links' => [
                'first' => route('users.index', ['sort' => "name", 'page[number]' => 1]),
                'last' => route('users.index', ['sort' => "name", 'page[number]' => 1]),
                'prev' => null,
                'next' => null,
            ],
            'meta' => [
                "current_page" => 1,
                "from" => 1,
                "last_page" =>  1,
                "path" => route('users.index'),
                "per_page" =>  30,
                "to" =>  3,
                "total" => 3
            ]
        ]);
})->group('sort_users')->skip();

// TODO: fix https and http issue
it('can sort users by name in descending order through a sort query parameter', function () {
    // delete the passport user
    $this->user->delete();

    // create some users with names
    $users = factory(User::class, 3)->create();

    // sort them
    $users = $users->sortByDesc(function ($item) {
        return $item->name;
    })->values();

    // passport
    Passport::actingAs($users->first());

    // assert
    $this->get("/api/v1/users?sort=-name", [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json'
    ])
        ->seeStatusCode(200)
        ->seeJsonEquals([
            "data" => [
                [
                    "id" => $users[0]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[0]->name,
                        'email' => $users[0]->email,
                        'created_at' => $users[0]->created_at->toJSON(),
                        'updated_at' => $users[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users[1]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[1]->name,
                        'email' => $users[1]->email,
                        'created_at' => $users[1]->created_at->toJSON(),
                        'updated_at' => $users[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users[2]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[2]->name,
                        'email' => $users[2]->email,
                        'created_at' => $users[2]->created_at->toJSON(),
                        'updated_at' => $users[2]->updated_at->toJSON(),
                    ]
                ],
            ],
            'links' => [
                'first' => route('users.index', ['sort' => "-name", 'page[number]' => 1]),
                'last' => route('users.index', ['sort' => "-name", 'page[number]' => 1]),
                'prev' => null,
                'next' => null,
            ],
            'meta' => [
                "current_page" => 1,
                "from" => 1,
                "last_page" =>  1,
                "path" => route('users.index'),
                "per_page" =>  30,
                "to" =>  3,
                "total" => 3
            ]
        ]);
})->group('sort_users')->skip();

// TODO: fix http and https issue
it('can sort users by multiple attributes through a sort query parameter', function () {
    // delete the passport user
    $this->user->delete();

    // create some users with names
    $users = factory(User::class, 3)->make()->each(function (User $user, $index) {
        $names = [
            'Adam',
            'Adam',
            'Clara'
        ];
        $emails = [
            'adam@example.com',
            '1212adam@example.com',
            'cl@example.com'
        ];
        $user->name = $names[$index];
        $user->email = $emails[$index];
        $user->save();
    });

    // passport
    Passport::actingAs($users->first());

    // assert
    $this->get("/api/v1/users?sort=name,email", [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json'
    ])
        ->seeStatusCode(200)
        ->seeJsonEquals([
            "data" => [
                [
                    "id" => $users[1]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[1]->name,
                        'email' => $users[1]->email,
                        'created_at' => $users[1]->created_at->toJSON(),
                        'updated_at' => $users[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users[0]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[0]->name,
                        'email' => $users[0]->email,
                        'created_at' => $users[0]->created_at->toJSON(),
                        'updated_at' => $users[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users[2]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[2]->name,
                        'email' => $users[2]->email,
                        'created_at' => $users[2]->created_at->toJSON(),
                        'updated_at' => $users[2]->updated_at->toJSON(),
                    ]
                ],
            ],
            'links' => [
                'first' => route('users.index', ['sort' => "name,email", 'page[number]' => 1]),
                'last' => route('users.index', ['sort' => "name,email", 'page[number]' => 1]),
                'prev' => null,
                'next' => null,
            ],
            'meta' => [
                "current_page" => 1,
                "from" => 1,
                "last_page" =>  1,
                "path" => route('users.index'),
                "per_page" =>  30,
                "to" =>  3,
                "total" => 3
            ]
        ]);
})->group('sort_users')->skip();

// TODO: fix https and http issue
it('can sort users by multiple attributes in descending order through a sort query param', function () {
    // delete the passport user
    $this->user->delete();

    $users = factory(User::class, 3)->make()->each(function (User $user, $index) {
        $names = [
            'Adam',
            'Adam',
            'Clara',
        ];
        $emails = [
            'adam@example.com',
            '1212adam@example.com',
            'cl@example.com'
        ];
        $user->name = $names[$index];
        $user->email = $emails[$index];
        $user->save();
    });

    // passport
    Passport::actingAs($users->first());

    // assert
    $this->get("/api/v1/users?sort=-name,email", [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json'
    ])
        ->seeStatusCode(200)
        ->seeJsonEquals([
            "data" => [
                [
                    "id" => $users[2]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[2]->name,
                        'email' => $users[2]->email,
                        'created_at' => $users[2]->created_at->toJSON(),
                        'updated_at' => $users[2]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users[1]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[1]->name,
                        'email' => $users[1]->email,
                        'created_at' => $users[1]->created_at->toJSON(),
                        'updated_at' => $users[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users[0]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[0]->name,
                        'email' => $users[0]->email,
                        'created_at' => $users[0]->created_at->toJSON(),
                        'updated_at' => $users[0]->updated_at->toJSON(),
                    ]
                ],
            ],
            'links' => [
                'first' => route('users.index', ['sort' => "-name,email", 'page[number]' => 1]),
                'last' => route('users.index', ['sort' => "-name,email", 'page[number]' => 1]),
                'prev' => null,
                'next' => null,
            ],
            'meta' => [
                "current_page" => 1,
                "from" => 1,
                "last_page" =>  1,
                "path" => route('users.index'),
                "per_page" =>  30,
                "to" =>  3,
                "total" => 3
            ]
        ]);
})->group('sort_users')->skip();

// TODO: fix localhost links issue
it('can paginate users through a page query param', function () {
    // delete the passport user
    $this->user->delete();

    // create some users
    $users = factory(User::class, 6)->create();

    // passport
    Passport::actingAs($users->first());

    // assert
    $this->get("/api/v1/users?page[size]=3&page[number]=1", [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json'
    ])
        ->seeStatusCode(200)
        ->seeJsonEquals([
            "data" => [
                [
                    "id" => $users[0]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[0]->name,
                        'email' => $users[0]->email,
                        'created_at' => $users[0]->created_at->toJSON(),
                        'updated_at' => $users[0]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users[1]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[1]->name,
                        'email' => $users[1]->email,
                        'created_at' => $users[1]->created_at->toJSON(),
                        'updated_at' => $users[1]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users[2]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[2]->name,
                        'email' => $users[2]->email,
                        'created_at' => $users[2]->created_at->toJSON(),
                        'updated_at' => $users[2]->updated_at->toJSON(),
                    ]
                ],
            ],
            'links' => [
                'first' => route('users.index', ['page[size]' => 3, 'page[number]' => 1]),
                'last' => route('users.index', ['page[size]' => 3, 'page[number]' => 2]),
                'prev' => null,
                'next' => route('users.index', ['page[size]' => 3, 'page[number]' => 2]),
            ],
            'meta' => [
                "current_page" => 1,
                "from" => 1,
                "last_page" =>  2,
                "path" => route('users.index'),
                "per_page" =>  3,
                "to" =>  3,
                "total" => 6
            ]
        ]);
})->group('paginate_users')->skip();

// TODO: fix localhost links issue
it('can paginate users through a page query param and show different pages', function () {
    // delete the passport user
    $this->user->delete();

    // create some users
    $users = factory(User::class, 6)->create();

    // passport
    Passport::actingAs($users->first());

    // assert
    $this->get("/api/v1/users?page[size]=3&page[number]=2", [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json'
    ])
        ->seeStatusCode(200)
        ->seeJsonEquals([
            "data" => [
                [
                    "id" => $users[3]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[3]->name,
                        'email' => $users[3]->email,
                        'created_at' => $users[3]->created_at->toJSON(),
                        'updated_at' => $users[3]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users[4]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[4]->name,
                        'email' => $users[4]->email,
                        'created_at' => $users[4]->created_at->toJSON(),
                        'updated_at' => $users[4]->updated_at->toJSON(),
                    ]
                ],
                [
                    "id" => $users[5]->id,
                    "type" => "users",
                    "attributes" => [
                        'name' => $users[5]->name,
                        'email' => $users[5]->email,
                        'created_at' => $users[5]->created_at->toJSON(),
                        'updated_at' => $users[5]->updated_at->toJSON(),
                    ]
                ],
            ],
            'links' => [
                'first' => route('users.index', ['page[size]' => 3, 'page[number]' => 1]),
                'last' => route('users.index', ['page[size]' => 3, 'page[number]' => 2]),
                'prev' => route('users.index', ['page[size]' => 3, 'page[number]' => 1]),
                'next' => null
            ],
            'meta' => [
                "current_page" => 2,
                "from" => 4,
                "last_page" =>  2,
                "path" => route('users.index'),
                "per_page" =>  3,
                "to" =>  6,
                "total" => 6
            ]
        ]);
})->group('paginate_users')->skip();


it('can update a user from a resource object', function () {
    $this->withoutExceptionHandling();
    // create a user
    $user = factory(User::class)->create();

    // get the timestamp for now
    $creationTimestamp = $user->created_at;
    sleep(5);

    $response = $this->json("PATCH", "/api/v1/users/{$user->id}", [
        'data' => [
            'id' => $user->id,
            'type' => 'users',
            'attributes' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'secret@123',
                'password_confirmation' => 'secret@123',
            ]
        ]
    ], [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ]);

    // get the created and updated at time
    $updatedTime = $response->response['data']['attributes']['updated_at'];

    $response->seeStatusCode(200)
        ->seeJsonEquals([
            "data" => [
                "id" => $user->id,
                "type" => "users",
                "attributes" => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'created_at' => $creationTimestamp->toJSON(),
                    'updated_at' => $updatedTime,
                ]
            ]
        ]);

    $this->seeInDatabase('users', [
        'id' => $user->id,
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $this->assertTrue(Hash::check('secret@123', User::whereId($user->id)->first()->password));
});


it('validates that the type member is given when updating a user', function () {
    // create a user
    $user = factory(User::class)->create();

    $this->json("PATCH", "/api/v1/users/{$user->id}", [
        'data' => [
            'id' => $user->id,
            'type' => '',
            'attributes' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'secret@123',
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
                    'title'   => 'Validation Error',
                    'details' => 'The data.type field is required.',
                    'source'  => [
                        'pointer' => '/data/type',
                    ]
                ]
            ]
        ]);

    $this->missingFromDatabase('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
})->group('validate_update_users');


it('validates that the type member has the value of users when updating a user', function () {
    // create a user
    $user = factory(User::class)->create();

    $this->json("PATCH", "/api/v1/users/{$user->id}", [
        'data' => [
            'id' => $user->id,
            'type' => 'other',
            'attributes' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'secret@123',
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
                    'title'   => 'Validation Error',
                    'details' => 'The selected data.type is invalid.',
                    'source'  => [
                        'pointer' => '/data/type',
                    ]
                ]
            ]
        ]);

    $this->missingFromDatabase('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
})->group('validate_update_users');


it('validates that the attributes member has been given when updating a user', function () {
    // create a user
    $user = factory(User::class)->create();

    $this->json("PATCH", "/api/v1/users/{$user->id}", [
        'data' => [
            'id' => $user->id,
            'type' => 'users'
        ]
    ], [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title'   => 'Validation Error',
                    'details' => 'The data.attributes field is required.',
                    'source'  => [
                        'pointer' => '/data/attributes',
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
})->group('validate_update_users');


it('validates that the attributes member is an object when updating a user', function () {
    // create a user
    $user = factory(User::class)->create();

    $this->json("PATCH", "/api/v1/users/{$user->id}", [
        'data' => [
            'id' => $user->id,
            'type' => 'users',
            'attributes' => 'not an object'
        ]
    ], [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])
        ->seeStatusCode(422)
        ->seeJsonEquals([
            'errors' => [
                [
                    'title'   => 'Validation Error',
                    'details' => 'The data.attributes must be an array.',
                    'source'  => [
                        'pointer' => '/data/attributes',
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
})->group('validate_update_users');


it('validates that an id is given when updating a user', function () {
    // create a user
    $user = factory(User::class)->create();

    // assert
    $this->json("PATCH", "/api/v1/users/{$user->id}", [
        'data' => [
            'type' => 'users',
            'attributes' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'secret@123',
                'password_confirmation' => 'secret@123',
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
                    'details' => 'The data.id field is required.',
                    'source' => [
                        'pointer' => '/data/id'
                    ]
                ]
            ]
        ]);
})->group('validate_update_users');


it('validates that an id is string when updating a user', function () {
    // create a user
    $user = factory(User::class)->create();

    // assert
    $this->json("PATCH", "/api/v1/users/{$user->id}", [
        'data' => [
            'type' => 'users',
            'id' => 1,
            'attributes' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'secret@123',
                'password_confirmation' => 'secret@123',
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
                    'details' => 'The data.id must be a string.',
                    'source' => [
                        'pointer' => '/data/id'
                    ]
                ]
            ]
        ]);
})->group('validate_update_users');


it('validates that a name attribute is given when updating a user', function () {
    // create a user
    $user = factory(User::class)->create();

    // assert
    $this->json("PATCH", "/api/v1/users/{$user->id}", [
        'data' => [
            'id' => $user->id,
            'type' => 'users',
            'attributes' => [
                'name' => '',
                'email' => 'john@example.com',
                'password' => 'secret@123',
                'password_confirmation' => 'secret@123',
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
})->group('validate_update_users');


it('validates that a name attribute is a string when updating a user', function () {
    // create a user
    $user = factory(User::class)->create();

    // assert
    $this->json("PATCH", "/api/v1/users/{$user->id}", [
        'data' => [
            'id' => $user->id,
            'type' => 'users',
            'attributes' => [
                'name' => 33,
                'email' => 'john@example.com',
                'password' => 'secret@123',
                'password_confirmation' => 'secret@123',
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
})->group('validate_update_users');


it('validates that a name attribute is not more than 255 characters when updating a user', function () {
    // create a user
    $user = factory(User::class)->create();

    // assert
    $this->json("PATCH", "/api/v1/users/{$user->id}", [
        'data' => [
            'id' => $user->id,
            'type' => 'users',
            'attributes' => [
                'name' => 'It is a long established fact that a reader will be distracted by the readable' .
                    'content of a page when looking at its layout. The point of using Lorem Ipsum is that it ' .
                    'content of a page when looking at its layout. The point of using Lorem Ipsum is that it ' .
                    'content of a page when looking at its layout. The point of using Lorem Ipsum is that it ' .
                    'has a more-or-less normal distribution of letters',
                'email' => 'john@example.com',
                'password' => 'secret@123',
                'password_confirmation' => 'secret@123',
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
})->group('validate_update_users');



it('validates that an email attribute is given when updating a user', function () {
    // create a user
    $user = factory(User::class)->create();

    // assert
    $this->json("PATCH", "/api/v1/users/{$user->id}", [
        'data' => [
            'id' => $user->id,
            'type' => 'users',
            'attributes' => [
                'name' => 'John Doe',
                'email' => '',
                'password' => 'secret@123',
                'password_confirmation' => 'secret@123',
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
})->group('validate_update_users');


it('validates that an email attribute is an email when updating a user', function () {
    // create a user
    $user = factory(User::class)->create();

    // assert
    $this->json("PATCH", "/api/v1/users/{$user->id}", [
        'data' => [
            'id' => $user->id,
            'type' => 'users',
            'attributes' => [
                'name' => 'John Doe',
                'email' => 'johndoeatexampledotcom',
                'password' => 'secret@123',
                'password_confirmation' => 'secret@123',
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
})->group('validate_update_users');


it('validates that a password attribute is given when updating a user', function () {
    // create a user
    $user = factory(User::class)->create();

    // assert
    $this->json("PATCH", "/api/v1/users/{$user->id}", [
        'data' => [
            'id' => $user->id,
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
})->group('validate_update_users');


it('validates that a password attribute is a string when updating a user', function () {
    // create a user
    $user = factory(User::class)->create();

    // assert
    $this->json("PATCH", "/api/v1/users/{$user->id}", [
        'data' => [
            'id' => $user->id,
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
})->group('validate_update_users');


it('validates that a password attribute is more than 8 characters when updating a user', function () {
    // create a user
    $user = factory(User::class)->create();

    // assert
    $this->json("PATCH", "/api/v1/users/{$user->id}", [
        'data' => [
            'id' => $user->id,
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
})->group('validate_update_users');


it('validates that a password attribute is not more than 255 characters when updating a user', function () {
    // create a user
    $user = factory(User::class)->create();

    // assert
    $this->json("PATCH", "/api/v1/users/{$user->id}", [
        'data' => [
            'id' => $user->id,
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
})->group('validate_update_users');


it('can delete a user through a delete request', function () {
    // create a user
    $user = factory(User::class)->create();

    // assert
    $this->json("DELETE", "/api/v1/users/{$user->id}", [], [
        'accept' => 'application/vnd.api+json',
        'content-type' => 'application/vnd.api+json',
    ])->seeStatusCode(204);

    // assert in database
    $this->missingFromDatabase('users', [
        'name' => $user->name,
        'email' => $user->email
    ]);
});
