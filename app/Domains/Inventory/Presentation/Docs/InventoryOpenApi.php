<?php

namespace App\Domains\Catalog\Presentation\Docs;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Catalog - Inventories",
    description: "API Endpoints for managing and viewing product inventories, stock adjustments, and audit history"
)]
class InventoryOpenApi
{
    #[OA\Get(
        path: "/api/v1/inventories",
        tags: ["Catalog - Inventories"],
        summary: "List paginated inventories (Public)",
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
                        new OA\Property(property: "message", type: "string", example: "تم استرجاع المخزون بنجاح")
                    ]
                )
            )
        ]
    )]
    #[OA\Get(
        path: "/api/v1/inventories/{id}",
        tags: ["Catalog - Inventories"],
        summary: "Get inventory details (Public)",
        parameters: [
            new OA\Parameter(
                name: "id", 
                in: "path", 
                required: true, 
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 404, description: "Inventory not found")
        ]
    )]
    #[OA\Patch(
        path: "/api/v1/admin/inventories/{id}",
        tags: ["Catalog - Inventories"],
        summary: "Update inventory metadata such as reserved quantity (Admin Only)",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id", 
                in: "path", 
                required: true, 
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["reserved_quantity"],
                properties: [
                    new OA\Property(property: "reserved_quantity", type: "integer", example: 5)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Inventory metadata updated successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden - Admin access required"),
            new OA\Response(response: 404, description: "Inventory not found"),
            new OA\Response(
                response: 422, 
                description: "Unprocessable Entity - Validation Error or Prohibited Field",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "status_code", type: "integer", example: 422),
                        new OA\Property(property: "message", type: "string", example: "فشل في التحقق من صحة البيانات المدخلة."),
                        new OA\Property(
                            property: "errors",
                            type: "object",
                            example: ["stock_quantity" => ["لا يمكن تعديل المخزون الفعلي من هنا."]]
                        )
                    ]
                )
            ),
        ]
    )]
    #[OA\Post(
        path: "/api/v1/admin/inventories/{id}/adjust",
        tags: ["Catalog - Inventories"],
        summary: "Adjust physical stock (In / Out) and log movement (Admin Only)",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id", 
                in: "path", 
                required: true, 
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["type", "quantity", "notes"],
                properties: [
                    new OA\Property(property: "type", type: "string", enum: ["in", "out"], example: "in"),
                    new OA\Property(property: "quantity", type: "integer", minimum: 1, example: 50),
                    new OA\Property(property: "notes", type: "string", example: "شحنة توريد جديدة من المصنع"),
                    new OA\Property(property: "reference_id", type: "string", example: "PO-9988")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Stock adjusted and movement logged successfully"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden - Admin access required"),
            new OA\Response(response: 404, description: "Inventory not found"),
            new OA\Response(
                response: 409, 
                description: "Conflict - Insufficient stock for 'out' adjustment",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "status_code", type: "integer", example: 409),
                        new OA\Property(property: "message", type: "string", example: "لا يمكن إخراج كمية أكبر من المخزون المتاح.")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    #[OA\Get(
        path: "/api/v1/admin/inventories/{id}/movements",
        tags: ["Catalog - Inventories"],
        summary: "View stock movement audit history (Admin Only)",
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id", 
                in: "path", 
                required: true, 
                schema: new OA\Schema(type: "integer", example: 1)
            ),
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
                        new OA\Property(property: "message", type: "string", example: "تم استرجاع سجل حركات المخزون بنجاح")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden - Admin access required"),
            new OA\Response(response: 404, description: "Inventory not found")
        ]
    )]
    public function definitions()
    {
        // Placeholder method for structural attribute grouping
    }
}