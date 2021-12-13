<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\User::class, function (Faker $faker) {
    return [
        'username' => $faker->username,
        'lastname' => $faker->lastName,
        'firstname' => $faker->lastName,
        'address' => $faker->address,
        'email' => $faker->unique()->safeEmail,
        'phone' => $faker->phoneNumber,
        'type' => $faker->randomElements(['1', '2', '3'])[0],
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'remember_token' => str_random(10),
        'photo' => 'https://loremflickr.com/g/320/240/paris,girl/all',
    ];
});

$factory->define(App\Vendor::class, function (Faker $faker) {
	$name = $faker->name;
	$slug = str_slug($name, '-');
   	return [
        'user_id' => App\User::all()->random()->id,
        'store_name' => $name,
        'store_slug' => $slug,
        'store_unique_code' => $faker->unique()->randomNumber($nbDigits = 5),
        'status' =>'Active',
        'store_address' => $faker->address,
        'store_email' => $faker->unique()->safeEmail,
        'store_phone' => $faker->phoneNumber,
        'store_logo' => 'https://loremflickr.com/g/320/240/paris,girl/all',
      
    ];
});

$factory->define(App\Book_Category::class, function (Faker $faker) {
	$name = $faker->word;
	$slug = str_slug($name, '-');
    return [
        'name' => $name,
        'slug' => $slug,
        'store_id' => App\Vendor::all()->random()->id,
    ];
});

$factory->define(App\Book::class, function (Faker $faker) {
	$name = $faker->sentence($nbWords = 4, $variableNbWords = true);
	$slug = str_slug($name, '-');
    return [
    	'store_id' => App\Vendor::all()->random()->id,
        'name' => $name,
        'slug' => $slug,
        'store_id' => App\Vendor::all()->random()->id,
        'book_code' => $faker->unique()->randomNumber($nbDigits = 5),
        'image' => 'https://loremflickr.com/g/320/240/paris,girl/all',
        'price' => $faker->numberBetween($min = 1000, $max = 9000),
        'is_available' => $faker->randomElements(['0', '1'])[0],
    ];
});