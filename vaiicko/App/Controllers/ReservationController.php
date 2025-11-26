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
            $reservation->setCreatedAt(date('Y-m-d H:i:s'));
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
        if ($status === 'active') {
            // 'active' means currently reserved
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

        $reservations = Reservation::getAll($where, $whereParams, 'created_at DESC');
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
            $items[] = ['reservation' => $r, 'copy' => $copy, 'book' => $book, 'user' => $u];
        }

        $isAjax = $request->isAjax();
        return $this->html(['items' => $items, 'q' => $q, 'status' => $status, 'ajax' => $isAjax], 'manage');
    }

    public function update(Request $request): Response
    {

        // accept POST only
        if (!$request->isPost()) {
            return $this->redirect($this->url('reservation.manage'));
        }

        // read id/action directly from request (covers POST form-encoded and GET params)
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

        // For AJAX: return JSON with success and optionally updated row HTML (or indicate deletion)
        if ($request->isAjax()) {
            // If cancelled (deleted) return success and id so client can remove row
            if ($action === 'cancel') {
                return $this->json(['success' => true, 'id' => $id]);
            }

            // otherwise reload reservation and related models and render row HTML fragment
            $copy = null;
            $book = null;
            $user = null;
            try {
                $copy = BookCopy::getOne($reservation->getBookCopyId());
                if ($copy) $book = Book::getOne($copy->getBookId());
                $user = User::getOne($reservation->getUserId());
            } catch (\Throwable $e) {
            }

            // build a minimal row fragment that uses is_reserved status
            $safeTitle = $book ? htmlspecialchars($book->getTitle(), ENT_QUOTES, 'UTF-8') : '';
            ob_start();
            ?>
            <div class="list-group-item d-flex justify-content-between align-items-start reservation-item" data-title="<?= $safeTitle ?>" data-reservation-id="<?= htmlspecialchars((string)$reservation->getId()) ?>">
                <div>
                    <div class="fw-bold"><?php echo $book ? htmlspecialchars($book->getTitle()) : 'Neznáma kniha'; ?></div>
                    <div class="small text-muted">
                        Kópia: <?= $copy ? htmlspecialchars((string)$copy->getId()) : '—' ?>
                        Používateľ: <?= $user ? htmlspecialchars($user->getUsername() ?? $user->getId()) : '—' ?>
                        Rezervované: <?= $reservation->getIsReserved() ? 'Áno' : 'Nie' ?>
                    </div>
                </div>
                <div class="text-end">
                    <?php if ($reservation->getIsReserved()): ?>
                        <button class="btn btn-sm btn-warning me-1 reservation-action" data-action="cancel" data-id="<?= htmlspecialchars((string)$reservation->getId()) ?>">Zrušiť rezerváciu</button>
                    <?php else: ?>
                        <button class="btn btn-sm btn-success me-1 reservation-action" data-action="restore" data-id="<?= htmlspecialchars((string)$reservation->getId()) ?>">Obnoviť rezerváciu</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php
            $html = ob_get_clean();
            return $this->json(['success' => true, 'id' => $reservation->getId(), 'rowHtml' => $html]);
        }

        // Non-AJAX: redirect back to manage
        return $this->redirect($this->url('reservation.manage'));
    }

}
