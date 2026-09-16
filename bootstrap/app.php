<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Domains\Catalog\Domain\Exceptions\CategoryHasProductsException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Domains\Identity\Infrastructure\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        // 404 Not Found (Model or Route missing)
        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->error('العنصر المطلوب غير موجود.', Response::HTTP_NOT_FOUND);
            }
        });

        // 409 Conflict (Custom Domain Exception: Category has products)
        $exceptions->render(function (CategoryHasProductsException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->error($e->getMessage(), Response::HTTP_CONFLICT);
            }
        });

        // 422 Unprocessable Entity (Validation Errors)
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->error('فشل في التحقق من صحة البيانات المدخلة.', Response::HTTP_UNPROCESSABLE_ENTITY, $e->errors());
            }
        });

        // 401 Unauthenticated
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->error('غير مصرح، يرجى تسجيل الدخول.', Response::HTTP_UNAUTHORIZED);
            }
        });

        // 403 Forbidden
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->error('ليس لديك صلاحية للقيام بهذا الإجراء.', Response::HTTP_FORBIDDEN);
            }
        });

        // 405 Method Not Allowed
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->error('طريقة الطلب غير مسموح بها لهذا المسار.', Response::HTTP_METHOD_NOT_ALLOWED);
            }
        });

        // 429 Too Many Requests
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->is('api/*')) {
                $retryAfter = $e->getHeaders()['Retry-After'] ?? 60;

                return response()->error(
                    'لقد تجاوزت الحد المسموح من المحاولات. يرجى المحاولة مرة أخرى لاحقًا.', 
                    Response::HTTP_TOO_MANY_REQUESTS, 
                    ['retry_after_seconds' => (int) $retryAfter]
                );
            }
        });
        //403 Forbidden
        $exceptions->render(function (AuthorizationException|HttpException $e, Request $request) {
            if ($request->is('api/*')) {
                $status = $e instanceof HttpException ? $e->getStatusCode() : Response::HTTP_FORBIDDEN;

                if ($status === Response::HTTP_FORBIDDEN) {
                    return response()->error('ليس لديك صلاحية للقيام بهذا الإجراء أو أن الحساب غير مفعل.', Response::HTTP_FORBIDDEN);
                }
            }
        });

        // 500 Internal Server Error (Production Catch-all)
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') && config('app.env') === 'production') {
                return response()->error('حدث خطأ غير متوقع في الخادم.', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });

        

    })->create();
