<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\User;
use App\Entity\Author;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        //Création d'un user normal
        $user = new User;
        $user->setEmail("user@bookapi.fr");
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
        $manager->persist($user);

        //Création d'un user admin
        $userAdmin = new User;
        $userAdmin->setEmail("admin@bookapi.fr");
        $userAdmin->setRoles(["ROLE_ADMIN"]);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
        $manager->persist($userAdmin);

        $listAuthor = [];
        for ($i=0; $i < 10; $i++) { 
            //Création de l'auteur
            $author = new Author;
            $author->setFirstName('Prénom '.$i);
            $author->setLastName('Nom '.$i);
            $manager->persist($author);
            //On sauvegarde l'auteur créé dans le tableau
            $listAuthor[] = $author;
        }
        
        for ($i=0; $i < 20; $i++) { 
            $book = new Book();
            $book->setTitle("Titre ". $i);
            $book->setCoverText("Quatrième de couverture numéro : ". $i);
            $book->setAuthor($listAuthor[array_rand($listAuthor)]);
            $book->setComment("Commentaire du bibliothécaire " . $i);
            $manager->persist($book);
        }

        $manager->flush();
    }
}