<?php

namespace App\Services;

use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Product;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function getAllProducts(): Collection
    {
        return $this->productRepository->getActive();
    }

    public function getProductBySlug(string $slug): ?Product
    {
        return $this->productRepository->findBySlug($slug);
    }

    public function checkStock(int $productId, int $quantity): bool
    {
        $product = $this->productRepository->findById($productId);

        if (!$product) {
            return false;
        }

        return $product->stock >= $quantity;
    }
}
