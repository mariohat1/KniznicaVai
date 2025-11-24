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
        // only admins can modify copies
        $auth = $this->app->getAuth();
        if (!$auth?->isLogged()) return false;
        $user = $auth->getUser();
        if (is_object($user)) {
            if (method_exists($user, 'getRole')) return strtolower((string)$user->getRole()) === 'admin';
            $vars = get_object_vars($user);
            return isset($vars['role']) && strtolower((string)$vars['role']) === 'admin';
        }
        return false;
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
        $session = $this->app->getSession();
        if ($book === null) {
            $items = $session->get('flash_messages', []);
            $items[] = ['type' => 'danger', 'message' => 'Kniha neexistuje.'];
            $session->set('flash_messages', $items);
            return $this->redirect($this->url('book.manage'));
        }

        if ($toAdd <= 0) {
            $items = $session->get('flash_messages', []);
            $items[] = ['type' => 'info', 'message' => 'Zadaj kladné číslo kopíí na pridanie.'];
            $session->set('flash_messages', $items);
            return $this->redirect($this->url('book.manage'));
        }

        $created = 0;
        for ($i = 0; $i < $toAdd; $i++) {
            try {
                $c = new BookCopy();
                $c->setBookId($book->getId());
                $c->save();
                $created++;
            } catch (\Throwable $e) {
                // ignore individual failures but continue
            }
        }

        $items = $session->get('flash_messages', []);
        $items[] = ['type' => 'success', 'message' => "Pridaných $created kópií." ];
        $session->set('flash_messages', $items);
        return $this->redirect($this->url('book.manage'));
    }

    public function index(Request $request): Response
    {
        // Redirect to book manage for now
        return $this->redirect($this->url('book.manage'));
    }
}

