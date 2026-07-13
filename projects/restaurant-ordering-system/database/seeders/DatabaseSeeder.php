<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@restaurant.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Customer',
            'email' => 'customer@restaurant.test',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        $menu = [
            'Appetizers' => [
                ['name' => 'Spring Rolls', 'description' => 'Crispy vegetable spring rolls with sweet chili sauce.', 'price' => 4.50],
                ['name' => 'Garlic Bread', 'description' => 'Toasted baguette with garlic butter and herbs.', 'price' => 3.00],
                ['name' => 'Hummus Plate', 'description' => 'Creamy hummus with warm pita bread.', 'price' => 5.00],
            ],
            'Main Courses' => [
                ['name' => 'Grilled Chicken', 'description' => 'Marinated chicken breast with rice and vegetables.', 'price' => 12.50],
                ['name' => 'Beef Burger', 'description' => 'Angus beef patty, cheddar, lettuce, tomato, fries.', 'price' => 10.00],
                ['name' => 'Margherita Pizza', 'description' => 'Tomato, mozzarella, and fresh basil.', 'price' => 9.50],
                ['name' => 'Pasta Alfredo', 'description' => 'Fettuccine in a creamy parmesan sauce.', 'price' => 11.00],
            ],
            'Drinks' => [
                ['name' => 'Fresh Orange Juice', 'description' => 'Squeezed to order.', 'price' => 3.50],
                ['name' => 'Iced Tea', 'description' => 'House-brewed with lemon.', 'price' => 2.50],
                ['name' => 'Mineral Water', 'description' => 'Still or sparkling.', 'price' => 1.50],
            ],
            'Desserts' => [
                ['name' => 'Chocolate Cake', 'description' => 'Rich chocolate layer cake.', 'price' => 5.50],
                ['name' => 'Cheesecake', 'description' => 'Classic New York style.', 'price' => 6.00],
            ],
        ];

        foreach ($menu as $categoryName => $items) {
            $category = Category::create(['name' => $categoryName]);

            foreach ($items as $item) {
                $category->menuItems()->create($item);
            }
        }
    }
}
