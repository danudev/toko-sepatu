<?php

namespace App\Repositories\Contracts;

use App\Models\Cart;
use Illuminate\Database\Eloquent\Collection;

interface CartRepositoryInterface
{
    public function getBySession(string $sessionId): Collection;

    public function findBySessionAndProduct(string $sessionId, int $productId): ?Cart;

    public function create(array $data): Cart;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function clearSession(string $sessionId): bool;
}
