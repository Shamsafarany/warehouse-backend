<?php

namespace App\Domains\Catalog\Presentation\Docs;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Catalog - Products",
    description: "API Endpoints for managing and viewing catalog products"
)]
class ProductOpenApi
{
    #[OA\Get(
        path: "/api/v1/products",
        tags: ["Catalog - Products"],
        summary: "List paginated products with category and inventory (Public)",
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
                        new OA\Property(property: "message", type: "string", example: "تم استرجاع قائمة المنتجات بنجاح")
                    ]
                )
            )
        ]
    )]
    #[OA\Get(
        path: "/api/v1/products/{id}",
        tags: ["Catalog - Products"],
        summary: "Get product details with relations (Public)",
        parameters: [
            new OA\Parameter(
                name: "id", 
                in: "path", 
                required: true, 
                schema: new OA\Schema(type: "string", format: "ulid", example: "01H4Z3ABC1234567890ABCDEFG")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 404, description: "Product not found")
        ]
    )]
    #[OA\Post(
        path: "/api/v1/admin/products",
        tags: ["Catalog - Products"],
        summary: "Create a new product with initial inventory (Admin Only)",
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["category_id", "name", "price", "stock"],
                properties: [
                    new OA\Property(property: "category_id", type: "string", example: "01H4Z3ABC1234567890ABCDEFG"),
                    new OA\Property(property: "name", type: "string", example: "Wireless Mouse"),
                    new OA\Property(property: "description", type: "string", example: "Ergonomic wireless mouse with USB receiver"),
                    new OA\Property(property: "price", type: "number", format: "float", example: 29.99),
                    new OA\Property(property: "stock", type: "integer", example: 100),
                    new OA\Property(property: "is_active", type: "boolean", example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Product created successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden - Admin access required"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    #[OA\Patch(
        path: "/api/v1/admin/products/{id}",
        tags: ["Catalog - Products"],
        summary: "Partially update a product (Admin Only)",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id", 
                in: "path", 
                required: true, 
                schema: new OA\Schema(type: "string", format: "ulid", example: "01H4Z3ABC1234567890ABCDEFG")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Updated Wireless Mouse"),
                    new OA\Property(property: "price", type: "number", format: "float", example: 24.99)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Product updated successfully"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden - Admin access required"),
            new OA\Response(response: 404, description: "Product not found")
        ]
    )]
    #[OA\Delete(
        path: "/api/v1/admin/products/{id}",
        tags: ["Catalog - Products"],
        summary: "Delete a product (Admin Only)",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id", 
                in: "path", 
                required: true, 
                schema: new OA\Schema(type: "string", format: "ulid", example: "01H4Z3ABC1234567890ABCDEFG")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Product deleted successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden - Admin access required"),
            new OA\Response(response: 404, description: "Product not found"),
            new OA\Response(response: 409, description: "Conflict - Cannot delete product linked to active orders")
        ]
    )]
    public function definitions()
    {
        // Placeholder method for structural attribute grouping
    }
}