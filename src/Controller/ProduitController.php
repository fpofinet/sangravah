<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Items;
use App\Entity\Produit;
use App\Entity\Commande;
use App\Form\ProduitType;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Service\CartService;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\TextUI\Command;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProduitController extends AbstractController
{
    /**
     * @Route("/produit", name="app_produit")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $produits=$doctrine->getRepository(Produit::class)->findAll();
        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
        ]);
    }
    
    /**
     * @Route("/produit/new",name="new_produit")
     * @Route("/produit/{id}/update",name="update_produit")
     */
    public function addProduit(Produit $produit=null,Request $request,ManagerRegistry $doctrine):Response
    {
        if(!$produit){
            $produit= new Produit();
        }

        $form=$this->createForm(ProduitType::class,$produit);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $images = $form->get('images')->getData();
            //dd($images);
            // On boucle sur les images
            foreach($images as $image){
                // On génère un nouveau nom de fichier
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();

                // On copie le fichier dans le dossier uploads
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );

                // On stocke l'image dans la base de données (son nom)
                $img = new Image;
                $img->setNom($fichier);
                $img->setChemin($produit->getCategorie()->getLibelle());
                
                $produit->addImage($img);
            }

            if($produit->getId()==null){
                $produit->setCreatedAt(new \DateTimeImmutable);
            }

            $doctrine->getManager()->persist($produit);
            $doctrine->getmanager()->flush();
            return $this->redirectToRoute("app_produit");
        }

        return $this->renderForm("produit/form.html.twig",[
            'form'=>$form,
            'produit'=>$produit,
            'editState' => $produit->getId() !==null
        ]);
    }
    /**
     * @Route("/produit/{id}",name="details")
     */
    public function details(Produit $produit,Request $request,CartService $cartService):Response
    {
        if(!$produit){
            return $this->redirectToRoute("app_produit");
        }
        $form=$this->createForm(CommandeType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
           
            $item = new Items();
            $item->setProduit($produit);
            $item->setQuantite($form->get("quantite")->getData());
           // dd("break");
            $cartService->addItem($item);
            return $this->redirectToRoute("app_produit");
        }
        return $this->renderForm("produit/details.html.twig",[
            'form'=>$form,
            'produit'=>$produit
        ]);
    }
     /**
     * @Route("/produit/{id}/delete",name="delete_produit")
     */
    public function deteleProduit(Produit $produit,ManagerRegistry $doctrine):Response
    {
        if($produit){
            $doctrine->getManager()->remove($produit);
            $doctrine->getManager()->flush();

            return $this->redirectToRoute("app_produit");
        }
        return $this->redirectToRoute("app_produit");
    }
    /**
     * @Route("/supprime/image/{id}", name="annonces_delete_image", methods={"DELETE"})
     */
    public function deleteImage(Image $image, Request $request,ManagerRegistry $doctrine){
        $data = json_decode($request->getContent(), true);

        // On vérifie si le token est valide
        if($this->isCsrfTokenValid('delete'.$image->getId(), $data['_token'])){
            // On récupère le nom de l'image
            $nom = $image->getNom();
            // On supprime le fichier
            unlink($this->getParameter('images_directory').'/'.$nom);

            // On supprime l'entrée de la base
            $doctrine->getManager()->remove($image);
            $doctrine->getManager()->flush();

            // On répond en json
            return new JsonResponse(['success' => 1]);
        }else{
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }
    /**
     * @Route("/cart/produit",name="cart")
     */
    public function addToCart(CartService $cartService)
    {
        $commande=new Commande();
        $commande=$cartService->getCart();
        
        return $this->render('produit/panier.html.twig', [
            'commande' => $commande,
        ]);
    }
    /**
     *@Route("/cart/remove/",name="crm")
     */
    public function removeFromCart(Items $item=null,CartService $cartService){
        $cartService->removeItem($item);

        return $this->redirectToRoute("cart");

    }

    /**
     * @Route("/cart/save",name="saveCart")
     */
    public function saveCart(ManagerRegistry $doctrine,CartService $cartService):Response
    {
        $commande = new Commande();
        $commande->setCreatedAt(new  \DateTimeImmutable);
        $doctrine->getManager()->persist($commande);
        $doctrine->getManager()->flush();

        $repo= new CommandeRepository($doctrine);
        $shopList=$cartService->getCart();
        
        foreach($shopList->getItems() as $pds){
            $item= new Items();
            $commande = $repo->findLast();
            $item->setCommande($commande);
            $item->setProduit($doctrine->getRepository(Produit::class)->findOneBy(["id"=>$pds->getProduit()->getId()]));
            $item->setQuantite($pds->getQuantite());
            $doctrine->getManager()->persist($item);
            $doctrine->getManager()->flush();
        }
        return $this->redirectToRoute("cart");
    }
}
