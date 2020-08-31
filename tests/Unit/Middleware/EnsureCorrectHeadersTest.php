<?php

use App\Http\Middleware\EnsureCorrectAPIHeaders;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

beforeEach(function () {
    // get the correct middleware
    $this->middleware = new EnsureCorrectAPIHeaders;
});

it('aborts request if accept header does not adhere to json api spec', function () {
    // create a test request
    $request = Request::create('/test', 'GET');

    /** @var Response $response */
    $response = $this->middleware->handle($request, function ($request) {
        $this->fail('Did not abort request because of invalid Accept header');
    });
    // assert 406 Not Acceptable
    $this->assertEquals(406, $response->status());
});


it('accepts request if accept header adheres to json api spec', function () {
    // create a test request with json api header
    $request = Request::create('/test', 'GET');
    $request->headers->set('accept', 'application/vnd.api+json');

    /** @var Response $response */
    $response = $this->middleware->handle($request, function ($request) {
        return new Response();
    });
    // assert 200 OK
    $this->assertEquals(200, $response->status());
});


it('aborts post request if content type header does not adhere to json api spec', function () {
    // create a test request with json api header
    $request = Request::create('/test', 'POST');
    $request->headers->set('accept', 'application/vnd.api+json');

    /** @var Response $response */
    $response = $this->middleware->handle($request, function ($request) {
        $this->fail('Did not abort request because of invalid Content-Type header');
    });
    // assert 415 Unsupported Content type
    $this->assertEquals(415, $response->status());
});


it('aborts patch request if content type header does not adhere to json api spec', function () {
    // create a test request with json api header for patch
    $request = Request::create('/test', 'PATCH');
    $request->headers->set('accept', 'application/vnd.api+json');

    /** @var Response $response */
    $response = $this->middleware->handle($request, function ($request) {
        $this->fail('Did not abort request because of invalid Content-Type header');
    });
    // assert 415 Unsupported Content type
    $this->assertEquals(415, $response->status());
});


it('accepts post request if content type header adheres to json api spec', function () {
    // create a test request with json api header for post
    $request = Request::create('/test', 'POST');
    $request->headers->set('accept', 'application/vnd.api+json');
    $request->headers->set('content-type', 'application/vnd.api+json');

    /** @var Response $response */
    $response = $this->middleware->handle($request, function ($request) {
        return new Response();
    });
    // assert 415 Unsupported Content type
    $this->assertEquals(200, $response->status());
});


it('accepts patch request if content type header adheres to json api spec', function () {
    // create a test request with json api header for patch
    $request = Request::create('/test', 'PATCH');
    $request->headers->set('accept', 'application/vnd.api+json');
    $request->headers->set('content-type', 'application/vnd.api+json');

    /** @var Response $response */
    $response = $this->middleware->handle($request, function ($request) {
        return new Response();
    });
    // assert 415 Unsupported Content type
    $this->assertEquals(200, $response->status());
});


it('ensures that a content type header adhering to json api spec is on response', function () {
    // create a test request with json api headers
    $request = Request::create('/test', 'GET');
    $request->headers->set('accept', 'application/vnd.api+json');
    $request->headers->set('content-type', 'application/vnd.api+json');

    /** @var Response $response */
    $response = $this->middleware->handle($request, function ($request) {
        return new Response();
    });
    // assert 200 OK
    $this->assertEquals(200, $response->status());
    $this->assertEquals('application/vnd.api+json', $response->headers->get('content-type'));
});


test('when aborting for a missing accept header the correct content type header is there', function () {
    // create a test request
    $request = Request::create('/test', 'GET');

    /** @var Response $response */
    $response = $this->middleware->handle($request, function ($request) {
        return new Response();
    });
    // assert 406 Not Acceptable
    $this->assertEquals(406, $response->status());
    // assert for content-type header
    $this->assertEquals('application/vnd.api+json', $response->headers->get('content-type'));
});


test('when aborting for a missing content type header the correct content type header is there', function () {
    // create a test request
    $request = Request::create('/test', 'POST');
    $request->headers->set('accept', 'application/vnd.api+json');

    /** @var Response $response */
    $response = $this->middleware->handle($request, function ($request) {
        return new Response();
    });
    // assert 415 Not Supported
    $this->assertEquals(415, $response->status());
    // assert for content-type header
    $this->assertEquals('application/vnd.api+json', $response->headers->get('content-type'));
});
