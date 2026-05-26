<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Dompdf\Dompdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoiceService) {}

    #[OA\Get(
        path: '/api/invoices',
        summary: 'Listar facturas',
        tags: ['Facturas'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Listado de facturas')]
    )]
    public function index(Request $request): JsonResponse
    {
        $invoices = Invoice::with(['client', 'user', 'items'])
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15));

        return response()->json($invoices);
    }

    #[OA\Post(
        path: '/api/invoices',
        summary: 'Crear factura',
        tags: ['Facturas'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['client_id', 'items'],
                properties: [
                    new OA\Property(property: 'client_id', type: 'integer', example: 1),
                    new OA\Property(property: 'notes',     type: 'string'),
                    new OA\Property(
                        property: 'items',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'product_id', type: 'integer'),
                                new OA\Property(property: 'quantity',   type: 'integer'),
                            ]
                        )
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Factura creada'),
            new OA\Response(response: 422, description: 'Error de validación o stock insuficiente'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id'          => 'required|exists:clients,id',
            'notes'              => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        try {
            $invoice = $this->invoiceService->create(
                $data['client_id'],
                $data['items'],
                $data['notes'] ?? ''
            );
            return response()->json($invoice, 201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    #[OA\Get(
        path: '/api/invoices/{id}',
        summary: 'Ver detalle de factura',
        tags: ['Facturas'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Detalle de factura')]
    )]
    public function show(Invoice $invoice): JsonResponse
    {
        return response()->json($invoice->load(['client', 'user', 'items.product']));
    }

    public function pdf(Invoice $invoice): \Illuminate\Http\Response
    {
        $invoice->load(['client', 'items']);

        $html = view('pdf.invoice', compact('invoice'))->render();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response(
            $dompdf->output(),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $invoice->invoice_number . '.pdf"',
            ]
        );
    }

    #[OA\Patch(
        path: '/api/invoices/{id}/cancel',
        summary: 'Cancelar factura y revertir stock',
        tags: ['Facturas'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Factura cancelada'),
            new OA\Response(response: 422, description: 'La factura ya está cancelada'),
        ]
    )]
    public function cancel(Invoice $invoice): JsonResponse
    {
        try {
            $invoice = $this->invoiceService->cancel($invoice);
            return response()->json($invoice);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
