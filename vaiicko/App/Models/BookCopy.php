<?php

namespace App\Models;

use Framework\Core\Model;

/**
 * BookCopy model mapped to the `book_copy` table (note override of table name).
 */
class BookCopy extends Model
{
    // table name in DB is `book_copy` (underscored, singular)

    protected ?int $id = null;
    protected ?int $book_id = null;
    protected ?int $available = null;

    protected static function getTableName(): string
    {
        return 'book_copy';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getBookId(): ?int
    {
        return $this->book_id;
    }

    public function setBookId(?int $book_id): void
    {
        $this->book_id = $book_id;
    }

    public function getAvailable(): ?int
    {
        return $this->available;
    }

    public function setAvailable(?int $available): void
    {
        $this->available = $available;
    }
}
