<?php

namespace App\Repositories\Contracts;

use App\Models\Order;

interface OrderRepositoryInterface
{
    public function create(array $data): Order;

    public function findByOrderNumber(string $orderNumber): ?Order;

    public function updatePaymentStatus(int $orderId, string $status, ?string $transactionId = null): bool;
}
