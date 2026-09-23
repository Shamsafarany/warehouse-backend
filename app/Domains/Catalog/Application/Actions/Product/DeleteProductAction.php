<?php

namespace App\Domains\Catalog\Application\Actions\Product;

use App\Domains\Catalog\Infrastructure\Models\Product;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

class DeleteProductAction
{
    public function execute(Product $product): void
    {
        Gate::authorize('delete', $product);
        if ($product->orderItems()->exists()) {
            throw new HttpException(
                Response::HTTP_CONFLICT, 
                'لا يمكن حذف المنتج لأنه مرتبط بطلبات موجودة مسبقاً.'
            );
        }

        $product->delete();
    }
}