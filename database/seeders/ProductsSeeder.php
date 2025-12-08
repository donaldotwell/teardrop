<?php

namespace Database\Seeders;

use App\Models\Listing;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productCategories = [
            [
                'name' => 'Drugs & Chemicals',
                'products' => [
                    ['name' => 'Cannabis', 'listings' => 13],
                    ['name' => 'Stimulants', 'listings' => 5],
                    ['name' => 'Ecstasy', 'listings' => 7],
                    ['name' => 'Opioids', 'listings' => 7],
                    ['name' => 'Prescription', 'listings' => 5],
                    ['name' => 'Steroids', 'listings' => 5],
                ]
            ],
            [
                'name' => 'Fraud',
                'products' => [
                    ['name' => 'Credit Cards', 'listings' => 5],
                    ['name' => 'Bank Accounts', 'listings' => 6],
                    ['name' => 'PayPal Accounts', 'listings' => 9],
                    ['name' => 'Gift Cards', 'listings' => 11],
                ]
            ],
            [
                'name' => 'Guides & Tutorials',
                'products' => [
                    ['name' => 'Carding', 'listings' => 12],
                    ['name' => 'Hacking', 'listings' => 7],
                    ['name' => 'Social Engineering', 'listings' => 89],
                    ['name' => 'Fraud', 'listings' => 10],
                    ['name' => 'Security', 'listings' => 11],
                    ['name' => 'Anonymity', 'listings' => 8],
                ]
            ],
            [
                'name' => 'Services',
                'products' => [
                    ['name' => 'Hacking', 'listings' => 12],
                    ['name' => 'Carding', 'listings' => 7],
                    ['name' => 'Social Engineering', 'listings' => 12],
                    ['name' => 'Fraud', 'listings' => 9],
                    ['name' => 'Security', 'listings' => 3],
                    ['name' => 'Anonymity', 'listings' => 2],
                ]
            ],
            [
                'name' => 'Weapons',
                'products' => [
                    ['name' => 'Pistols', 'listings' => 2],
                    ['name' => 'Rifles', 'listings' => 23],
                    ['name' => 'Shotguns', 'listings' => 6],
                    ['name' => 'Ammunition', 'listings' => 9],
                    ['name' => 'Explosives', 'listings' => 1],
                    ['name' => 'Accessories', 'listings' => 4],
                ],
            ],
            [
                'name' => 'Software & Malware',
                'products' => [
                    ['name' => 'Ransomware', 'listings' => 7],
                    ['name' => 'Botnets', 'listings' => 2],
                    ['name' => 'Exploits', 'listings' => 2],
                    ['name' => 'Rootkits', 'listings' => 3],
                    ['name' => 'Keyloggers', 'listings' => 5],
                    ['name' => 'Spyware', 'listings' => 8],
                ]
            ],
            [
                'name' => 'Jewelry & Gold',
                'products' => [
                    ['name' => 'Rings', 'listings' => 9],
                    ['name' => 'Necklaces', 'listings' => 2],
                    ['name' => 'Bracelets', 'listings' => 6],
                    ['name' => 'Earrings', 'listings' => 7],
                    ['name' => 'Watches', 'listings' => 8],
                    ['name' => 'Gold Bars', 'listings' => 3],
                ]
            ]
        ];


        foreach ($productCategories as $productCategory) {
            $category = \App\Models\ProductCategory::create([
                'name' => $productCategory['name'],
            ]);

            foreach ($productCategory['products'] as $product) {
                $productItem = \App\Models\Product::create([
                    'product_category_id' => $category->id,
                    'name' => $product['name'],
                ]);

                try {
                    Listing::factory()->create([
                        'product_id' => $productItem->id,
                        'quantity' => $product['listings']
                    ]);
                } catch (\Exception $e) {
                    // echo $e->getMessage();
                }

            }
        }
    }
}
