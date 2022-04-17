<?php

namespace App\Controller;

use App\Entity\Association;
use App\Entity\DepositEntry;
use App\Entity\Order;
use App\Entity\OrderEntry;
use App\Entity\StockEntry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class OrderValidatorController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {
        $this->em = $em;
    }

   
    public function __invoke(Order $data)
    {
        $response =  new JsonResponse();

        if($data->isValidated()){
            $response->setStatusCode(401);
            $response->setData(["data" => ["message" => "La commande a deja ete validee"]]);
            return $response;
        }

        $orderEntry =  $this->em->getRepository(OrderEntry::class)->findOneBy(['order'=> $data]);
        if($orderEntry == null){
            $response->setStatusCode(401);
            $response->setData(["data" => ["message" => "La commande n'existe pas"]]);
            return $response;
        }
        $product = $orderEntry->getProduct();
        $qtyOrder = $orderEntry->getQuantity();

        $stores = $this->recupStore($product);
        if($stores != null){
            $sumQty = 0;
            $sumSQty = 0;
            for ($i = 0; $i < count($stores); $i++) {
                $remainingQty = 0;
                $sumQty = $sumQty + $stores[$i]->getQuantity();
                $sumSQty = $sumSQty + $stores[$i]->getSoldQuantity();
                $remainingQty = $sumQty - $sumSQty;
            }

            /// la quantite commandee > la quantite dispo
            if ($remainingQty - $qtyOrder < 0) {
                $response->setStatusCode(401);
                $response->setData(["data" => ["message" => "On ne peut pas executer cette commande"]]);
                return $response;
               
            }
            
            $i = 0;
            while($qtyOrder > 0){
                $diff = $stores[$i]->getQuantity() - $stores[$i]->getSoldQuantity();
                $remaining = $diff > $qtyOrder ?  $qtyOrder : $diff;
                $qtyOrder -= $remaining;
                $stores[$i]->setSoldQuantity($stores[$i]->getSoldQuantity() + $remaining);
                $this->em->persist($stores[$i]);
                $this->createAssosiation($orderEntry,$stores[$i], $remaining);
                $i++;
            }
            $data->setValidated(true);
            $this->em->persist($data);
            $this->em->flush();
            $response->setStatusCode(200);
            $response->setData(["data" => ["message" => "La commande a ete validee"]]);
            return $response;
            
        }

        $response->setStatusCode(404);
        $response->setData(["data" => ["message" => "ce produit n'a ni de depot ni de stock"]]);
        return $response;
       


    }

    private function recupStore($product)
    {   
        $deposits = $this->em->getRepository(DepositEntry::class)->findBy(['product'=> $product], ['id'=> 'ASC'], null, null);
        $stocks = $this->em->getRepository(StockEntry::class)->findBy(['product'=> $product], ['id'=> 'ASC'], null, null);
        $stores = array_merge($deposits, $stocks);
        return $stores; 
    }
    
    private function createAssosiation($orderEntry, $store, $qty)
    {
        $assoc = new Association();
        $assoc->setIdDetail($orderEntry->getId());
        $assoc->setQuantity($qty);
        $assoc->setIdDetailStock($store->getId());
        $assoc->setSeller($this->determineSeller($store));
        $assoc->setMargins($this->determineMargin($orderEntry, $store));
        $this->em->persist($assoc);
    }


    private function determineSeller($store)
    {
        $seller = 'V';
        if($store instanceof StockEntry){
            $seller = 'O';
        }elseif($store instanceof DepositEntry){
            $seller = 'N';
        }
        return $seller;
            
    }


    private function determineMargin($orderEntry, $store)
    {
        $sellPrice = $orderEntry->getSellPrice();
        $margin = 0;
        if($store instanceof StockEntry){
            $margin  =  $sellPrice - $store->getBuyPrice();
        }elseif($store instanceof DepositEntry){
            $margin  = ($store->getReimbursementPercentage() * $sellPrice) / 100;
        }
        return $margin;
            
    }
}
