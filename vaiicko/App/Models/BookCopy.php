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
    protected ?bool $available = null;
    protected ?int $book_id = null;
    protected static function getTableName(): string
    {
        return 'book_copy';
    }
}
