<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CategoryController extends Controller
{
    #[OA\Get(
        path: '/api/categories',
        summary: 'Listar categorías',
        tags: ['Categorías'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Listado de categorías con conteo de productos')]
    )]
    public function index(): JsonResponse
    {
        $categories = Category::withCount('products')
            ->orderByDesc('id')
            ->get();

        return response()->json($categories);
    }

    #[OA\Post(
        path: '/api/categories',
        summary: 'Crear categoría',
        tags: ['Categorías'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'slug'],
                properties: [
                    new OA\Property(property: 'name',        type: 'string', example: 'Electrónica'),
                    new OA\Property(property: 'slug',        type: 'string', example: 'electronica'),
                    new OA\Property(property: 'description', type: 'string', example: 'Equipos y dispositivos electrónicos'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Categoría creada'),
            new OA\Response(response: 422, description: 'Error de validación'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:categories,slug',
            'description' => 'nullable|string|max:500',
        ]);

        return response()->json(Category::create($data), 201);
    }

    #[OA\Get(
        path: '/api/categories/{id}',
        summary: 'Ver categoría',
        tags: ['Categorías'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Detalle de la categoría con conteo de productos')]
    )]
    public function show(Category $category): JsonResponse
    {
        return response()->json($category->loadCount('products'));
    }

    #[OA\Put(
        path: '/api/categories/{id}',
        summary: 'Actualizar categoría',
        tags: ['Categorías'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name',        type: 'string'),
                    new OA\Property(property: 'slug',        type: 'string'),
                    new OA\Property(property: 'description', type: 'string'),
                ]
            )
        ),
        responses: [new OA\Response(response: 200, description: 'Categoría actualizada')]
    )]
    public function update(Request $request, Category $category): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'slug'        => 'sometimes|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string|max:500',
        ]);

        $category->update($data);
        return response()->json($category);
    }

    #[OA\Delete(
        path: '/api/categories/{id}',
        summary: 'Eliminar categoría',
        tags: ['Categorías'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Categoría eliminada')]
    )]
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();
        return response()->json(['message' => 'Categoría eliminada correctamente']);
    }
}
