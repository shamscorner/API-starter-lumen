<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        // AuthorizationException::class,
        // HttpException::class,
        // ModelNotFoundException::class,
        // ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof QueryException) {
            $exception = new NotFoundHttpException('Resource not found');
        } else if ($exception instanceof AuthorizationException) {
            return $this->unAuthorized($request, $exception);
        } else if ($exception instanceof ValidationException) {
            return $this->invalidJson($exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * prepare the default json response with given request and exception
     * 
     * @param Request $request
     * @param Throwable $e
     * 
     * @throws \Exception
     */
    protected function prepareJsonResponse($request, Throwable $e)
    {
        return response()->json([
            'errors' => [
                [
                    'title' => Str::title(Str::snake(class_basename($e), ' ')),
                    'details' => $e->getMessage(),
                ]
            ]
        ], $this->isHttpException($e) ? $e->getStatusCode() : 500);
    }

    /**
     * report when json data is invalid
     * 
     * @param Illuminate\Validation\ValidationException $exception
     * 
     * @throws \Exception
     */
    private function invalidJson(ValidationException $exception)
    {
        // prepare the errors
        $errors = (new Collection($exception->validator->errors()))
            ->map(function ($error, $key) {
                return [
                    'title' => 'Validation Error',
                    'details' => $error[0],
                    'source' => [
                        'pointer' => '/' . str_replace('.', '/', $key)
                    ]
                ];
            })->values();

        // return the error response
        return response()->json([
            'errors' => $errors,
        ], $exception->status);
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function unAuthorized($request, AuthorizationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'errors' => [
                    [
                        'title' => 'Unauthorized',
                        'details' => 'You are not authorized'
                    ]
                ]
            ], 403);
        }

        return redirect()->guest($exception->redirectTo() ?? route('login'));
    }
}
