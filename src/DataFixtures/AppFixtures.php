<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Phone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Liior\Faker\Prices;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $faker->addProvider(new Prices($faker));

        /**
         * ============ PHONE ============
         */
        /* Default values for brands, capacity, colours and size */
        $brands = ['Samsung', 'Apple', 'Xiaomi', 'Google', 'Alcatel', 'Acer', 'Asus', 'Blackview'];
        $capacity = [64, 128, 256, 512, 1000, 2000];
        $colors = ['black', 'white', 'red', 'darkgrey', 'green', 'blue'];
        $size = [3.5, 6.5];

        for ($i = 0; $i < 40; $i++) {
            $brand_random = $brands[array_rand($brands)];
            $model_random = rand(1, 25);

            switch ($brand_random) {
                case 'Samsung':
                    $phone_name = $brand_random . ' Galaxy S' . $model_random;
                    break;
                case 'Apple':
                    $phone_name = $brand_random . ' iPhone ' . $model_random;
                    break;
                case 'Google':
                    $phone_name = $brand_random . ' Pixel ' . $model_random;
                    break;
                case 'Xiaomi':
                    $phone_name = $brand_random . ' Mi ' . $model_random;
                    break;
                case 'Alcatel':
                    $phone_name = $brand_random . ' OneTouch ' . $model_random;
                    break;
                default:
                    $phone_name = $brand_random . ' ' . $model_random;
                    break;
            }


            $phone = new Phone();
            $phone->setName($phone_name);
            $phone->setImage('https://placehold.co/420x600');
            $phone->setBrand($brand_random);
            $phone->setCapacity($capacity[array_rand($capacity)]);
            $phone->setModel($model_random);
            $phone->setColor(array_rand($colors));
            $phone->setScreenSize(rand($size[0] * 10, $size[1] * 10) / 10);
            $phone->setDescription($faker->paragraph());
            $phone->setPrice($faker->price(20000, 200000));

            $manager->persist($phone);
        }

        /**
         * ============ CUSTOMER ============
         */

        for ($i = 0; $i < 20; $i++) {
            $customer = new Customer();
            $customer->setName($faker->company());
            $customer->setEmail($faker->email());

            $manager->persist($customer);
        }

        $manager->flush();
    }
}
