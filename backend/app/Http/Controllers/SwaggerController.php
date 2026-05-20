<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Todostock API',
    version: '1.0.0',
    description: 'Sistema de gestion de productos Todostock'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'apiKey',
    in: 'header',
    name: 'Authorization',
    description: 'Bearer {token}'
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'API Server'
)]
class SwaggerController extends Controller
{
    #[OA\Get(
        path: '/api/health',
        summary: 'Health check',
        tags: ['Sistema'],
        responses: [
            new OA\Response(response: 200, description: 'OK')
        ]
    )]
    public function health(): void {}
}
