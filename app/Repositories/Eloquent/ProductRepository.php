<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductRepositoryInterface
{
     public function __construct(
        protected Product $model
    ) {}

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function findById(int $id): ?Product
    {
        return $this->model->find($id);
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function getActive(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function decrementStock(int $productId, int $quantity): bool
    {
        return DB::transaction(function () use ($productId, $quantity) {
            $product = $this->model
                ->where('id', $productId)
                ->lockForUpdate() // Pessimistic lock
                ->first();

            if (!$product || $product->stock < $quantity) {
                return false;
            }

            $product->decrement('stock', $quantity);
            return true;
        });
    }
}
