<?php

namespace App\Controllers;

use App\Models\Book;
use App\Models\BookCopy;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class BookCopyController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        $auth = $this->app->getAuth();
        if (!$auth?->isLogged()) return false;
        $user = $auth->getUser();
        return strtolower((string)$user->getRole()) === 'admin';
    }

    /**
     * Admin: update total number of copies for a book.
     * Expects POST params: id, copies (int)
     */
    public function updateCopies(Request $request): Response
    {
        if (!$request->isPost()) {
            return $this->redirect($this->url('book.manage'));
        }
        $id = $request->value('id');
        $toAdd = (int)$request->post('copies');
        $book = Book::getOne($id);
        for ($i = 0; $i < $toAdd; $i++) {
            try {
                $c = new BookCopy();
                $c->setBookId($book->getId());
                $c->save();
            } catch (\Throwable $e) {
                // ignore individual failures but continue
            }
        }
        return $this->redirect($this->url('book.manage'));
    }

    public function index(Request $request): Response
    {
        return $this->redirect($this->url('book.manage'));
    }
}

