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
    // optional descriptive text for the genre
    protected ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
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
