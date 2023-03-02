<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategorieController extends AbstractController
{
    /**
     * @Route("/admin/categorie", name="app_categorie")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $categories=$doctrine->getRepository(Categorie::class)->findAll();
        return $this->render('categorie/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/categorie/new",name="new_categorie")
     * @Route("/categorie/{id}/update",name="update_categorie")
     */
    public function addCategorie(Categorie $categorie=null,Request $request,ManagerRegistry $doctrine):Response
    {
        if(!$categorie){
            $categorie= new Categorie();
        }
        
        $form=$this->createForm(CategorieType::class,$categorie);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            if($categorie->getId()==null){
                $categorie->setCreatedAt(new \DateTimeImmutable);;
            }

            $doctrine->getManager()->persist($categorie);
            $doctrine->getManager()->flush();

            return $this->redirectToRoute("app_categorie");
        }
        return $this->renderForm('categorie/form.html.twig',[
            'form'=>$form,
            'editState' => $categorie->getId() !==null
        ]);
    }
    /**
     * @Route("/categorie/{id}/delete",name="delete_categorie")
     */
    public function deteleCategorie(Categorie $categorie,ManagerRegistry $doctrine):Response
    {
        if($categorie){
            $doctrine->getManager()->remove($categorie);
            $doctrine->getManager()->flush();

            return $this->redirectToRoute("app_categorie");
        }
        return $this->redirectToRoute("app_categorie");
    }
}
