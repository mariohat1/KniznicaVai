<?php

namespace App\Models;

use Framework\Core\Model;

/**
 * Category model mapped to the `categories` table.
 */
class Category extends Model
{
    protected ?int $id = null;
    protected ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}
