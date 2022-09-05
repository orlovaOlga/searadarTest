<?php

namespace App\Controller\Api\v1;

use App\Entity\Book;
use App\Manager\BookManager;
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
     * @Route("/add",name="add_book", methods={"POST"})
     */
    public function createBookAction(Request $request): Response
    {
        $title = $request->request->get('title');
        $author = $request->request->get('author');
        $bookId = $this->bookManager->saveBook($title, $author);
        [$data, $code] = $bookId === null ?
            [['success' => false], Response::HTTP_BAD_REQUEST] :
            [['success' => true, 'bookId' => $bookId], Response::HTTP_CREATED];

        return new JsonResponse($data, $code);
    }

    /**
     * @Route("/get/{id}",name="get_book", methods={"GET"})
     */
    public function getBookByIdAction(int $id): Response
    {
        $book = $this->bookManager->getBookById($id);

        [$data, $code] = !$book ?
            [['success' => false], Response::HTTP_NOT_FOUND] :
            [['success' => true, 'book' => $book->toArray()], Response::HTTP_OK];

        return new JsonResponse($data, $code);
    }

    /**
     * @Route("/delete",name="delete_book", methods={"POST"})
     */
    public function deleteBookByIdAction(Request $request): Response
    {
        $bookId = $request->request->get('bookId');
        $result = $this->bookManager->deleteBookById($bookId);

        return new JsonResponse(
            ['success' => $result],
            $result ? Response::HTTP_NO_CONTENT : Response::HTTP_NOT_FOUND
        );
    }

    /**
     * @Route("/edit",name="edit_book", methods={"POST"})
     */
    public function updateBookAction(Request $request): Response
    {
        $bookId = $request->request->get('bookId');
        $title = $request->request->get('title');
        $author = $request->request->get('author');
        $result = $this->bookManager->updateBook($bookId, $title, $author);

        return new JsonResponse(
            ['success' => $result !== null],
            ($result !== null) ? Response::HTTP_OK : Response::HTTP_NOT_FOUND
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

        $books = $this->bookManager->searchBooks($title, $author, $page ?? 0, $perPage ?? BookManager::DEFAULT_PAGINATION_LIMIT);

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
                    'books' => array_map(static fn(Book $book) => $book->toArray(), $books)
                ], Response::HTTP_OK
            ];

        return new JsonResponse($data, $code);
    }
}