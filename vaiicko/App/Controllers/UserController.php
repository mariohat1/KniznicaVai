<?php

namespace App\Controllers;

use Exception;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
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

            // basic validation
            if ($username === '' || $password === '' || $password !== $password2) {
                $message = 'Skontrolujte polia (username a heslá musí byť rovnaké).';
                $session = $this->app->getSession();
                $session->set('open_auth_modal', true);
                $session->set('auth_modal_mode', 'register');
                $session->set('auth_modal_message', $message);
                $session->set('auth_modal_username', $username);
                $referer = $request->server('HTTP_REFERER') ?: $this->url('home.index');
                return $this->redirect($referer);
            }

            try {
                // check username uniqueness
                $exists = User::getCount('username = ?', [$username]);
                if ($exists > 0) {
                    $message = 'Používateľ s týmto menom už existuje.';
                    $session = $this->app->getSession();
                    $session->set('open_auth_modal', true);
                    $session->set('auth_modal_mode', 'register');
                    $session->set('auth_modal_message', $message);
                    $session->set('auth_modal_username', $username);
                    $referer = $request->server('HTTP_REFERER') ?: $this->url('home.index');
                    return $this->redirect($referer);
                }

                // create and save user
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

                // attempt to auto-login the newly registered user

                $auth = $this->app->getAuth();
                $auth?->login($username, $password);


                // success flash
                $session = $this->app->getSession();
                $items = $session->get('flash_messages', []);
                $items[] = ['type' => 'success', 'message' => 'Registrácia prebehla úspešne.'];
                $session->set('flash_messages', $items);

                $referer = $request->server('HTTP_REFERER') ?: $this->url('home.index');
                return $this->redirect($referer);
            } catch (Exception $e) {
                $message = 'Registrácia zlyhala: ' . $e->getMessage();
                $session = $this->app->getSession();
                $session->set('open_auth_modal', true);
                $session->set('auth_modal_mode', 'register');
                $session->set('auth_modal_message', $message);
                $session->set('auth_modal_username', $username);
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
