<?php
declare(strict_types=1);

namespace App\Manager;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

class AuthorManager
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createAuthorByName(string $name): Author
    {
        $author = new Author();
        $author->setName($name);
        $this->entityManager->persist($author);
        $this->entityManager->flush();

        return $author;
    }

    public function getAuthorById(int $id): ?Author
    {
        /** @var AuthorRepository $authorRepository */
        $authorRepository = $this->entityManager->getRepository(Author::class);

        $author = $authorRepository->findOneBy(['id' => $id]);

        if (!$author) {
            throw new EntityNotFoundException('Invalid author id "%s"', $id);
        }

        return $author;
    }

}