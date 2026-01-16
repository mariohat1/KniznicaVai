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
        if (!$auth || !$auth->isLogged()) {
            return false;
        }

        $user = $auth->getUser();
        return $user && strtolower((string)$user->getRole()) === 'admin';
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
                $c->setAvailable(1);
                $c->save();
            } catch (\Throwable $e) {
                // ignore individual failures but continue
            }
        }
        // After adding copies, stay on the copies management page for the same book
        return $this->redirect($this->url('bookcopy.index', ['book_id' => $book->getId()]));
    }

    /**
     * Admin: set availability flag for a specific copy or (fallback) for all copies of a book.
     * POST params:
     *  - copy_id (preferred) OR id (book id)
     *  - available (0|1)
     */
    public function setAvailability(Request $request): Response
    {
        if (!$request->isPost()) {
            return $this->redirect($this->url('book.manage'));
        }

        $avail = $request->post('available');
        $avail = $avail !== null ? ((int)$avail ? 1 : 0) : 0;

        $copyId = $request->value('copy_id');
        if ($copyId !== null && trim((string)$copyId) !== '') {
            // update single copy
            try {
                BookCopy::executeRawSQL('UPDATE `book_copy` SET `available` = ? WHERE `id` = ?', [$avail, (int)$copyId]);
            } catch (\Throwable $e) {
                // ignore
            }
            $backBook = $request->value('book_id');
            if ($backBook !== null) return $this->redirect($this->url('bookcopy.index', ['book_id' => $backBook]));
            return $this->redirect($this->url('book.manage'));
        }

        // fallback: book-level update (existing behavior)
        $bookId = $request->value('id');
        if ($bookId === null) {
            return $this->redirect($this->url('book.manage'));
        }
        try {
            BookCopy::executeRawSQL('UPDATE `book_copy` SET `available` = ? WHERE `book_id` = ?', [$avail, (int)$bookId]);
        } catch (\Throwable $e) {

        }
        return $this->redirect($this->url('book.manage'));
    }

    /**
     * Show copies for a specific book (admin)
     * GET param: book_id
     */
    public function index(Request $request): Response
    {
        $bookId = $request->value('book_id');
        if ($bookId === null) {
            return $this->redirect($this->url('book.manage'));
        }
        $book = Book::getOne($bookId);
        if ($book === null) {
            return $this->redirect($this->url('book.manage'));
        }

        $copies = BookCopy::getAll('book_id = ?', [(int)$bookId]);

        // Preload active reservations for these copies to show reserved status without N+1 queries
        $reservationsMap = [];
        $usersMap = [];
        $copyIds = array_values(array_filter(array_map(fn($c) => $c->getId(), $copies)));
        if (!empty($copyIds)) {
            $placeholders = implode(',', array_fill(0, count($copyIds), '?'));
            // active reservation = is_reserved = 1 (we treat all as active; expiry handled elsewhere/event)
            $where = "book_copy_id IN ($placeholders) AND is_reserved = 1";
            $reservations = \App\Models\Reservation::getAll($where, $copyIds);
            $userIds = [];
            foreach ($reservations as $r) {
                $reservationsMap[$r->getBookCopyId()] = $r;
                $uid = $r->getUserId();
                if ($uid !== null) $userIds[] = $uid;
            }
            $userIds = array_values(array_unique($userIds));
            if (!empty($userIds)) {
                $uPlace = implode(',', array_fill(0, count($userIds), '?'));
                $users = \App\Models\User::getAll("id IN ($uPlace)", $userIds);
                foreach ($users as $u) {
                    $usersMap[$u->getId()] = $u;
                }
            }
        }

        return $this->html(['copies' => $copies, 'book' => $book, 'reservations' => $reservationsMap, 'users' => $usersMap], 'index');
    }

    /**
     * Delete a single copy (admin)
     * POST params: copy_id, book_id (optional)
     */
    public function delete(Request $request): Response
    {
        if (!$request->isPost()) {
            return $this->redirect($this->url('book.manage'));
        }

        $copyId = $request->post('copy_id') ?? $request->value('copy_id');
        if ($copyId) {
            try {
                $copy = BookCopy::getOne((int)$copyId);
                if ($copy !== null) {
                    $bookId = $copy->getBookId();
                    $copy->delete();
                    // redirect back to copies list for that book
                    return $this->redirect($this->url('bookcopy.index', ['book_id' => $bookId]));
                }
            } catch (\Throwable $e) {
                // ignore and continue to fallback
            }
        }

        // fallback: if book_id provided redirect there, else to manage
        $backBook = $request->post('book_id') ?? $request->value('book_id');
        if ($backBook) return $this->redirect($this->url('bookcopy.index', ['book_id' => $backBook]));
        return $this->redirect($this->url('book.manage'));
    }
}
