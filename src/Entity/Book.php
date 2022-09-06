<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\BookRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(
 *     name="`book`",
 *     indexes={
 *         @ORM\Index(name="title", columns={"title"})
 *     }
 * )
 * @ORM\Entity(repositoryClass=BookRepository::class)
 */
class Book
{
    /**
     * @ORM\Column(name="id", type="bigint", unique=true)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $title;

    /**
     * @ORM\ManyToMany(targetEntity="Author", inversedBy="books")
     * @ORM\JoinTable(
     *     name="author_to_book",
     *     joinColumns={
     *          @ORM\JoinColumn(name="book_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *          @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     *     }
     * )
     */
    private Collection $authors;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     */
    private DateTime $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="update")
     */
    private DateTime $updatedAt;

    public function __construct()
    {
        $this->authors = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getAuthors(): Collection
    {
        return $this->authors;
    }

    public function hasAuthor(Author $author): bool
    {
        return $this->authors->contains($author);
    }

    public function addAuthor(Author $author): self
    {
        if(!$this->hasAuthor($author)){
            $this->authors->add($author);
        }

        return $this;
    }

    public function removeAuthor(Author $author): self
    {
        if($this->hasAuthor($author)){
            $this->authors->removeElement($author);
        }

        return $this;
    }

    public function getCreatedAt(): DateTime {
        return $this->createdAt;
    }

    public function setCreatedAt(): void {
        $this->createdAt = new DateTime();
    }

    public function getUpdatedAt(): DateTime {
        return $this->updatedAt;
    }

    public function setUpdatedAt(): void {
        $this->updatedAt = new DateTime();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'authors' => array_map(static fn($author) => [
                'id' => $author->getId(),
                'name' => $author->getName()
            ], $this->getAuthors()->getValues())
        ];
    }
}
