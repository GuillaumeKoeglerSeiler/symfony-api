<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\BookRepository;
use JMS\Serializer\Annotation\Since;
use JMS\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiResource;
use Hateoas\Configuration\Annotation as Hateoas;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Hateoas\Relation(
 *      "self",
 *      href=@Hateoas\Route(
 *          "detailBook",
 *          parameters = { "id" = "expr(object.getId())"}
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getBooks")
 * )
 * @Hateoas\Relation(
 *      "delete",
 *      href=@Hateoas\Route(
 *          "deleteBook",
 *          parameters = { "id" = "expr(object.getId())"}
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getBooks"),
 * )
 * @Hateoas\Relation(
 *      "update",
 *      href=@Hateoas\Route(
 *          "updateBook",
 *          parameters = { "id" = "expr(object.getId())"}
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getBooks", excludeIf = "expr(not is_granted('ROLE_ADMIN'))"),
 * )
 * @ApiResource()
 * @ORM\Entity(repositoryClass=BookRepository::class)
 */
class Book
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"getBooks"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"getBooks"})
     * @Assert\NotBlank(message="Le titre du livre est obligatoire")
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"getBooks"})
     */
    private $coverText;

    /**
     * @ORM\ManyToOne(targetEntity=Author::class, inversedBy="books")
     * @Groups({"getBooks"})
     */
    private $author;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"getBooks"})
     * @Since("2.0")
     */
    private $comment;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCoverText(): ?string
    {
        return $this->coverText;
    }

    public function setCoverText(?string $coverText): self
    {
        $this->coverText = $coverText;

        return $this;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
