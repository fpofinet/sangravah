<?php

namespace App\Service;

use App\Entity\Items;
use App\Entity\Commande;
use Doctrine\ORM\Query\Expr\Func;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService{

    private $requestStack;
    private $commande;
    public function __construct(RequestStack $requestStack){
        $this->requestStack=$requestStack;
        $this->commande= new Commande();
    }

    public function addItem(Items $item){
        
        $session=$this->requestStack->getSession();
        if($session->get('cart')!=null){
            $this->commande=$session->get('cart');
            $this->commande->addItem($item);

        } else{
            $this->commande->addItem($item);
            $session->set('cart',$this->commande);
        }
    }

    public function removeItem(Items $item){
        $comm= new Commande();

        $session=$this->requestStack->getSession();
        $comm=$session->get('cart');
        $comm->removeItem($item);
        
        $session->set('cart',$comm);
    }

    public function getCart():?Commande{
        $session=$this->requestStack->getSession();
        $this->commande=$session->get('cart');
        return $this->commande;
    }

    public function clearCart(){
        $session=$this->requestStack->getSession();
        $session->set('cart',null);
    }

}