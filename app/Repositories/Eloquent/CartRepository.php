<?php

namespace App\Repositories\Eloquent;

use App\Models\Cart;
use App\Repositories\Contracts\CartRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CartRepository implements CartRepositoryInterface
{
    public function __construct(
        protected Cart $model
    ) {}

    public function getBySession(string $sessionId): Collection
    {
        return $this->model
            ->where('session_id', $sessionId)
            ->with('product')
            ->get();
    }

    public function findBySessionAndProduct(string $sessionId, int $productId): ?Cart
    {
        return $this->model
            ->where('session_id', $sessionId)
            ->where('product_id', $productId)
            ->first();
    }

    public function create(array $data): Cart
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->model->where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return $this->model->where('id', $id)->delete();
    }

    public function clearSession(string $sessionId): bool
    {
        return $this->model->where('session_id', $sessionId)->delete();
    }
}
