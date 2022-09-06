<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 *
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    const DEFAULT_PAGINATION_LIMIT = 20;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function add(Book $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Book $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Book[]
     */
    public function searchBooksWithPagination(?string $title, ?string $author, int $page, int $perPage): array
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(Book::class, 'alias');

        $sql = "SELECT b.* FROM author_to_book
                    LEFT JOIN author a on author_to_book.author_id = a.id
                    LEFT JOIN book b on author_to_book.book_id = b.id
                WHERE b.title LIKE :title AND a.name LIKE :name
                    GROUP BY book_id
                    ORDER BY b.title
                LIMIT :perPage OFFSET :offset";

        $nativeQuery = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $nativeQuery->setParameters(
            [
                'title' => "%$title%",
                'name' => "%$author%",
                'perPage' => $perPage,
                'offset' => ($page - 1) * $perPage,
            ]);

        return $nativeQuery->getResult();
    }

    public function getRandomBook(): ?Book
    {
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addRootEntityFromClassMetadata(Book::class, 'alias');

        $nativeQuery = $this->getEntityManager()->createNativeQuery('SELECT * FROM book ORDER BY rand() ASC LIMIT 1', $rsm);

        return $nativeQuery->getOneOrNullResult();
    }
}
