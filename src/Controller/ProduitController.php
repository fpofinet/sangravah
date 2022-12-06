<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Produit;
use App\Form\ProduitType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @Route("/produit/{id}/delete",name="delete_produit")
     */
    public function deteleCategorie(Produit $produit,ManagerRegistry $doctrine):Response
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
}
