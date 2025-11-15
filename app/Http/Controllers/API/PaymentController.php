<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Services\Payment\PaymentInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentInterface $paymentService,
        protected OrderService $orderService
    ) {}

    /**
     * Handle Midtrans notification webhook
     */
    public function notification(Request $request): JsonResponse
    {
        try {
            $result = $this->paymentService->handleNotification($request->all());

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

            // Find order and update payment status
            $order = $this->orderService->getOrderByNumber($result['order_number']);

            if ($order) {
                $this->orderService->updatePaymentStatus(
                    $order->id,
                    $result['payment_status'],
                    $result['transaction_id'] ?? null
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification processed'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
