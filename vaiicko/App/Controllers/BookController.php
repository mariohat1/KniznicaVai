<?php

namespace App\Controllers;

use App\Models\Book;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class BookController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        // Only admin users can perform non-read actions (add/store). Allow index for everyone.
        if ($action === 'index') {
            return true;
        }

        $auth = $this->app->getAuth();
        if (!$auth->isLogged()) {
            return false;
        }

        $user = $auth->getUser();
        // If user has role property, require it to be 'admin'. Otherwise, fallback to username check for DummyAuthenticator.
        if (is_object($user)) {
            if (property_exists($user, 'role')) {
                return ($user->role === 'admin');
            }
            if (method_exists($user, 'getUsername')) {
                return $user->getUsername() === 'admin';
            }
            if (property_exists($user, 'username')) {
                return ($user->username === 'admin');
            }
        }

        return false;
    }

    public function index(Request $request): Response
    {
        $books = Book::getAll();
        return $this->html(['books' => $books]);
    }

    public function add(Request $request): Response
    {
        // Show a simple form for adding a book
        return $this->html();
    }

    public function store(Request $request): Response
    {
        // Basic store implementation: create Book from POST and save
        $book = new Book();
        $book->setFromRequest($request);
        $book->save();
        return $this->redirect($this->url('book.index'));
    }
}

