<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Produit;
use App\Entity\Commande;
use App\Entity\Categorie;
use App\Entity\Items;
use App\Repository\ItemsRepository;
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
    public function index(ManagerRegistry $doctrine,ItemsRepository $itemsRepository): Response
    {
        $commandes=$doctrine->getRepository(Commande::class)->findAll();
        
        $lv= $doctrine->getRepository(Commande::class)->findBy(["statut"=> 2]);
        $produits=$doctrine->getRepository(Produit::class)->findAll();
        $categories=$doctrine->getRepository(Categorie::class)->findAll();
        $items= $itemsRepository->CountProduits();
       
        $rupt=0;
        $somme=0;
        foreach($produits as $p){
            if($p->getQuantite()<50){
                $rupt =$rupt+1;
            }
        }
        foreach($commandes as $c){
            $somme=$somme + $c->getTotalPrix();
        }
        return $this->render('admin/index.html.twig', [
            'commandes'=>$commandes,
            'produits'=>$produits,
            'rupture'=>$rupt,
            'categorie'=>$categories,
            'livre'=>$lv,
            'somme'=>$somme,
            'vendu'=>$items
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

    /**
     * @Route("/admin/produit-rupture", name="produit_rupture")
     */
    public function produitRupture(ManagerRegistry $doctrine): Response
    {
        $produits=$doctrine->getRepository(Produit::class)->findAll();
        $prods =array();
        foreach($produits as $p){
            if($p->getQuantite()<50){
               $prods []=$p;
            }
        }
        return $this->render('produit/rupture.html.twig', [
            'produits' => $prods,
        ]);
    }

    
    /**
     * @Route("/admin/produit-vendu", name="produit_vendu")
     */
    public function produitVendu(ManagerRegistry $doctrine,ItemsRepository $itemsRepository): Response
    {
        $items=$itemsRepository->findByDistinctByProduit();
        $prods =array();
        foreach($items as $i){
            $prods[]=$doctrine->getRepository(Produit::class)->findOneBy(["id"=>$i["produit_id"]]);
        }
        //dd($prods);
        return $this->render('produit/vendu.html.twig', [
            'produits' => $prods,
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
