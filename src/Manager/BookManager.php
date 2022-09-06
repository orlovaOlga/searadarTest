<?php
declare(strict_types=1);

namespace App\Manager;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

class BookManager
{
    private EntityManagerInterface $entityManager;

    private AuthorManager $authorManager;

    public function __construct(EntityManagerInterface $entityManager, AuthorManager $authorManager)
    {
        $this->entityManager = $entityManager;
        $this->authorManager = $authorManager;
    }

    public function createBook(string $title, ?string $existedAuthors, ?string $authorNames): ?int
    {
        $book = new Book();
        $book->setTitle($title);

        if ($authorNames) {
            $names = explode(',', $authorNames);

            foreach ($names as $name) {
                $author = $this->authorManager->createAuthorByName(trim($name));
                $book->addAuthor($author);
            }
        }

        if ($existedAuthors) {
            $ids = explode(',', $existedAuthors);

            foreach ($ids as $id) {
                try {
                    $author = $this->authorManager->getAuthorById((int)$id);
                } catch (\Exception) {
                    throw new \InvalidArgumentException(sprintf('Invalid id "%s"', $id));
                }

                $book->addAuthor($author);
            }
        }


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

    public function updateBook(int $bookId, ?string $title, ?string $newAuthors, ?string $existedAuthors, ?string $deleteAuthors): ?Book
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

        if ($newAuthors) {
            $names = explode(',', $newAuthors);

            foreach ($names as $name) {
                $author = $this->authorManager->createAuthorByName(trim($name));
                $book->addAuthor($author);
            }
        }

        if ($existedAuthors) {
            $ids = explode(',', $existedAuthors);

            foreach ($ids as $id) {
                try {
                    $author = $this->authorManager->getAuthorById((int)$id);
                } catch (\Exception) {
                    throw new \InvalidArgumentException(sprintf('Invalid author id "%s"', $id));
                }

                $book->addAuthor($author);
            }
        }

        if ($deleteAuthors) {
            $ids = explode(',', $deleteAuthors);

            if (count($book->getAuthors()) <= count($ids)) {
                throw new \InvalidArgumentException('Book should have at least one author');
            }

            foreach ($ids as $id) {
                try {
                    $author = $this->authorManager->getAuthorById((int)$id);
                } catch (EntityNotFoundException) {
                    throw new \InvalidArgumentException(sprintf('Invalid author id "%s"', $id));
                }

                $book->removeAuthor($author);
            }
        }

        $this->entityManager->flush();

        return $book;
    }

    /**
     * @return Book[]
     */
    public function searchBooksWithPagination(?string $title, ?string $author, int $page, int $perPage): array
    {
        /** @var BookRepository $bookRepository */
        $bookRepository = $this->entityManager->getRepository(Book::class);

        return $bookRepository->searchBooksWithPagination($title, $author, $page, $perPage);
    }
}