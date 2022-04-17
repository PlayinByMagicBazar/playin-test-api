<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\AssociationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: AssociationRepository::class)]
#[ORM\Table(name: 't_assoc')]
#[ApiResource()]
class Association
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_assoc', type: 'integer')]
    private int $id;

    #[ORM\Column(name: 'id_detail', type: 'integer')]
    private $idDetail;

    #[ORM\Column(name:"vendeur", type:"string", columnDefinition:"enum('O', 'N', 'V')")]
    private $seller;

  
    #[ORM\Column(name: 'quantite', type: 'integer')]
    #[Assert\Positive]
    private $quantity = 1;


    #[ORM\Column(name: 'id_detail_stock', type: 'integer')]
    private $idDetailStock;

    
    private $margins;

    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeller(): ?string
    {
        return $this->seller;
    }

    public function setSeller(string $seller): self
    {
        $this->seller = $seller;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }


    public function getIdDetailStock(): ?int
    {
        return $this->idDetailStock;
    }

    public function setIdDetailStock(int $idDetailStock): self
    {
        $this->idDetailStock = $idDetailStock;

        return $this;
    }

    public function getMargins(): ?string
    {
        return $this->margins;
    }

    public function setMargins(string $margins): self
    {
        $this->margins = $margins;

        return $this;
    }

    public function getIdDetail(): ?int
    {
        return $this->idDetail;
    }

    public function setIdDetail(int $idDetail): self
    {
        $this->idDetail = $idDetail;

        return $this;
    }

}