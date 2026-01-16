<?php

namespace App\Controllers;

use Exception;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Responses\JsonResponse;
use App\Models\User;

class UserController extends BaseController
{
    /**
     * Shows / handles user registration.
     */
    public function register(Request $request): Response
    {
        if ($request->isPost()) {
            $username = trim($request->value('username') ?? '');
            $email = trim($request->value('email') ?? '');
            $password = $request->value('password') ?? '';
            $password2 = $request->value('password2') ?? '';

            // Validation: empty fields or mismatched passwords
            if ($username === '' || $password === '' || $password !== $password2) {
                return new JsonResponse(['success' => false, 'message' => 'Skontrolujte polia (username a heslá musí byť rovnaké).']);
            }

            // Validation: password length
            if (strlen((string)$password) < 8) {
                return new JsonResponse(['success' => false, 'message' => 'Heslo musí mať najmenej 8 znakov.']);
            }

            // Validation: email format
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return new JsonResponse(['success' => false, 'message' => 'Neplatný e-mail.']);
            }

            try {
                // Check if username exists
                $exists = User::getCount('username = ?', [$username]);
                if ($exists > 0) {
                    return new JsonResponse(['success' => false, 'message' => 'Používateľ s týmto menom už existuje.']);
                }

                // Check if email exists
                if ($email !== '') {
                    $existsEmail = User::getCount('email = ?', [$email]);
                    if ($existsEmail > 0) {
                        return new JsonResponse(['success' => false, 'message' => 'Používateľ s týmto emailom už existuje.']);
                    }
                }

                // Create new user
                $user = new User();
                $user->setUsername($username);
                $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
                $user->setEmail($email ?: null);
                $user->setRole('user');
                $user->save();

                // Auto-login
                $auth = $this->app->getAuth();
                $auth?->login($username, $password);

                $referer = $request->server('HTTP_REFERER') ?: $this->url('home.index');
                return new JsonResponse(['success' => true, 'redirect' => $referer]);
            } catch (Exception $e) {
                return new JsonResponse(['success' => false, 'message' => 'Registrácia zlyhala: ' . $e->getMessage()]);
            }
        }

        // GET request - show registration page
        return $this->html();
    }

    public function index(Request $request): Response
    {
        return $this->html();
    }

    /**
     * Alias for register - provides `user.add` route for forms that expect an "add" action.
     */
    public function add(Request $request): Response
    {
        return $this->register($request);
    }
}
