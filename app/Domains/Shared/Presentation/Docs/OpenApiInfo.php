<?php

namespace App\Domains\Shared\Presentation\Docs;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Warehouse E-commerce & WMS API Documentation",
    description: "API documentation for the Modular Monolith Warehouse System (Admin & Storefront endpoints)",
    contact: new OA\Contact(email: "admin@warehouse.local")
)]
#[OA\Server(
    url: "http://localhost:8000",    
    description: "API Server"
)]
class OpenApiInfo
{
    // Global OpenAPI Info Definition holder
}