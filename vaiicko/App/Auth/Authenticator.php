<?php

namespace App\Auth;

use App\Models\User;
use Framework\Auth\DummyAuthenticator;
use Framework\Core\App;
use Framework\DB\Connection;
use Framework\Http\Session;
use PDO;

class Authenticator extends DummyAuthenticator
{

    private Session $session;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->session = $app->getSession();
    }

    public function login(string $username, string $password): bool
    {
        $sql = 'SELECT * FROM `users` WHERE `username` = :username LIMIT 1';
        $stmt = Connection::getInstance()->prepare($sql);
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return false;

        if (empty($row['password']) || !password_verify($password, $row['password'])) {
            return false;
        }

        $displayName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        if ($displayName === '') {
            $displayName = $row['username'] ?? $username;
        }

        $user = new User();
        $user->setId((int)$row['id']);
        $user->setUsername($row['username']);
        $user->setEmail($row['email'] ?? null);
        $user->setRole($row['role'] ?? null);

        $this->session->set('user', $user);
        return true;
    }
}