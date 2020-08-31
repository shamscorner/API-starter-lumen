<?php

use App\Exceptions\Handler;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;
use Illuminate\Database\QueryException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;


beforeEach(function () {
    /** @var Handler $handler */
    $this->handler = app(Handler::class);

    $this->request = Request::create('/test', 'GET');
    $this->request->headers->set('accept', 'application/vnd.api+json');
});


it('converts an exception into a json api spec error response', function () {

    $exception = new \Exception('Test exception');

    $response = $this->handler->render($this->request, $exception);
    TestResponse::fromBaseResponse($response)->assertJson([
        'errors' => [
            [
                'title' => 'Exception',
                'details' => 'Test exception',
            ]
        ]
    ])->assertStatus(500);
});


it('converts an http exception into a json api spec error response', function () {

    $exception = new HttpException(404, 'Not Found');

    $response = $this->handler->render($this->request, $exception);
    TestResponse::fromBaseResponse($response)->assertJson([
        'errors' => [
            [
                'title' => 'Http Exception',
                'details' => 'Not Found',
            ]
        ]
    ])->assertStatus(404);
});


it('converts an unauthenticated exception into a json api spec error response', function () {

    $exception = new AuthorizationException();

    $response = $this->handler->render($this->request, $exception);
    TestResponse::fromBaseResponse($response)->assertJson([
        'errors' => [
            [
                'title' => 'Unauthorized',
                'details' => 'You are not authorized',
            ]
        ]
    ]);
});


it('converts a query exception into a not found exception', function () {

    $exception = new QueryException('select ? from ?', [
        'name',
        'nothing'
    ], new \Exception(''));

    $response = $this->handler->render($this->request, $exception);
    TestResponse::fromBaseResponse($response)->assertJson([
        'errors' => [
            [
                'title' => 'Not Found Http Exception',
                'details' => 'Resource not found',
            ]
        ]
    ]);
});
