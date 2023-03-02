<?php

namespace App\DataFixtures;

use App\Entity\Produit;
use App\Entity\Categorie;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        for ($i = 1; $i <= 70; $i++) {
            $categorie = new Categorie();
            $categorie->setCode("CAT00 ".$i."0");
            $categorie->setLibelle("categorie".$i);
            $categorie->setType("Typecategorie".$i);
            if($i%2==0){
                $categorie->setSexe("Masculin");
            } else{
                $categorie->setSexe("feminin");
            }
            $categorie->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($categorie);
            $manager->flush();
            for($j=1; $j<= 60; $j++){
                $produit = new Produit();
                $produit->setReference("REF00".$i."".$j);
                $produit->setDesignation("Produit ".$i.$j."-".$i);
                $produit->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua');
                $produit->setPrix(mt_rand(1000, 600000));
                $produit->setQuantite(mt_rand(25, 150));
                $produit->setCreatedAt(new \DateTimeImmutable());
                $produit->setCategorie($categorie);
                $manager->persist($produit);
                $manager->flush();
            }
           
        }
        $manager->flush();
    }
}
