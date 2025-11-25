<?php

namespace App\Controllers;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Reservation;
use App\Models\User;
use App\Support\AuthView;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class ReservationController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        // Allow logged-in users to view their reservations (index).
        // Allow create only for logged-in non-admin users (existing behavior).
        $auth = $this->app->getAuth();
        if ($action === 'index') {
            return $auth?->isLogged() ? true : false;
        }
        if ($action === 'create') {
            if (!$auth?->isLogged()) return false;
            $user = $auth->getUser();
            if (is_object($user)) {
                if (method_exists($user, 'getRole')) return strtolower((string)$user->getRole()) !== 'admin';
                $vars = get_object_vars($user);
                return !isset($vars['role']) || strtolower((string)$vars['role']) !== 'admin';
            }
            return true;
        }
        if ($action === 'manage') {
            // only admin can access manage
            if (!$auth?->isLogged()) return false;
            $user = $auth->getUser();
            if (is_object($user)) {
                if (method_exists($user, 'getRole')) return strtolower((string)$user->getRole()) === 'admin';
                $vars = get_object_vars($user);
                return isset($vars['role']) && strtolower((string)$vars['role']) === 'admin';
            }
            return false;
        }

        return false;
    }

    /**
     * Create a reservation (POST). Expects param `id` = book id.
     */
    public function create(Request $request): Response
    {
        $auth = $this->app->getAuth();

        if (!$auth->isLogged()) {
            $bid = $request->value('id');
            if ($bid) {
                return $this->redirect($this->url('book.view', ['id' => $bid, 'must_login' => 1]));
            }
            return $this->redirect($this->url('book.index'));
        }
        $user = $auth->getUser();
        $role = null;
        $userId = null;
        $role = $user->getRole();
        $userId = $user->getId();


        if (strtolower((string)$role) === 'admin') {
            // admins cannot reserve; silently redirect to book index
            return $this->redirect($this->url('book.index'));
        }

        $id = $request->value('id');
        $book = Book::getOne($id);
        if ($book === null) {
            // book missing -> redirect to book index
            return $this->redirect($this->url('book.index'));
        }

        // find an available copy by checking reservations (controller handles logic)
        $allCopies = BookCopy::getAll('book_id = ?', [$book->getId()]);
        $copy = null;
        foreach ($allCopies as $c) {
            $reservedCount = Reservation::getCount('book_copy_id = ? AND is_active = 1', [$c->getId()]);
            if ($reservedCount === 0) {
                $copy = $c;
                break;
            }
        }
        if ($copy === null) {
            // no available copies -> redirect to book view
            return $this->redirect($this->url('book.view', ['id' => $book->getId()]));
        }

        try {
            $reservation = new Reservation();
            // mark as reserved and active, set user and copy and created timestamp
            $reservation->setIsReserved(1);
            $reservation->setIsActive(1);
            $reservation->setUserId($userId);
            $reservation->setBookCopyId($copy->getId());
            $reservation->setCreatedAt(date('Y-m-d H:i:s'));
            $reservation->save();

            // reservation saved successfully -> redirect with success flag and reserved copy id
            return $this->redirect($this->url('book.view', ['id' => $book->getId(), 'reserved' => 1]));
        } catch (\Throwable $e) {
            // reservation failed
            return $this->redirect($this->url('book.view', ['id' => $book->getId()]));
        }
    }

    public function index(Request $request): Response
    {
        $auth = $this->app->getAuth();
        if (!$auth->isLogged()) {
            return $this->redirect($this->url('book.index'));
        }

        $user = $auth->getUser();
        $userId = null;
        if (is_object($user) && method_exists($user, 'getId')) {
            $userId = $user->getId();
        } elseif (is_object($user)) {
            $vars = get_object_vars($user);
            $userId = $vars['id'] ?? null;
        }

        if ($userId === null) {
            return $this->redirect($this->url('book.index'));
        }

        // Fetch reservations for this user (active and historical). Controller composes related info.
        $reservations = Reservation::getAll('user_id = ?', [$userId]);
        $items = [];
        foreach ($reservations as $r) {
            $copy = null;
            $book = null;
            try {
                $copy = BookCopy::getOne($r->getBookCopyId());
                if ($copy) $book = Book::getOne($copy->getBookId());
            } catch (\Throwable $e) {
            }
            $items[] = ['reservation' => $r, 'copy' => $copy, 'book' => $book];
        }

        return $this->html(['items' => $items], 'index');
    }

    /**
     * Admin: show all reservations (manage)
     */
    public function manage(Request $request): Response
    {
        $auth = $this->app->getAuth();
        if (!$auth?->isLogged()) return $this->redirect($this->url('book.index'));
        $user = $auth->getUser();
        $role = null;
        if (is_object($user)) {
            $role = method_exists($user, 'getRole') ? $user->getRole() : (get_object_vars($user)['role'] ?? null);
        }
        if (strtolower((string)$role) !== 'admin') {
            return $this->redirect($this->url('book.index'));
        }

        // Filters via GET: q (query for book title), status (active|finished|all)
        $q = trim((string)$request->value('q'));
        $status = $request->value('status'); // expected: 'active'|'finished'|'all' or null

        $whereParts = [];
        $whereParams = [];

        // If query present, find book ids matching title -> then find book_copy ids
        if ($q !== '') {
            // search books by title
            $like = '%' . $q . '%';
            $books = Book::getAll('title LIKE ?', [$like]);
            $bookIds = array_filter(array_map(fn($b) => $b->getId(), $books));
            if (empty($bookIds)) {
                $items = [];
                $isAjax = $request->isAjax();
                return $this->html(['items' => $items, 'q' => $q, 'status' => $status, 'ajax' => $isAjax], 'manage');
            }
            // find copies for these books
            $placeholders = implode(',', array_fill(0, count($bookIds), '?'));
            $copies = BookCopy::getAll("book_id IN ($placeholders)", $bookIds);
            $copyIds = array_filter(array_map(fn($c) => $c->getId(), $copies));
            if (empty($copyIds)) {
                $items = [];
                $isAjax = $request->isAjax();
                return $this->html(['items' => $items, 'q' => $q, 'status' => $status, 'ajax' => $isAjax], 'manage');
            }
            $cpPlace = implode(',', array_fill(0, count($copyIds), '?'));
            $whereParts[] = "book_copy_id IN ($cpPlace)";
            $whereParams = array_merge($whereParams, $copyIds);
        }

        // status filter
        if ($status === 'active') {
            $whereParts[] = 'is_active = ?';
            $whereParams[] = 1;
        } elseif ($status === 'finished') {
            $whereParts[] = 'is_active = ?';
            $whereParams[] = 0;
        }

        $where = null;
        if (!empty($whereParts)) {
            $where = implode(' AND ', $whereParts);
        }

        $reservations = Reservation::getAll($where, $whereParams, 'created_at DESC');
        $items = [];
        foreach ($reservations as $r) {
            $copy = null; $book = null; $u = null;
            try {
                $copy = BookCopy::getOne($r->getBookCopyId());
                if ($copy) $book = Book::getOne($copy->getBookId());
                $u = User::getOne($r->getUserId());
            } catch (\Throwable $e) {
            }
            $items[] = ['reservation' => $r, 'copy' => $copy, 'book' => $book, 'user' => $u];
        }

        $isAjax = $request->isAjax();
        return $this->html(['items' => $items, 'q' => $q, 'status' => $status, 'ajax' => $isAjax], 'manage');
    }

}
