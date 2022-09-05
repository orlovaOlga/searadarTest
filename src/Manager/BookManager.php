<?php
declare(strict_types = 1);

namespace App\Manager;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;

class BookManager
{
    const DEFAULT_PAGINATION_LIMIT = 20;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function saveBook(string $title, string $author): ?int
    {
        $book = new Book();
        $book->setTitle($title);
        $book->setAuthor($author);
        $this->entityManager->persist($book);
        $this->entityManager->flush();

        return $book->getId();
    }

    public function getBookById(int $bookId): Book|bool
    {
        /** @var BookRepository $bookRepository */
        $bookRepository = $this->entityManager->getRepository(Book::class);
        /** @var Book $book */
        $book = $bookRepository->find($bookId);

        return $book ?? false;
    }

    public function deleteBookById(int $bookId): bool
    {
        /** @var BookRepository $bookRepository */
        $bookRepository = $this->entityManager->getRepository(Book::class);

        /** @var Book $book */
        $book = $bookRepository->find($bookId);

        return $book ? $this->deleteBook($book) : false;
    }

    public function deleteBook(Book $book): bool
    {
        $this->entityManager->remove($book);
        $this->entityManager->flush();

        return true;
    }

    public function updateBook(int $bookId, ?string $title, ?string $author): ?Book
    {
        /** @var $bookRepository $bookRepository */
        $bookRepository = $this->entityManager->getRepository(Book::class);
        /** @var Book $book */
        $book = $bookRepository->find($bookId);

        if ($book === null) {
            return null;
        }

        if ($title) {
            $book->setTitle($title);
        }

        if ($author) {
            $book->setAuthor($author);
        }

        $this->entityManager->flush();

        return $book;
    }

    /**
     * @return Book[]
     */
    public function searchBooks(?string $title, ?string $author, int $page, int $perPage): array
    {
        /** @var BookRepository $bookRepository */
        $bookRepository = $this->entityManager->getRepository(Book::class);

        return $bookRepository->searchBooks($title, $author, $page, $perPage);
    }
}