<?php

namespace App\Models;

use Framework\Core\Model;

/**
 * Author model mapped to the `authors` table.
 */
class Author extends Model
{
    protected ?int $id = null;
    protected ?string $first_name = null;
    protected ?string $last_name = null;
    protected ?int $birth_year = null;
    protected ?int $death_year = null;
    protected ?string $description = null;
    protected ?string $photo = null;

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): void
    {
        $this->photo = $photo;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(?string $first_name): void
    {
        $this->first_name = $first_name;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(?string $last_name): void
    {
        $this->last_name = $last_name;
    }

    public function getBirthYear(): ?int
    {
        return $this->birth_year;
    }

    public function setBirthYear(?int $birth_year): void
    {
        $this->birth_year = $birth_year;
    }

    public function getDeathYear(): ?int
    {
        return $this->death_year;
    }

    public function setDeathYear(?int $death_year): void
    {
        $this->death_year = $death_year;
    }
}
