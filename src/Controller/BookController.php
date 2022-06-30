<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use App\Repository\AuthorRepository;
use App\Service\VersioningService;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security;
use Nelmio\ApiDocBundle\Annotation\Model;


class BookController extends AbstractController
{
    /**
     * 
     * 
     * @Route("/api/books", name="book", methods="GET")
     */
    public function getAllBooks(BookRepository $bookRepository, TagAwareCacheInterface $cache, Request $request, SerializerInterface $serializer): JsonResponse
    {
        
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getAllBooks-" . $page . "-" . $limit;

        $jsonBookList = $cache->get($idCache, function(ItemInterface $item) use ($bookRepository, $page, $limit, $serializer){
        
        $context = SerializationContext::create()->setGroups(["getBooks"]);
        echo("L'ELEMENT N'EST PAS ENCORE EN CACHE !\n");
        $item->tag("booksCache");
        $bookList = $bookRepository->findAllWithPagination($page, $limit);
        return $serializer->serialize($bookList, 'json', $context);        
        });

        return new JsonResponse($jsonBookList, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/books/{id}", name="detailBook", methods="GET")
     */
    public function getDetailsBooks(Book $book, SerializerInterface $serializer, VersioningService $versioningService)
    {
        $version = $versioningService->getVersion();
        $context = SerializationContext::create()->setGroups(["getBooks"]);
        $context->setVersion($version);    
        $jsonBook = $serializer->serialize($book, 'json', $context);
        return new JsonResponse($jsonBook, Response::HTTP_OK, [], true);
    }
    /* LA MEME FONCTION SANS SENSIO EXTRA BUNDLE // PARAM CONVERTER
    public function getDetailsBooks(int $id, BookRepository $bookRepository, SerializerInterface $serializer)
    {
        $book = $bookRepository->find($id);
        if($book){
            $jsonBook = $serializer->serialize($book, 'json');
            return new JsonResponse($jsonBook, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }*/

    /**
     * @Route("/api/books/{id}", name="deleteBook", methods="DELETE")
     * @IsGranted("ROLE_ADMIN", message="Vous n\'avez pas les droits requis")
     */
    public function deleteBook(Book $book, TagAwareCacheInterface $cache, EntityManagerInterface $em) : JsonResponse
    {
            $cache->invalidateTags(["booksCache"]);
            $em->remove($book);
            $em->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
     /**
     * @Route("/api/books", name="createBook", methods="POST")
     * @IsGranted("ROLE_ADMIN", message="Vous n\'avez pas les droits requis")
     */
    public function createBook(SerializerInterface $serializer, Request $request, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, AuthorRepository $authorRepository, ValidatorInterface $validator) : JsonResponse
    {
            $context = SerializationContext::create()->setGroups(["getBooks"]);
            $book = $serializer->deserialize($request->getContent(), Book::class, 'json');
            
            //On vérifie les erreurs
            $errors = $validator->validate($book);
            if($errors->count() > 0){
                return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            }
            
            $em->persist($book);
            $em->flush();

            //Récupération de l'ensemble des données envoyé sous forme d'array
            $content = $request->toArray();
            //Récupération idAuthor, si pas définit, on met par défaut -1
            $idAuthor = $content['idAuthor'] ?? -1;
            //On cherche l'auteur et on l'assigne au livre
            $book->setAuthor($authorRepository->find($idAuthor));

            $jsonBook = $serializer->serialize($book, 'json', $context);

            //PERMET DE RETOURNER LE NOUVEL URL AVEC L'ID DANS POSTMAN
            $location = $urlGenerator->generate('detailBook', ['id' => $book->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

            return new JsonResponse($jsonBook, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    /**
     * @Route("/api/books/{id}", name="updateBook", methods="PUT")
     */
    public function updateBook(SerializerInterface $serializer, Request $request, EntityManagerInterface $em, Book $currentBook, AuthorRepository $authorRepository, ValidatorInterface $validator, TagAwareCacheInterface $cache) : JsonResponse
    {
            $newBook = $serializer->deserialize($request->getContent(), Book::class, 'json');

            $currentBook->setTitle($newBook->getTitle());
            $currentBook->setCoverText($newBook->getCoverText());

            //On vérifie les erreurs
            $errors = $validator->validate($currentBook);
            if($errors->count() > 0){
                return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            }
            
            $content = $request->toArray();
            $idAuthor = $content['idAuthor'] ?? -1;

            $currentBook->setAuthor($authorRepository->find($idAuthor));

            $em->persist($currentBook);
            $em->flush();
            
            return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
