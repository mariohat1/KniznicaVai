<?php

namespace App\Models;

use Framework\Core\Model;

/**
 * Book model mapped to the `books` table.
 */
class Book extends Model
{
    protected ?int $id = null;
    protected ?string $isbn = null;
    protected ?string $year_published = null;
    protected ?string $description = null;
    protected ?string $title = null;
    protected ?int $author_id = null;
    protected ?int $category_id = null;
    protected ?int $genre_id = null;
    protected ?string $photo = null;

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): void
    {
        $this->photo = $photo;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(?string $isbn): void
    {
        $this->isbn = $isbn;
    }

    public function getYearPublished(): ?string
    {
        return $this->year_published;
    }

    public function setYearPublished(?string $year_published): void
    {
        $this->year_published = $year_published;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getAuthorId(): ?int
    {
        return $this->author_id;
    }

    public function setAuthorId(?int $author_id): void
    {
        $this->author_id = $author_id;
    }

    public function getCategoryId(): ?int
    {
        return $this->category_id;
    }

    public function setCategoryId(?int $category_id): void
    {
        $this->category_id = $category_id;
    }

    public function getGenreId(): ?int
    {
        return $this->genre_id;
    }

    public function setGenreId(?int $genre_id): void
    {
        $this->genre_id = $genre_id;
    }

}
