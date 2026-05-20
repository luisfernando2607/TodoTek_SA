<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    #[OA\Get(
        path: '/api/products',
        summary: 'Listar productos paginados',
        tags: ['Productos'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'search',      in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'category_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'active',      in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'per_page',    in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Listado paginado')]
    )]
    public function index(Request $request): JsonResponse
    {
        $data = $this->productService->paginate(
            $request->only(['search', 'category_id', 'active']),
            $request->integer('per_page', 15)
        );
        return response()->json($data);
    }

    #[OA\Post(
        path: '/api/products',
        summary: 'Crear producto',
        tags: ['Productos'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['name', 'sku', 'price', 'category_id'],
                    properties: [
                        new OA\Property(property: 'name',        type: 'string'),
                        new OA\Property(property: 'sku',         type: 'string'),
                        new OA\Property(property: 'price',       type: 'number'),
                        new OA\Property(property: 'category_id', type: 'integer'),
                        new OA\Property(property: 'description', type: 'string'),
                        new OA\Property(property: 'tax_rate',    type: 'number'),
                        new OA\Property(property: 'stock',       type: 'integer'),
                        new OA\Property(property: 'stock_min',   type: 'integer'),
                        new OA\Property(property: 'active',      type: 'boolean'),
                        new OA\Property(property: 'images[]',    type: 'array', items: new OA\Items(type: 'string', format: 'binary')),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Producto creado'),
            new OA\Response(response: 422, description: 'Error de validación'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'sku'         => 'required|string|unique:products,sku',
            'price'       => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'tax_rate'    => 'nullable|numeric|min:0|max:100',
            'stock'       => 'nullable|integer|min:0',
            'stock_min'   => 'nullable|integer|min:0',
            'active'      => 'nullable|boolean',
            'images'      => 'nullable|array',
            'images.*'    => 'image|max:2048',
        ]);

        $product = $this->productService->create(
            $request->except('images'),
            $request->file('images', [])
        );

        return response()->json($product, 201);
    }

    #[OA\Get(
        path: '/api/products/{id}',
        summary: 'Ver detalle de producto',
        tags: ['Productos'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Detalle del producto'),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function show(Product $product): JsonResponse
    {
        return response()->json(
            $product->load(['category', 'images', 'stockMovements.user'])
        );
    }

    #[OA\Post(
        path: '/api/products/{id}',
        summary: 'Actualizar producto (usa POST + _method=PUT para multipart)',
        tags: ['Productos'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: '_method',     type: 'string', example: 'PUT'),
                        new OA\Property(property: 'name',        type: 'string'),
                        new OA\Property(property: 'price',       type: 'number'),
                        new OA\Property(property: 'category_id', type: 'integer'),
                        new OA\Property(property: 'images[]',    type: 'array', items: new OA\Items(type: 'string', format: 'binary')),
                    ]
                )
            )
        ),
        responses: [new OA\Response(response: 200, description: 'Producto actualizado')]
    )]
    public function update(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'name'        => 'sometimes|string|max:255',
            'sku'         => 'sometimes|string|unique:products,sku,' . $product->id,
            'price'       => 'sometimes|numeric|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'description' => 'nullable|string',
            'tax_rate'    => 'nullable|numeric|min:0|max:100',
            'stock_min'   => 'nullable|integer|min:0',
            'active'      => 'nullable|boolean',
            'images'      => 'nullable|array',
            'images.*'    => 'image|max:2048',
        ]);

        $updated = $this->productService->update(
            $product,
            $request->except(['images', '_method']),
            $request->file('images', [])
        );

        return response()->json($updated);
    }

    #[OA\Delete(
        path: '/api/products/{id}',
        summary: 'Eliminar producto (soft-delete)',
        tags: ['Productos'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Producto eliminado'),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function destroy(Product $product): JsonResponse
    {
        $this->productService->delete($product);
        return response()->json(['message' => 'Producto eliminado correctamente']);
    }

    #[OA\Delete(
        path: '/api/products/{id}/images/{imageId}',
        summary: 'Eliminar imagen de un producto',
        tags: ['Productos'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id',      in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'imageId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Imagen eliminada')]
    )]
    public function destroyImage(Product $product, ProductImage $image): JsonResponse
    {
        $this->productService->deleteImage($image);
        return response()->json(['message' => 'Imagen eliminada correctamente']);
    }

    #[OA\Patch(
        path: '/api/products/{id}/images/{imageId}/main',
        summary: 'Establecer imagen principal',
        tags: ['Productos'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id',      in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'imageId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Imagen principal actualizada')]
    )]
    public function setMainImage(Product $product, ProductImage $image): JsonResponse
    {
        $this->productService->setMainImage($product, $image);
        return response()->json(['message' => 'Imagen principal actualizada']);
    }
}
