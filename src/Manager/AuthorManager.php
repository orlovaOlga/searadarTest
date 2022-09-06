<?php

namespace App\Manager;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;

class AuthorManager
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getOrCreateAuthorByName(string $name): Author
    {
        /** @var AuthorRepository $authorRepository */
        $authorRepository = $this->entityManager->getRepository(Author::class);

        $author = $authorRepository->findOneBy(['name' => $name]);

        if(!$author) {
            $author = new Author();
            $author->setName($name);
            $this->entityManager->persist($author);
            $this->entityManager->flush();
        }

        return $author;
    }

}