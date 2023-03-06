<?php

namespace App\Controller;

use App\Entity\Commande;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    /**
     * @Route("/admin/commande", name="app_commande")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $commandes= $doctrine->getRepository(Commande::class)->findAll();
        return $this->render('commande/index.html.twig', [
            'commandes' => $commandes
        ]);
    }


    /**
     * @Route("/admin/commande-livre", name="commande_livre")
     */
    public function returnLivre(ManagerRegistry $doctrine):Response
    {
        $commandes= $doctrine->getRepository(Commande::class)->findBy(["statut"=> 2]);
        return $this->render('commande/livre.html.twig', [
            'commandes' => $commandes
        ]);
    }

    /**
     * @Route("/admin/commande/{id}", name="details_commande")
     */
    public function details(Commande $commande){
        if(!$commande){
            return $this->redirectToRoute("app_commande");
        }
        return $this->render('commande/details.html.twig', [
            'commande' => $commande
        ]);
    }

    /**
     * @Route("/admin/commande/{id}/cloturer", name="cloturer")
     */
    public function cloturer(Commande $commande,ManagerRegistry $doctrine){
        if($commande!=null){
            $commande->setStatut(2);
            $doctrine->getManager()->persist($commande);
            $doctrine->getManager()->flush();
            return $this->redirectToRoute("app_commande");
        } else{
            return $this->redirectToRoute("app_commande");
        }
    }

    /**
     * @Route("/admin/commande/{id}/annuler", name="annuler")
     */
    public function annuler(Commande $commande,ManagerRegistry $doctrine){
        if($commande!=null){
            $commande->setStatut(0);
            $doctrine->getManager()->persist($commande);
            $doctrine->getManager()->flush();
            return $this->redirectToRoute("app_commande");
        } else{
            return $this->redirectToRoute("app_commande");
        }
    }

    /**
     * @Route("/admin/commande/encours", name="commande_live")
     */
    public function returnLive(ManagerRegistry $doctrine):Response
    {
        $liveCommandes= $doctrine->getRepository(Commande::class)->findBy(["statut"=> 1]);
        return $this->render('admin/encours.html.twig', [
            'commandes' => $liveCommandes
        ]);
    }

    



}
