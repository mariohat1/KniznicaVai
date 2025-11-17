<?php

namespace App\Models;

use Framework\Core\Model;

/**
 * Reservation model mapped to the `reservation` table (singular).
 */
class Reservation extends Model
{
    // DB table is named `reservation` (singular), override conventions
    protected static ?string $tableName = 'reservation';

    protected ?int $id = null;
    protected ?string $reserved = null;
    protected ?int $user_id = null;
    protected ?int $book_copy_id = null;
    protected ?string $created_at = null; // TIMESTAMP as string
}
