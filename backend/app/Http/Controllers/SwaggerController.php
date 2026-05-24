<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Todostock API',
    description: 'API REST para el sistema de gestión de productos Todostock',
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Servidor local'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Ingresa el token obtenido del login'
)]
class SwaggerController extends Controller
{
    // Este controlador solo sirve para las anotaciones globales de OpenAPI
}
