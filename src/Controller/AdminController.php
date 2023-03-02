<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Produit;
use App\Entity\Commande;
use App\Entity\Categorie;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/dashboard", name="app_admin")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $commandes=$doctrine->getRepository(Commande::class)->findAll();
        $produits=$doctrine->getRepository(Produit::class)->findAll();
        $categories=$doctrine->getRepository(Categorie::class)->findAll();
        $rupt=0;
        foreach($produits as $p){
            if($p->getQuantite()<50){
                $rupt =$rupt+1;
            }
        }
        return $this->render('admin/index.html.twig', [
            'commandes'=>$commandes,
            'produits'=>$produits,
            'rupture'=>$rupt,
            'categorie'=>$categories
        ]);
    }

    /**
     * @Route("/admin/produit", name="produit_list")
     */
    public function produits(ManagerRegistry $doctrine): Response
    {
        $produits=$doctrine->getRepository(Produit::class)->findAll();
        return $this->render('produit/tableProduit.html.twig', [
            'produits' => $produits,
        ]);
    }

   /*
    public function addUser(ManagerRegistry $doctrine,UserPasswordHasherInterface $encoder){
        
        $user = new User();
        $user->setUsername("luka");
        $user->setPassword("123456");
        $hash= $encoder->hashPassword($user,$user->getPassword());
        $user->setPassword($hash);
        $doctrine->getManager()->persist($user);
        $doctrine->getmanager()->flush();
        return $this->redirectToRoute("app_admin");
    }*/
}
