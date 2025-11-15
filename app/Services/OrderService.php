<?php

namespace App\Services;

use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected ProductRepositoryInterface $productRepository,
        protected CartService $cartService
    ) {}

    public function createOrder(array $customerData, string $sessionId): Order
    {
        return DB::transaction(function () use ($customerData, $sessionId) {
            // Get cart items
            $cartItems = $this->cartService->getCartItems($sessionId);

            if ($cartItems->isEmpty()) {
                throw new \Exception('Cart is empty');
            }

            // Calculate total
            $totalAmount = $this->cartService->calculateTotal($sessionId);

            // Generate order number
            $orderNumber = $this->generateOrderNumber();

            // Create order
            $order = $this->orderRepository->create([
                'order_number' => $orderNumber,
                'customer_name' => $customerData['name'],
                'customer_email' => $customerData['email'],
                'customer_phone' => $customerData['phone'],
                'total_amount' => $totalAmount,
                'payment_status' => 'pending'
            ]);

            // Create order items and decrement stock
            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product;

                // Check stock availability
                if ($product->stock < $cartItem->quantity) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }

                // Create order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $product->price * $cartItem->quantity
                ]);

                // Decrement stock
                $this->productRepository->decrementStock($product->id, $cartItem->quantity);
            }

            // Clear cart
            $this->cartService->clearCart($sessionId);

            return $order;
        });
    }

    public function getOrderByNumber(string $orderNumber): ?Order
    {
        return $this->orderRepository->findByOrderNumber($orderNumber);
    }

    public function updatePaymentStatus(int $orderId, string $status, ?string $transactionId = null): bool
    {
        return $this->orderRepository->updatePaymentStatus($orderId, $status, $transactionId);
    }

    protected function generateOrderNumber(): string
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}
