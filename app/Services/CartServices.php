<?php

namespace App\Services;

use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CartService
{
    public function __construct(
        protected CartRepositoryInterface $cartRepository,
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function getSessionId(string $providedSessionId = null): string
    {
        if ($providedSessionId) {
            return $providedSessionId;
        }

        // Generate new session ID
        return Str::uuid()->toString();
    }

    public function getCartItems(string $sessionId): Collection
    {
        return $this->cartRepository->getBySession($sessionId);
    }

    public function addToCart(string $sessionId, int $productId, int $quantity): array
    {
        // Check if product exists and has enough stock
        $product = $this->productRepository->findById($productId);

        if (!$product) {
            return [
                'success' => false,
                'message' => 'Product not found'
            ];
        }

        if ($product->stock < $quantity) {
            return [
                'success' => false,
                'message' => 'Insufficient stock'
            ];
        }

        // Check if item already in cart
        $existingCart = $this->cartRepository->findBySessionAndProduct($sessionId, $productId);

        if ($existingCart) {
            // Update quantity
            $newQuantity = $existingCart->quantity + $quantity;

            if ($product->stock < $newQuantity) {
                return [
                    'success' => false,
                    'message' => 'Insufficient stock for requested quantity'
                ];
            }

            $this->cartRepository->update($existingCart->id, ['quantity' => $newQuantity]);
        } else {
            // Create new cart item
            $this->cartRepository->create([
                'session_id' => $sessionId,
                'product_id' => $productId,
                'quantity' => $quantity
            ]);
        }

        return [
            'success' => true,
            'message' => 'Product added to cart'
        ];
    }

    public function updateCartItem(int $cartId, int $quantity): array
    {
        $cart = $this->cartRepository->getBySession('')->find($cartId);

        if (!$cart) {
            return [
                'success' => false,
                'message' => 'Cart item not found'
            ];
        }

        $product = $cart->product;

        if ($product->stock < $quantity) {
            return [
                'success' => false,
                'message' => 'Insufficient stock'
            ];
        }

        $this->cartRepository->update($cartId, ['quantity' => $quantity]);

        return [
            'success' => true,
            'message' => 'Cart updated'
        ];
    }

    public function removeFromCart(int $cartId): array
    {
        $deleted = $this->cartRepository->delete($cartId);

        return [
            'success' => $deleted,
            'message' => $deleted ? 'Item removed from cart' : 'Item not found'
        ];
    }

    public function calculateTotal(string $sessionId): float
    {
        $cartItems = $this->getCartItems($sessionId);

        return $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });
    }

    public function clearCart(string $sessionId): void
    {
        $this->cartRepository->clearSession($sessionId);
    }
}
