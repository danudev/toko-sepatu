<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Get cart items for session
     */
    public function index(Request $request): JsonResponse
    {
        $sessionId = $request->header('X-Session-ID');

        if (!$sessionId) {
            return response()->json([
                'success' => false,
                'message' => 'Session ID required'
            ], 400);
        }

        $cartItems = $this->cartService->getCartItems($sessionId);
        $total = $this->cartService->calculateTotal($sessionId);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $cartItems,
                'total' => $total
            ]
        ]);
    }

    /**
     * Add product to cart
     */
    public function store(AddToCartRequest $request): JsonResponse
    {
        $sessionId = $request->header('X-Session-ID');

        if (!$sessionId) {
            $sessionId = $this->cartService->getSessionId();
        }

        $result = $this->cartService->addToCart(
            $sessionId,
            $request->product_id,
            $request->quantity
        );

        $statusCode = $result['success'] ? 200 : 400;

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'session_id' => $sessionId
        ], $statusCode);
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $result = $this->cartService->updateCartItem($id, $request->quantity);

        $statusCode = $result['success'] ? 200 : 400;

        return response()->json($result, $statusCode);
    }

    /**
     * Remove item from cart
     */
    public function destroy(int $id): JsonResponse
    {
        $result = $this->cartService->removeFromCart($id);

        $statusCode = $result['success'] ? 200 : 404;

        return response()->json($result, $statusCode);
    }
}
