<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Nike Air Max 270',
                'slug' => 'nike-air-max-270',
                'description' => 'Sepatu running dengan cushioning maksimal dan desain modern',
                'price' => 1500000,
                'image_url' => 'https://via.placeholder.com/400x300?text=Nike+Air+Max+270',
                'stock' => 50,
                'is_active' => true,
            ],
            [
                'name' => 'Adidas Ultraboost 22',
                'slug' => 'adidas-ultraboost-22',
                'description' => 'Sepatu lari dengan teknologi boost untuk kenyamanan maksimal',
                'price' => 2000000,
                'image_url' => 'https://via.placeholder.com/400x300?text=Adidas+Ultraboost',
                'stock' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Puma RS-X',
                'slug' => 'puma-rs-x',
                'description' => 'Sepatu casual dengan style retro dan warna bold',
                'price' => 1200000,
                'image_url' => 'https://via.placeholder.com/400x300?text=Puma+RS-X',
                'stock' => 40,
                'is_active' => true,
            ],
            [
                'name' => 'Converse Chuck Taylor All Star',
                'slug' => 'converse-chuck-taylor',
                'description' => 'Sepatu klasik yang timeless dan cocok untuk berbagai outfit',
                'price' => 800000,
                'image_url' => 'https://via.placeholder.com/400x300?text=Converse+Chuck',
                'stock' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Vans Old Skool',
                'slug' => 'vans-old-skool',
                'description' => 'Sepatu skateboard iconic dengan stripe signature',
                'price' => 900000,
                'image_url' => 'https://via.placeholder.com/400x300?text=Vans+Old+Skool',
                'stock' => 45,
                'is_active' => true,
            ],
            [
                'name' => 'New Balance 574',
                'slug' => 'new-balance-574',
                'description' => 'Sepatu lifestyle dengan comfort dan style yang balance',
                'price' => 1300000,
                'image_url' => 'https://via.placeholder.com/400x300?text=New+Balance+574',
                'stock' => 35,
                'is_active' => true,
            ],
        ];

        DB::table('products')->insert($products);
    }
}
