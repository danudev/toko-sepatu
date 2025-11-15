<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Services\OrderService;
use App\Services\Payment\PaymentInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected PaymentInterface $paymentService
    ) {}

    /**
     * Create new order and get payment token
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        try {
            $sessionId = $request->header('X-Session-ID');

            if (!$sessionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session ID required'
                ], 400);
            }

            // Create order
            $order = $this->orderService->createOrder([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ], $sessionId);

            // Get payment token from Midtrans
            $paymentResult = $this->paymentService->createTransaction($order);

            if (!$paymentResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create payment transaction'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'order_number' => $order->order_number,
                    'snap_token' => $paymentResult['snap_token'],
                    'total_amount' => $order->total_amount
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order by order number
     */
    public function show(string $orderNumber): JsonResponse
    {
        $order = $this->orderService->getOrderByNumber($orderNumber);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }
}
