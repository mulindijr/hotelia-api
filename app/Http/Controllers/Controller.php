<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Hotelia Multi-Tenant Hotel Management API",
 *     description="Production-grade RESTful API documentation for Hotelia multi-tenant hotel backend application.",
 *     @OA\Contact(
 *         email="support@hotelia.app"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Primary API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter Sanctum API token in format: Bearer {token}"
 * )
 */
abstract class Controller
{
    //
}
