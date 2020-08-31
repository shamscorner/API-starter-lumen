<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class EnsureCorrectAPIHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // check for the header whether the accept type is 'application/vnd.api+json'
        if ($request->headers->get('accept') !== 'application/vnd.api+json') {
            return $this->addCorrectContentType(new Response('', 406));
        }

        // check for the header whether the content-type is 'application/vnd.api+json' for post and patch request
        if ($request->isMethod('POST') || $request->isMethod('PATCH')) {
            if ($request->headers->get('content-type') !== 'application/vnd.api+json') {
                return $this->addCorrectContentType(new Response('', 415));
            }
        }

        // return the response with the correct api spec content-type header
        return $this->addCorrectContentType($next($request));
    }

    /**
     * add correct content type in all response
     * 
     * @param BaseResponse just an alias of the Symfony's response class
     * 
     * @return Response
     */
    private function addCorrectContentType(BaseResponse $response)
    {
        $response->headers->set('content-type', 'application/vnd.api+json');
        return $response;
    }
}
