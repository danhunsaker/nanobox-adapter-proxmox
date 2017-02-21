<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        TokenMismatchException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $e
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $e
     *
     * @return \Illuminate\Http\Response
     *
     *  @SWG\Response(
     *      response="default",
     *      description="Error",
     *      @SWG\Schema(
     *          type="object",
     *          required={"errors"},
     *          @SWG\Property(
     *              property="errors",
     *              description="On Failure: Should return a json body with an errors node and a non 2xx status code.",
     *              type="array",
     *              @SWG\Items(
     *                  type="string",
     *              ),
     *          ),
     *      ),
     *  ),
     */
    public function render($request, Exception $e)
    {
        if ($request->wantsJson()) {
            if ($e instanceof ModelNotFoundException) {
                return response()->json(['errors' => ['Invalid ' . basename(strtr($e->getModel(), '\\', '/'))]], 404);
            } elseif ($e instanceof HttpException) {
                return response()->json(['errors' => [$e->getMessage()]], $e->getStatusCode());
            }
        }

        return parent::render($request, $e);
    }
}
