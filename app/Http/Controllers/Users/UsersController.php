<?php

namespace App\Http\Controllers\Users;

use App\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\JSONAPIService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\JSONAPIRequest;
use App\Http\Resources\JSONAPIResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UsersController extends Controller
{
    private $service;

    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return as a collection
        return $this->service->fetchResources(User::class, 'users');
    }

    /**
     * Display the specified resource.
     *
     * @param  string $user
     * @return \Illuminate\Http\Response
     */
    public function show($user)
    {
        // return as a user resource object
        return $this->service->fetchResource(User::class, $user, 'users');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\JSONAPIRequest  $request
     * @param  string  $user
     * @return \Illuminate\Http\Response
     */
    public function update(JSONAPIRequest $request, $user)
    {
        // find the user
        $user = User::findOrFail($user);

        // update the user data
        return $this->service->updateResource($user, [
            'name' => $request->getParams()->input('data.attributes.name'),
            'email' => $request->getParams()->input('data.attributes.email'),
            'password' => Hash::make(($request->getParams()->input('data.attributes.password'))),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($user)
    {
        // find the user
        $user = User::findOrFail($user);

        // delete the user
        return $this->service->deleteResource($user);
    }

    /**
     * Register a new user
     * 
     * @param  \App\Http\Requests\JSONAPIRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function register(JSONAPIRequest $request)
    {
        // create user
        $user = User::create([
            'name' => $request->getParams()->input('data.attributes.name'),
            'email' => $request->getParams()->input('data.attributes.email'),
            'password' => Hash::make(($request->getParams()->input('data.attributes.password'))),
        ]);

        // create a token
        $token = $user->createToken('Unnuio')->accessToken;

        // attach token
        $user->attributes['token'] = $token;

        // return response
        return (new JSONAPIResource($user))
            ->response()
            ->header('Location', route("{$user->type()}.show", [
                Str::of($user->type())->replace('-', '_')->singular()->__toString() => $user,
            ]));
    }

    /**
     * login a user
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // validate
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.type' => ['required', Rule::in(['users'])],
            'data.attributes' => 'required|array',
            'data.attributes.email' => 'required|email',
            'data.attributes.password' => 'required|string|min:8|max:255',
        ]);

        // if fails
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // find user
        $user = User::where('email', $request->input('data.attributes.email'))->first();

        // check and return with access token
        if ($user) {
            if (Hash::check($request->input('data.attributes.password'), $user->password)) {
                // create a token
                $token = $user->createToken('Unnuio')->accessToken;

                // attach token
                $user->attributes['token'] = $token;

                // return response
                return (new JSONAPIResource($user))->response();
            } else {
                throw ValidationException::withMessages(['password' => 'Password is incorrect.']);
            }
        } else {
            throw ValidationException::withMessages(['user' => 'User does not exist.']);
        }
    }

    /**
     * logout a user
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        // validate
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.type' => ['required', Rule::in(['users'])],
            'data.id' => 'required|string'
        ]);

        // if fails
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $token = $request->user()->token();
        $token->revoke();

        // return response
        return response(null, 200);
    }
}
