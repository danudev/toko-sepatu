<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        protected Order $model
    ) {}

    public function create(array $data): Order
    {
        return $this->model->create($data);
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return $this->model
            ->where('order_number', $orderNumber)
            ->with('items.product')
            ->first();
    }

    public function updatePaymentStatus(int $orderId, string $status, ?string $transactionId = null): bool
    {
        $data = ['payment_status' => $status];

        if ($transactionId) {
            $data['midtrans_transaction_id'] = $transactionId;
        }

        return $this->model->where('id', $orderId)->update($data);
    }
}
