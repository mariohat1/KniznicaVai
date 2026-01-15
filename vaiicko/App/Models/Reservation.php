<?php

namespace App\Models;

use Framework\Core\Model;

/**
 * Reservation model mapped to the `reservation` table (singular).
 */
class Reservation extends Model
{


    protected ?int $id = null;
    protected ?int $is_reserved = null;
    protected ?int $user_id = null;


    public function getIsReserved(): ?int
    {
        return $this->is_reserved;
    }

    public function setIsReserved(?int $is_reserved): void
    {
        $this->is_reserved = $is_reserved;
    }


    protected ?int $book_copy_id = null;
    protected ?string $created_at = null;
    protected ?string $reserved_until = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getReserved(): ?string
    {
        return $this->reserved;
    }

    public function setReserved(?string $reserved): void
    {
        $this->reserved = $reserved;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(?int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getBookCopyId(): ?int
    {
        return $this->book_copy_id;
    }

    public function setBookCopyId(?int $book_copy_id): void
    {
        $this->book_copy_id = $book_copy_id;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setCreatedAt(?string $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function getReservedUntil(): ?string
    {
        return $this->reserved_until;
    }

    public function setReservedUntil(?string $reserved_until): void
    {
        $this->reserved_until = $reserved_until;
    }


}
