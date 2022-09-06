<?php
declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\Entity\Author;
use App\Entity\Book;
use App\Manager\BookManager;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1/books")
 */
class BookController extends AbstractController
{
    private BookManager $bookManager;

    public function __construct(BookManager $bookManager)
    {
        $this->bookManager = $bookManager;
    }

    /**
     * @Route("/create",name="add_book", methods={"POST"})
     */
    public function createBookAction(Request $request): Response
    {
        $title = $request->request->get('title');
        $existedAuthors = $request->request->get('chooseAuthors');
        $newAuthors = $request->request->get('addNewAuthors');

        if (!$title) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Title is required parameter'
            ],
                Response::HTTP_BAD_REQUEST);
        }

        if (!$existedAuthors && !$newAuthors) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Choose existed author or add new one'
            ],
                Response::HTTP_BAD_REQUEST);
        }

        try {
            $book = $this->bookManager->createBook($title, $existedAuthors, $newAuthors);
        } catch (\InvalidArgumentException $exception) {

            return new JsonResponse([
                'success' => false,
                'message' => $exception->getMessage()
            ],
                Response::HTTP_BAD_REQUEST);
        }

        [$data, $code] = !$book->getId() ?
            [
                ['success' => false, 'message' => 'Book was not created, check parameters'],
                Response::HTTP_BAD_REQUEST
            ] :
            [
                [
                    'success' => true,
                    'message' => 'Book added',
                    'book' => $book->toArray()
                ],
                Response::HTTP_CREATED
            ];

        return new JsonResponse($data, $code);
    }

    /**
     * @Route("/get/{bookId}", name="get_book", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function getBookByIdAction(int $bookId): Response
    {
        $book = $this->bookManager->getBookById($bookId);

        [$data, $code] = !$book ?
            [
                [
                    'success' => false,
                    'message' => 'There are no books with this id'
                ],
                Response::HTTP_NOT_FOUND
            ] :
            [
                [
                    'success' => true,
                    'message' => 'OK',
                    'book' => $book->toArray()
                ],
                Response::HTTP_OK];

        return new JsonResponse($data, $code);
    }

    /**
     * @Route("/delete",name="delete_book", methods={"POST"})
     */
    public function deleteBookByIdAction(Request $request): Response
    {
        $bookId = $request->request->get('bookId');

        if (!$bookId) {
            return new JsonResponse(
                [
                    'success' => false,
                    'message' => 'BookId is require parameter'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $result = $this->bookManager->deleteBookById((int)$bookId);

        [$data, $code] = [
            [
                'success' => $result,
                'message' => $result ? 'OK' : 'There are no books with this ID'
            ],
            $result ? Response::HTTP_NO_CONTENT : Response::HTTP_NOT_FOUND
        ];

        return new JsonResponse($data, $code);
    }

    /**
     * @Route("/update",name="update_book", methods={"POST"})
     */
    public function updateBookAction(Request $request): Response
    {
        $bookId = $request->request->get('bookId');
        $title = $request->request->get('title');
        $newAuthors = $request->request->get('addNewAuthors');
        $existedAuthors = $request->request->get('chooseAuthors');
        $deleteAuthors = $request->request->get('deleteAuthors');

        if (!$bookId) {
            return new JsonResponse(
                [
                    'success' => false,
                    'message' => 'bookId is required parameter'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (!$title && !$newAuthors && !$existedAuthors && !$deleteAuthors) {
            return new JsonResponse(
                [
                    'success' => false,
                    'message' => 'Nothing to update. Please enter at least one parameter'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $updatedBook = $this->bookManager->updateBook((int)$bookId, $title, $newAuthors, $existedAuthors, $deleteAuthors);
            $isUpdated = $updatedBook !== null;
        } catch (\Exception $e) {

            return new JsonResponse(
                [
                    'success' => false,
                    'message' => $e->getMessage()
                ],
                Response::HTTP_NOT_FOUND
            );
        }


        return new JsonResponse(
            [
                'success' => $isUpdated,
                'message' => $isUpdated ? 'OK' : 'Book was not updated, check parameters',
                'book' => $updatedBook->toArray()
            ],
            $isUpdated ? Response::HTTP_OK : Response::HTTP_NOT_FOUND
        );
    }

    /**
     * @Route("/search",name="search_book", methods={"GET"})
     */
    public function searchBookAction(Request $request): Response
    {
        $title = $request->query->get('title');
        $author = $request->query->get('author');
        $perPage = $request->query->get('perPage');
        $page = $request->query->get('page');

        if (!$page || $page < 0) {
            $page = BookRepository::DEFAULT_PAGE_NUMBER;
        }

        if (!$perPage) {
            $perPage = BookRepository::DEFAULT_PAGINATION_LIMIT;
        }

        $books = $this->bookManager->searchBooksWithPagination($title, $author, (int)$page, (int)$perPage);

        [$data, $code] = !$books ?
            [
                [
                    'success' => false,
                    'message' => 'No books found with these parameters'
                ],
                Response::HTTP_NOT_FOUND
            ] :
            [
                [
                    'success' => true,
                    'message' => 'OK',
                    'books' => array_map(static fn(Book $book) => $book->toArray(), $books)
                ],
                Response::HTTP_OK
            ];

        return new JsonResponse($data, $code);
    }

    /**
     * @Route("/random",name="get_random_book", methods={"GET"})
     */
    public function getRandomBookAction(EntityManagerInterface $entityManager): Response
    {
        /** @var BookRepository $bookRepository */
        $bookRepository = $entityManager->getRepository(Book::class);
        $book = $bookRepository->getRandomBook();

        [$data, $code] = !$book ?
            [
                [
                    'success' => false,
                    'message' => 'There are no books in collection'
                ],
                Response::HTTP_NOT_FOUND
            ] :
            [
                [
                    'success' => true,
                    'message' => 'OK',
                    'book' => $book->toArray()
                ],
                Response::HTTP_OK
            ];

        return new JsonResponse($data, $code);
    }

    /**
     * @Route("/authors",name="get_authors_list", methods={"GET"})
     */
    public function getAuthorsListAction(EntityManagerInterface $entityManager): Response
    {
        /** @var AuthorRepository $authorRepository */
        $authorRepository = $entityManager->getRepository(Author::class);

        $authorList = $authorRepository->getAuthorList();

        [$data, $code] = $authorList === null ?
            [
                [
                    'success' => false,
                    'message' => 'There are no authors in collection'
                ],
                Response::HTTP_NOT_FOUND
            ] :
            [
                [
                    'success' => true,
                    'message' => 'OK',
                    'authors' => $authorList
                ],
                Response::HTTP_OK
            ];

        return new JsonResponse($data, $code);
    }
}