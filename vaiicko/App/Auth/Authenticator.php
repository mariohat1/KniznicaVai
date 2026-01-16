<?php

namespace App\Auth;

use App\Models\User;
use Framework\Core\App;
use Framework\Core\IAuthenticator;
use Framework\Core\IIdentity;
use Framework\Http\Session;

class Authenticator implements IAuthenticator
{

    private Session $session;
    private ?IIdentity $user = null;

    public function __construct(App $app)
    {
        $this->session = $app->getSession();
    }

    /**
     * @throws \Exception
     */
    public function login(string $username, string $password): bool
    {
        $username = trim($username);
        if ($username === '') return false;

        try {
            $users = User::getAll('username = ?', [$username], null, 1, 0);
        } catch (\Throwable $ex) {
            return false;
        }

        $userRow = $users[0] ?? null;
        if ($userRow === null) return false;

        $storedHash = $userRow->getPassword();
        if (empty($storedHash) || !password_verify($password, $storedHash)) {
            return false;
        }
        $this->user = $userRow;
        if ($userRow->getId() !== null) {
            $this->session->set('user', $this->user);
        }
        return true;
    }

    public function logout(): void
    {
        $this->user = null;
        $this->session->destroy();
    }

    public function isLogged(): bool
    {
        return $this->getUser() instanceof IIdentity;
    }

    public function getUser(): ?IIdentity
    {
        if ($this->user instanceof IIdentity) {
            return $this->user;
        }

        $sessionValue = $this->session->get('user');
        if (is_string($sessionValue) && $sessionValue !== '') {
            $u = new User();
            $u->setUsername($sessionValue);
            $this->user = $u;
            $this->session->set('user', $this->user);
            return $this->user;
        }

        if ($sessionValue instanceof User) {
            $this->user = $sessionValue;
            return $this->user;
        }

        $uid = $this->session->get('user_id');
        if ($uid !== null) {
            $u = User::getOne((int)$uid);
            if ($u !== null) {
                $this->user = $u;
                return $this->user;

            }
        }
        return null;
    }


    public function __get(string $name): mixed
    {
        if ($name === 'user') {
            return $this->getUser();
        }
        return null;
    }
}