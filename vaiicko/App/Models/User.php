<?php

namespace App\Models;

use Framework\Core\IIdentity;
use Framework\Core\Model;


class User extends Model implements IIdentity
{
    protected ?int $id = null;
    protected ?string $username = null;
    protected ?string $password = null;
    protected ?string $email = null;
    protected ?string $role = null;
    protected ?string $created_at = null;

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setCreatedAt(?string $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getUsername(): string
    {
        return (string)($this->username ?? '');
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }


    public function setRole(?string $role): void
    {
        $this->role = $role;
    }



}


