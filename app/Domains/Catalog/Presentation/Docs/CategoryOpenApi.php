<?php

namespace App\Domains\Catalog\Presentation\Docs;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Catalog - Categories",
    description: "API Endpoints for managing and viewing product categories"
)]
class CategoryOpenApi
{
    #[OA\Get(
        path: "/api/v1/categories",
        tags: ["Catalog - Categories"],
        summary: "List paginated categories (Public)",
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
                        new OA\Property(property: "message", type: "string", example: "تم استرجاع قائمة التصنيفات بنجاح")
                    ]
                )
            )
        ]
    )]
    #[OA\Get(
        path: "/api/v1/categories/{id}",
        tags: ["Catalog - Categories"],
        summary: "Get category details (Public)",
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
            new OA\Response(response: 404, description: "Category not found")
        ]
    )]
    #[OA\Post(
        path: "/api/v1/admin/categories",
        tags: ["Catalog - Categories"],
        summary: "Create a new category (Admin Only)",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Electronics"),
                    new OA\Property(property: "description", type: "string", example: "Electronic devices and accessories")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Category created successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden - Admin access required"),
            new OA\Response(
                response: 422, 
                description: "Unprocessable Entity - Validation Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "status_code", type: "integer", example: 422),
                        new OA\Property(property: "message", type: "string", example: "فشل في التحقق من صحة البيانات المدخلة."),
                        new OA\Property(
                            property: "errors",
                            type: "object",
                            example: ["name" => ["حقل اسم التصنيف إجباري."]]
                        )
                    ]
                )
            ),
        ]
    )]
   #[OA\Patch(
        path: "/api/v1/admin/categories/{id}",
        tags: ["Catalog - Categories"],
        summary: "Partially update a category (Admin Only)",
        security: [["bearerAuth" => []]],
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
                    new OA\Property(property: "name", type: "string", example: "Updated Electronics"),
                    new OA\Property(property: "description", type: "string", example: "Updated description")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Category updated successfully"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden - Admin access required"),
            new OA\Response(response: 404, description: "Category not found")
        ]
    )]
    #[OA\Delete(
        path: "/api/v1/admin/categories/{id}",
        tags: ["Catalog - Categories"],
        summary: "Delete a category (Admin Only)",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id", 
                in: "path", 
                required: true, 
                schema: new OA\Schema(type: "string", format: "ulid", example: "01H4Z3ABC1234567890ABCDEFG")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Category deleted successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden - Admin access required"),
            new OA\Response(response: 404, description: "Category not found"),
            new OA\Response(response: 409, description: "Conflict - Can't delete category because it contains products")
        ]
    )]
    public function definitions()
    {
        // Placeholder method for structural attribute grouping
    }
}