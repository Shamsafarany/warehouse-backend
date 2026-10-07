<?php

namespace App\Domains\Order\Presentation\Docs;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Orders",
    description: "API Endpoints for customer order management, checkout, and administrative status controls"
)]
class OrderOpenApi
{
    #[OA\Get(
        path: "/api/v1/orders/history",
        tags: ["Orders"],
        summary: "Get customer order history (Customer)",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "page", in: "query", required: false, schema: new OA\Schema(type: "integer", example: 1))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "status_code", type: "integer", example: 200),
                        new OA\Property(property: "message", type: "string", example: "تم استرجاع سجل الطلبات بنجاح")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    #[OA\Post(
        path: "/api/v1/orders",
        tags: ["Orders"],
        summary: "Place a new order / Checkout from active cart (Customer)",
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 201,
                description: "Order placed successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "status_code", type: "integer", example: 201),
                        new OA\Property(property: "message", type: "string", example: "تم إنشاء الطلب بنجاح")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(
                response: 422,
                description: "Unprocessable Entity - Empty cart, inactive product, or insufficient stock",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "status_code", type: "integer", example: 422),
                        new OA\Property(property: "message", type: "string", example: "فشل في إنشاء الطلب.")
                    ]
                )
            )
        ]
    )]
    #[OA\Get(
        path: "/api/v1/orders/{order}",
        tags: ["Orders"],
        summary: "View single order details with ownership enforcement (Customer / Admin)",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "order", 
                in: "path", 
                required: true, 
                schema: new OA\Schema(type: "string", example: "01m4b5j7vbd037fr5z3ss9wv5p")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "status_code", type: "integer", example: 200),
                        new OA\Property(property: "message", type: "string", example: "تم استرجاع تفاصيل الطلب بنجاح")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden - Unauthorized access to another user's order"),
            new OA\Response(response: 404, description: "Order not found")
        ]
    )]
    #[OA\Patch(
        path: "/api/v1/orders/{order}/cancel",
        tags: ["Orders"],
        summary: "Cancel a pending order and restore inventory (Customer)",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "order", 
                in: "path", 
                required: true, 
                schema: new OA\Schema(type: "string", example: "01m4b5j7vbd037fr5z3ss9wv5p")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Order cancelled and stock restored successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "status_code", type: "integer", example: 200),
                        new OA\Property(property: "message", type: "string", example: "تم إلغاء الطلب واسترجاع المخزون بنجاح")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 404, description: "Order not found"),
            new OA\Response(response: 422, description: "Unprocessable Entity - Order cannot be cancelled in current state")
        ]
    )]
    #[OA\Get(
        path: "/api/v1/admin/orders",
        tags: ["Admin - Orders"],
        summary: "List all system-wide orders (Admin Only)",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "page", in: "query", required: false, schema: new OA\Schema(type: "integer", example: 1))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "status_code", type: "integer", example: 200),
                        new OA\Property(property: "message", type: "string", example: "تم استرجاع قائمة الطلبات بنجاح")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden - Admin access required")
        ]
    )]
    #[OA\Patch(
        path: "/api/v1/admin/orders/{order}/status",
        tags: ["Admin - Orders"],
        summary: "Update order status via state machine validation (Admin Only)",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "order", 
                in: "path", 
                required: true, 
                schema: new OA\Schema(type: "string", example: "01m4b5j7vbd037fr5z3ss9wv5p")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["status"],
                properties: [
                    new OA\Property(
                        property: "status", 
                        type: "string", 
                        enum: ["pending", "confirmed", "processing", "shipped", "delivered", "cancelled"], 
                        example: "confirmed"
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Order status updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "status_code", type: "integer", example: 200),
                        new OA\Property(property: "message", type: "string", example: "تم تحديث حالة الطلب بنجاح")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden - Admin access required"),
            new OA\Response(response: 404, description: "Order not found"),
            new OA\Response(
                response: 422, 
                description: "Unprocessable Entity - Invalid state transition or validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "status_code", type: "integer", example: 422),
                        new OA\Property(property: "message", type: "string", example: "فشل في تحديث حالة الطلب.")
                    ]
                )
            )
        ]
    )]
    public function definitions()
    {
        // Placeholder method for structural attribute grouping
    }
}