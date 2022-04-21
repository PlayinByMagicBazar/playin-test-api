<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionInit extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initialise la base de données de test';
    }

    public function up(Schema $schema): void
    {

        /* Cette table représente les commandes */
        $this->addSql('create table t_panier
        (
            id_panier int primary key auto_increment,
            valide tinyint(1) not null default 0
        )
        ');

        /* Cette table représente les stocks, c'est-à-dire les achats faits à des fournisseurs */
        $this->addSql('create table stock
        (
            id_stock int primary key auto_increment,
            valide tinyint(1) not null default 0
        )
        ');

        /* Cette table représente les dépôts des clients, pour lesquels l'entreprise se charge de la vente
        en contrepartie d'une commission */
        $this->addSql('create table t_depot
        (
            id_depot int primary key auto_increment,
            valide tinyint(1) not null default 0
        )
        ');

        /* Cette table représente les produits */
        $this->addSql('create table t_produit
        (
            id_produit int primary key auto_increment,
            nom varchar(150) not null,
            prix_vente decimal(8,3) not null default 0.
        )
        ');


        $this->addSql('create table t_assoc
        (
            id_assoc int primary key auto_increment,
            /*  
            "O" = Lié à un stock, 
            "N" = Lié à un dépôt, 
            "V" = Association virtuelle (lié à rien)
             */
            vendeur enum("O", "N", "V"), 
            /* 
             ID de la table t_id_panier_id_produit
             */
            id_detail int default null,
            /* 
             ID de la table stock_to_product si vendeur = O, de la table t_id_depot_id_produit si vendeur = N, et null si vendeur = V
             */
            id_detail_stock int default null,
            quantite int not null default 1
        )
        ');

        /* Cette table représente l'association n..n entre les commandes et les produits et les propriétés
        associées à cette relation */
        $this->addSql('create table t_id_panier_id_produit
        (
            id_detail int primary key auto_increment,
            id_panier int not null,
            id_produit int not null,
            quantite int not null default 1,
            prix_vente decimal(8,3) not null default 0.,
            foreign key (id_panier) references t_panier(id_panier),
            foreign key (id_produit) references t_produit(id_produit) 
        )
        ');

        /* Cette table représente l'association n..n entre les stocks et les produits et les propriétés
        associées à cette relation */
        $this->addSql('create table stock_to_product
        (
            id_detail int primary key auto_increment,
            id_stock int not null,
            id_produit int not null,
            quantite int not null default 1,
            quantite_vendue int not null default 0,
            prix_achat decimal(8,3) not null default 0.,
            foreign key (id_stock) references stock(id_stock),
            foreign key (id_produit) references t_produit(id_produit) 
        )
        ');

        /* Cette table représente l'association n..n entre les dépôts et les produits et les propriétés
        associées à cette relation */
        $this->addSql('create table t_id_depot_id_produit
        (
            id_detail int primary key auto_increment,
            id_depot int not null,
            id_produit int not null,
            quantite int not null default 1,
            quantite_vendue int not null default 0,
            pourcentage int not null default 70, /* Le pourcentage reversé au client */
            foreign key (id_depot) references t_depot(id_depot),
            foreign key (id_produit) references t_produit(id_produit) 
        )
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE t_assoc');
        $this->addSql('DROP TABLE t_produit');

        $this->addSql('DROP TABLE t_panier');
        $this->addSql('DROP TABLE t_id_panier_id_produit');

        $this->addSql('DROP TABLE t_depot');
        $this->addSql('DROP TABLE t_id_depot_id_produit');

        $this->addSql('DROP TABLE stock');
        $this->addSql('DROP TABLE stock_to_product');
    }
}
