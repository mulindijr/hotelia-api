<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Hotelia Multi-Tenant Hotel Management API",
    description: "Production-grade RESTful API documentation for Hotelia multi-tenant hotel backend application.",
    contact: new OA\Contact(email: "support@hotelia.app")
)]
#[OA\Server(
    url: "http://localhost:8000",
    description: "Primary API Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Enter Sanctum API token in format: Bearer {token}"
)]
abstract class Controller
{
    //
}
