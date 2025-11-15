<?php

namespace App\Repositories\Contracts;
use App\Models\Product;

interface ProductRepositoryInterface
{
    public function all(): Collection;

    public function findById(int $id): ?Product;

    public function findBySlug(string $slug): ?Product;

    public function getActive(): Collection;

    public function decrementStock(int $productId, int $quantity): bool;
}
