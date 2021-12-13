<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        // $this->call(UsersTableSeeder::class);
        factory(App\User::class, 20)->create();
        factory(App\Vendor::class, 60)->create();
        factory(App\Book_Category::class, 20)->create();
        factory(App\Book::class, 100)->create();
    }
}
