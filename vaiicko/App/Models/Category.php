<?php

namespace App\Models;

use Framework\Core\Model;

/**
 * Category model mapped to the `categories` table.
 */
class Category extends Model
{
    public ?int $id = null;
    public ?string $name = null;
}

