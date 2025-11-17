<?php

namespace App\Models;

use Framework\Core\Model;

/**
 * Genre model mapped to the `genres` table.
 */
class Genre extends Model
{
    protected ?int $id = null;
    protected ?string $name = null;
}
