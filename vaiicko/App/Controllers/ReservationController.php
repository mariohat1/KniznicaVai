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
            if (!$auth?->isLogged()) return false;
            $user = $auth->getUser();
            if (is_object($user)) {
                if (method_exists($user, 'getRole')) return strtolower((string)$user->getRole()) === 'admin';
                $vars = get_object_vars($user);
                return isset($vars['role']) && strtolower((string)$vars['role']) === 'admin';
            }
            return false;
        }

        // allow admin to call update (AJAX actions)
        if ($action === 'update') {
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
        $role = $user->getRole();
        $userId = $user->getId();
        if (strtolower((string)$role) === 'admin') {
            return $this->redirect($this->url('book.index'));
        }
        $id = $request->value('id');
        $book = Book::getOne($id);
        if ($book === null) {
            return $this->redirect($this->url('book.index'));
        }

        // find an available copy by checking reservations (controller handles logic)
        $allCopies = BookCopy::getAll('book_id = ?', [$book->getId()]);
        $copy = null;
        foreach ($allCopies as $c) {
            // treat is_reserved as indicator of an active reservation that blocks a copy
            $reservedCount = Reservation::getCount('book_copy_id = ? AND is_reserved = 1', [$c->getId()]);
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
            // mark reservation as reserved
            $reservation->setIsReserved(1);
            $reservation->setUserId($userId);
            $reservation->setBookCopyId($copy->getId());
            $reservation->setCreatedAt(date('Y-m-d H:i'));
            $created = new \DateTime();
            $reservedUntil = (clone $created)->modify('+6 days')->setTime(23, 59);
            $reservation->setReservedUntil($reservedUntil->format('Y-m-d H:i'));
            $reservation->save();
            return $this->redirect($this->url('book.view', ['id' => $book->getId(), 'reserved' => 1]));
        } catch (\Throwable $e) {
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
     * @throws \Exception
     */
    public function manage(Request $request): Response
    {

        $q = trim((string)$request->value('q'));
        $status = $request->value('status');
        $searchBy = $request->value('searchBy');
        $searchBy = is_string($searchBy) ? strtolower($searchBy) : '';
        $page = (int)$request->value('page');
        if ($page < 1) $page = 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $whereParts = [];
        $whereParams = [];

        if ($searchBy !== 'user' && $searchBy !== 'book') {
            $searchBy = ($q !== '') ? 'book' : 'book';
        }

        if ($searchBy === 'user') {
            if ($q !== '') {
                $like = $q . '%';
                $usersFound = User::getAll('username LIKE ?', [$like]);
                $userIds = array_values(array_unique(array_filter(array_map(fn($u) => $u->getId(), $usersFound))));
                if (empty($userIds)) {
                    $items = [];
                    return $this->html(['items' => $items, 'q' => $q, 'status' => $status, 'searchBy' => $searchBy], 'manage');
                }
                $place = implode(',', array_fill(0, count($userIds), '?'));
                $whereParts[] = "user_id IN ($place)";
                $whereParams = array_merge($whereParams, $userIds);
            }
        } else {
            if ($q !== '') {
                $like = $q . '%';
                $books = Book::getAll('title LIKE ?', [$like]);
                $bookIds = array_filter(array_map(fn($b) => $b->getId(), $books));
                if (empty($bookIds)) {
                    $items = [];
                    return $this->html(['items' => $items, 'q' => $q, 'status' => $status, 'searchBy' => $searchBy], 'manage');
                }
                $placeholders = implode(',', array_fill(0, count($bookIds), '?'));
                $copies = BookCopy::getAll("book_id IN ($placeholders)", $bookIds);
                $copyIds = array_filter(array_map(fn($c) => $c->getId(), $copies));
                if (empty($copyIds)) {
                    $items = [];
                    return $this->html(['items' => $items, 'q' => $q, 'status' => $status, 'searchBy' => $searchBy], 'manage');
                }
                $cpPlace = implode(',', array_fill(0, count($copyIds), '?'));
                $whereParts[] = "book_copy_id IN ($cpPlace)";
                $whereParams = array_merge($whereParams, $copyIds);
            }
        }
        if ($status === 'active') {
            $whereParts[] = 'is_reserved = ?';
            $whereParams[] = 1;
        } elseif ($status === 'finished') {
            $whereParts[] = 'is_reserved = ?';
            $whereParams[] = 0;
        }

        $where = null;
        if (!empty($whereParts)) {
            $where = implode(' AND ', $whereParts);
        }

        $total = Reservation::getCount($where, $whereParams);
        $reservations = Reservation::getAll($where, $whereParams, 'created_at DESC', $limit, $offset);
        $items = [];
        foreach ($reservations as $r) {
            $copy = null;
            $book = null;
            $u = null;
            try {
                $copy = BookCopy::getOne($r->getBookCopyId());
                if ($copy) $book = Book::getOne($copy->getBookId());
                $u = User::getOne($r->getUserId());
            } catch (\Throwable $e) {
            }

            // minimal guard: only create DateTimeImmutable when reserved_until exists
            $rawUntil = $r->getReservedUntil();
            $expDate = '';
            $daysLeftStr = '';
            if ($rawUntil) {
                $until = new \DateTimeImmutable($rawUntil);
                $now   = new \DateTimeImmutable();

                $expDate = $until->format('d.m.Y H:i');

                if ($until > $now) {
                    $diff = $now->diff($until);
                    $days = (int)$diff->days;

                    if ($days > 0) {
                        $daysLeftStr = $days . ' ' . ($days === 1 ? 'deň' : 'dni');
                    }
                }
            }

            $items[] = [
                'reservation' => $r,
                'copy' => $copy,
                'book' => $book,
                'user' => $u,
                'expDate' => $expDate,
                'daysLeft' => $daysLeftStr
            ];
        }

        $pages = ($total > 0) ? (int)ceil($total / $limit) : 1;

        $pagination = [
            'page' => $page,
            'pages' => $pages,
            'total' => $total
        ];

        // if AJAX: return JSON data only (client renders HTML)
        if ($request->isAjax()) {
            return $this->json([
                'items' => $items,
                'pagination' => $pagination,
                'q' => $q,
                'status' => $status,
                'searchBy' => $searchBy
            ]);
        }

        return $this->html([
            'items' => $items,
            'q' => $q,
            'status' => $status,
            'searchBy' => $searchBy,
            'pagination' => $pagination
        ], 'manage');
    }

    public function update(Request $request): Response
    {
        if (!$request->isPost()) {
            return $this->redirect($this->url('reservation.manage'));
        }

        $id = $request->value('id');
        $action = $request->value('action');
        $id = $id !== null ? (int)$id : null;
        $action = $action ? (string)$action : null;

        if ($id === null || $action === null) {
            // bad request
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Neplatné údaje.'])->setStatusCode(400);
            }
            return $this->redirect($this->url('reservation.manage'));
        }

        $reservation = Reservation::getOne($id);
        if ($reservation === null) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Rezervácia nenájdená.'])->setStatusCode(404);
            }
            return $this->redirect($this->url('reservation.manage'));
        }

        try {
            if ($action === 'cancel') {
                // cancel reservation: mark as not reserved (is_reserved = 0)
                $reservation->setIsReserved(0);
                $reservation->save();
            } elseif ($action === 'restore') {
                // restore reservation: mark as reserved (is_reserved = 1)
                $reservation->setIsReserved(1);
                $reservation->save();
            } else {
                // unknown action
                if ($request->isAjax()) {
                    return $this->json(['success' => false, 'message' => 'Neznáma akcia.'])->setStatusCode(400);
                }
                return $this->redirect($this->url('reservation.manage'));
            }
        } catch (\Throwable $e) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Server error.'])->setStatusCode(500);
            }
            return $this->redirect($this->url('reservation.manage'));
        }
        if ($request->isAjax()) {
            if ($action === 'cancel') {
                return $this->json(['success' => true, 'id' => $id]);
            }
            try {
                $copy = BookCopy::getOne($reservation->getBookCopyId());
                if ($copy) $book = Book::getOne($copy->getBookId());
                $user = User::getOne($reservation->getUserId());
            } catch (\Throwable $e) {
            }

            return $this->json(['success' => true, 'id' => $reservation->getId()]);
        }

        // Non-AJAX: redirect back to manage
        return $this->redirect($this->url('reservation.manage'));
    }

}
