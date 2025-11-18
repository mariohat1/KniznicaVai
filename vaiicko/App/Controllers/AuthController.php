<?php

namespace App\Controllers;

use App\Configuration;
use Exception;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Responses\ViewResponse;
use App\Models\User;

/**
 * Class AuthController
 *
 * This controller handles authentication actions such as login, logout, and redirection to the login page. It manages
 * user sessions and interactions with the authentication system.
 *
 * @package App\Controllers
 */
class AuthController extends BaseController
{
    /**
     * Redirects to the login page.
     *
     * This action serves as the default landing point for the authentication section of the application, directing
     * users to the login URL specified in the configuration.
     *
     * @return \Framework\Http\Responses\Response The response object for the redirection to the login page.
     */
    public function index(Request $request): Response
    {
        return $this->redirect(Configuration::LOGIN_URL);
    }

    /**
     * Authenticates a user and processes the login request.
     *
     * This action handles user login attempts. If the login form is submitted, it attempts to authenticate the user
     * with the provided credentials. Upon successful login, the user is redirected to the admin dashboard.
     * If authentication fails, an error message is displayed on the login page.
     *
     * @return Response The response object which can either redirect on success or render the login view with
     *                  an error message on failure.
     * @throws Exception If the parameter for the URL generator is invalid throws an exception.
     */
    public function login(Request $request): Response
    {
        $message = null;

        if ($request->isPost()) {
            // accept either 'login' or 'username' field for compatibility
            $loginField = $request->value('login') ?? $request->value('username');
            $password = $request->value('password');

            $logged = $this->app->getAuth()->login($loginField, $password);
            $referer = $request->server('HTTP_REFERER') ?: $this->url('home.index');

            if ($logged) {
                return $this->redirect($referer);
            }

            // On failure, set session flags so layout reopens modal and shows message
            $session = $this->app->getSession();
            $items = $session->get('flash_messages', []);
            // set a danger flash too (optional)
            $items[] = ['type' => 'danger', 'message' => 'Neplatné meno alebo heslo'];
            $session->set('flash_messages', $items);

            $session->set('open_auth_modal', true);
            $session->set('auth_modal_mode', 'login');
            $session->set('auth_modal_message', 'Neplatné meno alebo heslo');
            $session->set('auth_modal_username', $loginField ?? '');

            return $this->redirect($referer);
        }

        return $this->html(compact('message'));
    }

    /**
     * Registers a new user and handles the registration request.
     *
     * This action processes user registration attempts. If the registration form is submitted, it validates the
     * provided data, creates a new User model, hashes the password, and saves the user. Upon successful
     * registration, the user is redirected to the login page. If registration fails, an error message is displayed
     * on the registration page.
     *
     * @return Response The response object which can either redirect to the login page on success or render the
     *                  registration view with an error message on failure.
     */


    /**
     * Logs out the current user.
     *
     * This action terminates the user's session and redirects them to a view. It effectively clears any authentication
     * tokens or session data associated with the user.
     *
     * @return ViewResponse The response object that renders the logout view.
     */
    public function logout(Request $request): Response
    {
        $this->app->getAuth()->logout();
        return $this->html();
    }
}
