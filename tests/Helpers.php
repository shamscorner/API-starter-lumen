<?php

namespace Tests;

use App\User;
use Laravel\Passport\Passport;

/**
 * get an authenticated passport user
 * 
 * @return \App\User
 */
function passportActingAs(): User
{
    // create a user
    $user = factory(User::class)->create();
    // authenticate
    Passport::actingAs($user);

    return $user;
}
