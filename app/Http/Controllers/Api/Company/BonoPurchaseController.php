<?php

namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\BonoPurchaseRequest;
use App\Models\BonoCatalog;
use App\Models\CompanyProfile;
use App\Models\BonoPurchase;
use App\Services\BonoPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador para la gestión de compras de bonos por parte de los perfiles de empresa.
 */
class BonoPurchaseController extends Controller
{
    protected BonoPaymentService $paymentService;

    public function __construct(BonoPaymentService $paymentService)
    {
        // Se inyecta automáticamente el servicio de pago (simulación de PayPal).
        $this->paymentService = $paymentService;
    }

    /**
     * Muestra una lista paginada de todas las compras de bonos realizadas por la empresa autenticada.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // 1. Obtener el perfil de la empresa del usuario autenticado
        $companyProfile = $user->companyProfile;

        if (!$companyProfile) {
            return response()->json([
                'message' => 'El usuario no tiene un perfil de empresa asociado para realizar consultas.'
            ], 403); // 403 Forbidden
        }

        // 2. Obtener las compras, cargando la relación con el bono
        $purchases = $companyProfile->purchases()
            ->with('bonoCatalog')
            ->orderByDesc('purchase_date')
            ->paginate(10);

        return response()->json($purchases);
    }

    /**
     * Inicia el proceso de compra de un bono, simulando la redirección a la pasarela de pago (PayPal).
     */
    public function store(BonoPurchaseRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // 1. Obtener el bono del catálogo y el perfil de la empresa
        $bono = BonoCatalog::findOrFail($request->bono_catalog_id);

        /** @var \App\Models\CompanyProfile $companyProfile */
        $companyProfile = $user->companyProfile;

        if (!$companyProfile) {
            return response()->json([
                'message' => 'Requiere un perfil de empresa activo para realizar compras.'
            ], 403);
        }

        try {
            // 2. Iniciar la compra a través del servicio de pago
            $result = $this->paymentService->initiatePurchase($companyProfile, $bono);

            return response()->json([
                'message' => 'Compra iniciada. Redirigiendo a pasarela de pago.',
                'purchase_id' => $result['purchase']->id,
                'redirect_url' => $result['redirect_url'], // URL simulada para redirigir al cliente a PayPal
                'payment_info' => [
                    'transaction_id' => $result['purchase']->transaction_id,
                    'status' => $result['purchase']->payment_status,
                ],
            ], 202); // 202 Accepted, la solicitud ha sido aceptada.

        } catch (\Exception $e) {
            \Log::error("Error al iniciar compra de bono: " . $e->getMessage());
            return response()->json([
                'message' => 'Error interno al procesar el pago.',
            ], 500);
        }
    }

    /**
     * Maneja el callback (webhook/retorno) de la pasarela de pago (simulación de PayPal).
     * Esta ruta NO requiere autenticación de usuario ya que es llamada por la pasarela de pago.
     */
    public function handleCallback(Request $request, int $purchase_id): JsonResponse
    {
        // NOTA DE SEGURIDAD: En producción, se debe verificar el token/firma de seguridad
        // proporcionado por PayPal para garantizar que la solicitud es legítima.

        // Simulación: verificamos un token simple y un parámetro 'status'
        if (empty($request->query('token'))) {
            return response()->json(['message' => 'Token de seguridad ausente o inválido.'], 401);
        }

        // Simulación: el parámetro 'status' indica el resultado del pago
        $paymentSuccess = ($request->query('status') === 'success');
        $finalTransactionId = $request->query('transaction_id');

        $purchase = $this->paymentService->handlePaymentCallback(
            $purchase_id,
            $paymentSuccess,
            $finalTransactionId
        );

        if (!$purchase) {
            return response()->json(['message' => 'Compra no encontrada o intento de callback repetido/inválido.'], 404);
        }

        if ($purchase->payment_status === BonoPaymentService::STATUS_COMPLETED) {
            // Lógica de cumplimiento de la orden:
            // - Generar el bono o código de cupón
            // - Enviar correo electrónico de confirmación al usuario
            // - Registrar el evento en el log
            return response()->json([
                'message' => 'Pago completado exitosamente. La compra ha sido confirmada.',
                'purchase' => $purchase->load('bonoCatalog'),
            ]);
        }

        // Si el pago falló
        return response()->json([
            'message' => 'El pago falló. Por favor, intente de nuevo.',
            'purchase' => $purchase->load('bonoCatalog'),
        ], 400); // 400 Bad Request
    }
}
