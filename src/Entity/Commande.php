<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommandeRepository::class)
 */
class Commande
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=Items::class, mappedBy="commande",cascade={"persist"})
     */
    private $items;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="integer")
     */
    private $statut;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalQte;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalPrix;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Items>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /*public function addItem(Items $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setCommande($this);
        }

        return $this;
    }*/
    public function addItem(Items $item): self
    {
        foreach ($this->getItems() as $existingItem) {
            // The item already exists, update the quantity
            if ($existingItem->equals($item)) {
                $existingItem->setQuantite(
                    $existingItem->getQuantite() + $item->getQuantite()
                );

                return $this;
            }
        }

        $this->items[] = $item;
        //$item->setCommande($this);

        return $this;
    }

    public function removeItem(Items $item): self
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getCommande() === $this) {
                $item->setCommande(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getStatut(): ?int
    {
        return $this->statut;
    }

    public function setStatut(int $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getTotalQte(): ?int
    {
        return $this->totalQte;
    }

    public function setTotalQte(int $totalQte): self
    {
        $this->totalQte = $totalQte;

        return $this;
    }

    public function getTotalPrix(): ?int
    {
        return $this->totalPrix;
    }

    public function setTotalPrix(int $totalPrix): self
    {
        $this->totalPrix = $totalPrix;

        return $this;
    }

    //public func
}
