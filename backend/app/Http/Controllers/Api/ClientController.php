<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ClientController extends Controller
{
    #[OA\Get(
        path: '/api/clients',
        summary: 'Listar clientes',
        tags: ['Clientes'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'search',   in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Listado de clientes')]
    )]
    public function index(Request $request): JsonResponse
    {
        $clients = Client::when($request->search, fn($q, $v) =>
                $q->where('name', 'ilike', "%{$v}%")
                  ->orWhere('identification', 'ilike', "%{$v}%")
            )
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15));

        return response()->json($clients);
    }

    #[OA\Post(
        path: '/api/clients',
        summary: 'Crear cliente',
        tags: ['Clientes'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'identification'],
                properties: [
                    new OA\Property(property: 'name',           type: 'string',  example: 'Juan Pérez'),
                    new OA\Property(property: 'identification', type: 'string',  example: '0912345678'),
                    new OA\Property(property: 'email',          type: 'string',  example: 'juan@email.com'),
                    new OA\Property(property: 'phone',          type: 'string',  example: '0991234567'),
                    new OA\Property(property: 'address',        type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Cliente creado'),
            new OA\Response(response: 422, description: 'Error de validación'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'identification' => 'required|string|max:20|unique:clients,identification',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:500',
        ]);

        return response()->json(Client::create($data), 201);
    }

    #[OA\Get(
        path: '/api/clients/{id}',
        summary: 'Ver cliente',
        tags: ['Clientes'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Detalle del cliente')]
    )]
    public function show(Client $client): JsonResponse
    {
        return response()->json($client->load('invoices'));
    }

    #[OA\Put(
        path: '/api/clients/{id}',
        summary: 'Actualizar cliente',
        tags: ['Clientes'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name',    type: 'string'),
                    new OA\Property(property: 'email',   type: 'string'),
                    new OA\Property(property: 'phone',   type: 'string'),
                    new OA\Property(property: 'address', type: 'string'),
                    new OA\Property(property: 'active',  type: 'boolean'),
                ]
            )
        ),
        responses: [new OA\Response(response: 200, description: 'Cliente actualizado')]
    )]
    public function update(Request $request, Client $client): JsonResponse
    {
        $data = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'identification' => 'sometimes|string|max:20|unique:clients,identification,' . $client->id,
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:500',
            'active'         => 'nullable|boolean',
        ]);

        $client->update($data);
        return response()->json($client);
    }

    #[OA\Delete(
        path: '/api/clients/{id}',
        summary: 'Eliminar cliente',
        tags: ['Clientes'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Cliente eliminado')]
    )]
    public function destroy(Client $client): JsonResponse
    {
        $client->delete();
        return response()->json(['message' => 'Cliente eliminado correctamente']);
    }
}
