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
        $message = null;
        if ($request->isPost()) {
            $first = trim($request->value('first_name') ?? '');
            $last = trim($request->value('last_name') ?? '');
            $username = trim($request->value('username') ?? '');
            $email = trim($request->value('email') ?? '');
            $password = $request->value('password') ?? '';
            $password2 = $request->value('password2') ?? '';
            // Basic validation: username, password match, and min length for password
            if ($username === '' || $password === '' || $password !== $password2) {
                $message = 'Skontrolujte polia (username a heslá musí byť rovnaké).';
                if ($request->isAjax()) {
                    return new JsonResponse(['success' => false, 'message' => $message]);
                }

                $referer = $request->server('HTTP_REFERER') ?: $this->url('home.index');
                return $this->redirect($referer);
            }

            if (strlen((string)$password) < 8) {
                $message = 'Heslo musí mať najmenej 8 znakov.';
                if ($request->isAjax()) {
                    return new JsonResponse(['success' => false, 'message' => $message]);
                }
                $referer = $request->server('HTTP_REFERER') ?: $this->url('home.index');
                return $this->redirect($referer);
            }

            try {
                $exists = User::getCount('username = ?', [$username]);
                if ($exists > 0) {
                    $message = 'Používateľ s týmto menom už existuje.';

                    if ($request->isAjax()) {
                        return new JsonResponse(['success' => false, 'message' => $message]);
                    }

                    $referer = $request->server('HTTP_REFERER') ?: $this->url('home.index');
                    return $this->redirect($referer);
                }
                $user = new User();
                $user->setUsername($username);
                $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
                $user->setEmail($email ?: null);
                $user->setRole('user');
                if (method_exists($user, 'setFirstName')) {
                    $user->setFirstName($first);
                }
                if (method_exists($user, 'setLastName')) {
                    $user->setLastName($last);
                }
                $user->save();
                $auth = $this->app->getAuth();
                $auth?->login($username, $password);
                $referer = $request->server('HTTP_REFERER') ?: $this->url('home.index');
                return $this->redirect($referer);
            } catch (Exception $e) {
                $message = 'Registrácia zlyhala: ' . $e->getMessage();
                if ($request->isAjax()) {
                    return new JsonResponse(['success' => false, 'message' => $message]);
                }
                $referer = $request->server('HTTP_REFERER') ?: $this->url('home.index');
                return $this->redirect($referer);
            }
        }
        return $this->html(compact('message'));
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
