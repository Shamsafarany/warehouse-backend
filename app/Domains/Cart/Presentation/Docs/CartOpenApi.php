<?php

namespace App\Domains\Cart\Presentation\Docs;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Cart",
    description: "API Endpoints for managing customer shopping cart, items, and inventory validation"
)]
class CartOpenApi
{
    #[OA\Get(
        path: "/api/v1/cart",
        tags: ["Cart"],
        summary: "View the authenticated user's cart",
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "status_code", type: "integer", example: 200),
                        new OA\Property(property: "message", type: "string", example: "تم استرجاع السلة بنجاح")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    #[OA\Post(
        path: "/api/v1/cart",
        tags: ["Cart"],
        summary: "Add a product to the cart",
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["product_id", "quantity"],
                properties: [
                    new OA\Property(property: "product_id", type: "integer", example: 1),
                    new OA\Property(property: "quantity", type: "integer", minimum: 1, example: 2)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Product added to cart successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "status_code", type: "integer", example: 201),
                        new OA\Property(property: "message", type: "string", example: "تمت إضافة المنتج إلى السلة بنجاح")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(
                response: 409, 
                description: "Conflict - Insufficient stock (InsufficientStockException)",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "status_code", type: "integer", example: 409),
                        new OA\Property(property: "message", type: "string", example: "لا يمكن إخراج كمية أكبر من المخزون المتاح.")
                    ]
                )
            ),
            new OA\Response(
                response: 422, 
                description: "Unprocessable Entity - Inactive product or validation failure",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "status_code", type: "integer", example: 422),
                        new OA\Property(property: "message", type: "string", example: "المنتج غير فعال ولا يمكن إضافته إلى السلة.")
                    ]
                )
            )
        ]
    )]
    #[OA\Patch(
        path: "/api/v1/cart/items/{cartItemId}",
        tags: ["Cart"],
        summary: "Update cart item quantity",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "cartItemId", 
                in: "path", 
                required: true, 
                schema: new OA\Schema(type: "string", example: "1")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["quantity"],
                properties: [
                    new OA\Property(property: "quantity", type: "integer", minimum: 1, example: 5)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Cart item quantity updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "status_code", type: "integer", example: 200),
                        new OA\Property(property: "message", type: "string", example: "تم تحديث كمية المنتج في السلة بنجاح")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 404, description: "Cart item not found or belongs to another user (IDOR protection)"),
            new OA\Response(response: 409, description: "Conflict - Insufficient stock"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    #[OA\Delete(
        path: "/api/v1/cart/items/{cartItemId}",
        tags: ["Cart"],
        summary: "Remove a single item from the cart",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "cartItemId", 
                in: "path", 
                required: true, 
                schema: new OA\Schema(type: "string", example: "1")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Item removed from cart successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "status_code", type: "integer", example: 200),
                        new OA\Property(property: "message", type: "string", example: "تم إزالة المنتج من السلة بنجاح")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 404, description: "Cart item not found")
        ]
    )]
    #[OA\Delete(
        path: "/api/v1/cart",
        tags: ["Cart"],
        summary: "Empty the entire cart",
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Cart emptied successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "status_code", type: "integer", example: 200),
                        new OA\Property(property: "message", type: "string", example: "تم إفراغ السلة بنجاح")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function definitions()
    {
        // Placeholder method for structural attribute grouping
    }
}