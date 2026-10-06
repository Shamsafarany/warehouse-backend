<?php

namespace App\Providers;

use App\Domains\Cart\Infrastructure\Models\Cart;
use App\Domains\Cart\Policies\CartPolicy;
use App\Domains\Catalog\Infrastructure\Models\Category;
use App\Domains\Catalog\Infrastructure\Models\Product;
use App\Domains\Catalog\Policies\CategoryPolicy;
use App\Domains\Catalog\Policies\ProductPolicy;
use App\Domains\Inventory\Infrastructure\Models\Inventory;
use App\Domains\Inventory\Policies\InventoryPolicy;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Gate $gate): void
    {
        Response::macro('success', function (
            mixed $data, 
            string $message = 'تمت العملية بنجاح', 
            int $status = 200, 
            array $headers = []
        ) {
            if ($data instanceof JsonResource || $data instanceof AnonymousResourceCollection) {
                $response = $data->additional([
                    'success' => true,
                    'status_code' => $status,
                    'message' => $message,
                ])->response()->setStatusCode($status);

                foreach ($headers as $key => $value) {
                    $response->header($key, $value);
                }

                return $response;
            }

            return response()->json([
                'success' => true,
                'status_code' => $status,
                'message' => $message,
                'data' => $data,
            ], $status, $headers);
        });

        Response::macro('error', function (
            string $message, 
            int $status = 400, 
            mixed $errors = null
        ) {
            $response = [
                'success' => false,
                'status_code' => $status,
                'message' => $message,
            ];

            if ($errors !== null) {
                $response['errors'] = $errors;
            }

            return response()->json($response, $status);
        });
        $gate->policy(Cart::class, CartPolicy::class);
        $gate->policy(Inventory::class, InventoryPolicy::class);
        $gate->policy(Category::class, CategoryPolicy::class);
        $gate->policy(Product::class, ProductPolicy::class);
    }
}
