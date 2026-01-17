<?php

namespace App\Controllers;

use App\Support\AuthHelper;
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
    use AuthHelper;


    public function authorize(Request $request, string $action): bool
    {
        if (in_array($action, ['manage', 'update'])) {
            return $this->isAdmin();
        }
        if (in_array($action, ['index', 'cancel'])) {
            return $this->app->getAuth()->isLogged();
        }
        if ($action === 'create') {
            return $this->isUser();
        }
        return false;
    }


    /**
     * Create a reservation (POST). Expects param `id` = book id.
     */
    public function create(Request $request): Response
    {
        if (!$request->isPost()) {
            return $this->redirect($this->url('book.index'));
        }
        $id = $request->value('id');
        $book = Book::getOne($id);
        if ($book === null) {
            return $this->redirect($this->url('book.index'));
        }
        $userId = $this->app->getAuth()->getUser()->getId();
        $copy = $this->findAvailableCopy($book);
        if ($copy === null) {
            return $this->redirect($this->url('book.view', ['id' => $book->getId()]));
        }

        try {
            $reservation = new Reservation();
            $reservation->setIsReserved(1);
            $reservation->setUserId($userId);
            $reservation->setBookCopyId($copy->getId());
            $now = new \DateTimeImmutable();
            $reservation->setCreatedAt($now->format('Y-m-d H:i'));
            $reservedUntil = $now->modify('+6 days')->setTime(23, 59);
            $reservation->setReservedUntil($reservedUntil->format('Y-m-d H:i'));
            $reservation->save();
            return $this->redirect($this->url('book.view', ['id' => $book->getId(), 'reserved' => 1]));
        } catch (\Throwable $e) {
            return $this->redirect($this->url('book.view', ['id' => $book->getId()]));
        }
    }

    public function index(Request $request): Response
    {
        $userId = $this->app->getAuth()->getUser()->getId();
        $status = $request->value('status');
        $whereParts = ['user_id = ?'];
        $whereParams = [$userId];
        if ($status === 'active') {
            $whereParts[] = 'is_reserved = ?';
            $whereParams[] = 1;
        } elseif ($status === 'finished') {
            $whereParts[] = 'is_reserved = ?';
            $whereParams[] = 0;
        }
        $where = !empty($whereParts) ? implode(' AND ', $whereParts) : null;
        $page = (int)$request->value('page');
        if ($page < 1) $page = 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        $total = Reservation::getCount($where, $whereParams);
        $reservations = Reservation::getAll($where, $whereParams, 'created_at DESC', $perPage, $offset);
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

        $pages = ($total > 0) ? (int)ceil($total / $perPage) : 1;
        $pagination = ['page' => $page, 'pages' => $pages, 'total' => $total, 'limit' => $perPage];

        if ($request->isAjax() || $request->wantsJson()) {
            return $this->json(['items' => $items, 'status' => $status ?? 'all', 'pagination' => $pagination]);
        }

        return $this->html(['items' => $items, 'status' => $status ?? 'all', 'pagination' => $pagination], 'index');
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
        $this->applySearchFilters($q, $searchBy, $whereParts, $whereParams);
        $data = $this->getReservationsData($whereParts, $whereParams, $status, $page, $limit, $offset);
        $items = $data['items'];
        $pagination = $data['pagination'];


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
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Neplatné údaje.']);
            }
            return $this->redirect($this->url('reservation.manage'));
        }

        $reservation = Reservation::getOne($id);
        if ($reservation === null) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Rezervácia nenájdená.']);
            }
        }

        try {
            if ($action === 'cancel') {
                $reservation->setIsReserved(0);
                $reservation->save();
            } elseif ($action === 'restore') {
                $reservation->setIsReserved(1);
                $reservation->save();
            }
        } catch (\Throwable $e) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => 'Server error.']);
            }
        }
        if ($request->isAjax()) {
            if ($action === 'cancel') {
                return $this->json(['success' => true, 'id' => $id]);
            }

            return $this->json(['success' => true, 'id' => $reservation->getId()]);
        }
        return $this->redirect($this->url('reservation.manage'));
    }

    /**
     * Umožní užívateľovi zrušiť iba svoju vlastnú rezerváciu (POST, id v parametri)
     * Minimal: predpokladá, že $this->app->getAuth()->getUser()->getId() existuje.
     */
    public function cancel(Request $request): Response
    {
        $user = $this->app->getAuth()->getUser();
        $userId = $user->getId();

        $reservationId = (int)$request->value('id');
        if ($reservationId <= 0) {
            return $this->redirect($this->url('reservation.index'));
        }

        $reservation = Reservation::getOne($reservationId);
        if (!$reservation) {
           return $this->redirect($this->url('reservation.index'));
        }

        if ((int)$reservation->getUserId() !== (int)$userId) {
           return $this->redirect($this->url('reservation.index'));
        }

        try {
            $reservation->setIsReserved(0);
            $reservation->save();
            return $this->redirect($this->url('reservation.index'));
        } catch (\Throwable $e) {
            return $this->redirect($this->url('reservation.index'));
        }
    }

    /**
     * Build and append WHERE parts and parameters for search filters.
     * Keeps code centralized and avoids inline SQL construction.
     *
     * @param string $q
     * @param string $searchBy
     * @param array  $whereParts (by ref)
     * @param array  $whereParams (by ref)
     */
    private function applySearchFilters(string $q, string $searchBy, array & $whereParts, array & $whereParams): void
    {
        if ($q === '') return;

        if ($searchBy === 'user') {
            $like = $q . '%';
            $usersFound = User::getAll('username LIKE ?', [$like]);
            $userIds = array_values(array_unique(array_filter(array_map(fn($u) => $u->getId(), $usersFound))));
            if (empty($userIds)) {
                $whereParts[] = '1 = 0';
            } else {
                $place = implode(',', array_fill(0, count($userIds), '?'));
                $whereParts[] = "user_id IN ($place)";
                $whereParams = array_merge($whereParams, $userIds);
            }
            return;
        }

        $like = $q . '%';
        $books = Book::getAll('title LIKE ?', [$like]);
        $bookIds = array_values(array_filter(array_map(fn($b) => $b->getId(), $books)));
        if (empty($bookIds)) {
            $whereParts[] = '1 = 0';
            return;
        }

        $placeholders = implode(',', array_fill(0, count($bookIds), '?'));
        $copies = BookCopy::getAll("book_id IN ($placeholders)", $bookIds);
        $copyIds = array_values(array_filter(array_map(fn($c) => $c->getId(), $copies)));
        if (empty($copyIds)) {
            $whereParts[] = '1 = 0';
            return;
        }

        $cpPlace = implode(',', array_fill(0, count($copyIds), '?'));
        $whereParts[] = "book_copy_id IN ($cpPlace)";
        $whereParams = array_merge($whereParams, $copyIds);
    }

    /**
     * Query reservations and build items + pagination using precomputed where parts and params.
     * @param array $whereParts
     * @param array $whereParams
     * @param string|null $status
     * @param int $page
     * @param int $limit
     * @param int $offset
     * @return array{items: array, pagination: array}
     */
    private function getReservationsData(array $whereParts, array $whereParams, ?string $status, int $page, int $limit, int $offset): array
    {

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

        return ['items' => $items, 'pagination' => $pagination];
    }

    // helper to find a copy that's available and not already reserved
    private function findAvailableCopy(Book $book): ?BookCopy
    {
        $allCopies = BookCopy::getAll('book_id = ? AND available = 1', [$book->getId()]);
        foreach ($allCopies as $c) {
            $reservedCount = Reservation::getCount('book_copy_id = ? AND is_reserved = 1', [$c->getId()]);
            if ($reservedCount === 0) {
                return $c;
            }
        }
        return null;
    }

}
