<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Product;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $product1 = new Product();
        $product1->setTitle('Product 1');
        $product1->setPrice(10);
        $product1->setCategory('Electronics');
        $product1->setDescription('Lorem ipsum...');
        $product1->setImage('product1.jpg');
        $manager->persist($product1);

        $product2 = new Product();
        $product2->setTitle('Product 2');
        $product2->setPrice(100);
        $product2->setCategory('Clothing');
        $product2->setDescription('Lorem ipsum...');
        $product2->setImage('product2.jpg');
        $manager->persist($product2);

        $manager->flush();
    }
}
