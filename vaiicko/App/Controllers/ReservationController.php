<?php

namespace App\Controllers;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Reservation;
use App\Support\AuthView;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class ReservationController extends BaseController
{
    // Allow public listing or viewing if needed (not implemented now)
    public function authorize(Request $request, string $action): bool
    {
        // Only logged non-admin users can create reservations
        if ($action === 'create') {
            $auth = $this->app->getAuth();
            if (!$auth?->isLogged()) return false;
            $user = $auth->getUser();
            if (is_object($user)) {
                if (method_exists($user, 'getRole')) return strtolower((string)$user->getRole()) !== 'admin';
                $vars = get_object_vars($user);
                return !isset($vars['role']) || strtolower((string)$vars['role']) !== 'admin';
            }
            return true;
        }

        // default deny for other actions unless overridden
        return false;
    }

    /**
     * Create a reservation (POST). Expects param `id` = book id.
     */
    public function create(Request $request): Response
    {
        $auth = $this->app->getAuth();
        $session = $this->app->getSession();

        if (!$auth->isLogged()) {
            $items = $session->get('flash_messages', []);
            $items[] = ['type' => 'warning', 'message' => 'Pre rezerváciu sa musíš prihlásiť.'];
            $session->set('flash_messages', $items);
            return $this->redirect($this->url('home.index'));
        }

        $user = $auth->getUser();
        // prevent admins
        $role = null;
        if (is_object($user)) {
            if (method_exists($user, 'getRole')) $role = $user->getRole();
            else {
                $vars = get_object_vars($user);
                $role = $vars['role'] ?? null;
            }
        }
        if (strtolower((string)$role) === 'admin') {
            $items = $session->get('flash_messages', []);
            $items[] = ['type' => 'warning', 'message' => 'Administrátori nemôžu rezervovať knihy.'];
            $session->set('flash_messages', $items);
            return $this->redirect($this->url('book.index'));
        }

        $id = $request->value('id');
        $book = Book::getOne($id);
        if ($book === null) {
            $items = $session->get('flash_messages', []);
            $items[] = ['type' => 'danger', 'message' => 'Kniha neexistuje.'];
            $session->set('flash_messages', $items);
            return $this->redirect($this->url('book.index'));
        }

        // find an available copy by checking reservations (controller handles logic)
        $allCopies = BookCopy::getAll('book_id = ?', [$book->getId()]);
        $copy = null;
        foreach ($allCopies as $c) {
            $reservedCount = Reservation::getCount('book_copy_id = ? AND is_active = 1', [$c->getId()]);
            if ($reservedCount === 0) { $copy = $c; break; }
        }
        if ($copy === null) {
            $items = $session->get('flash_messages', []);
            $items[] = ['type' => 'warning', 'message' => 'Žiadne dostupné výtlačky pre túto knihu.'];
            $session->set('flash_messages', $items);
            return $this->redirect($this->url('book.view', ['id' => $book->getId()]));
        }

        try {
            $reservation = new Reservation();
            $reservation->setReserved(date('Y-m-d H:i:s'));
            // determine user id without directly accessing protected properties
            $userId = null;
            if (is_object($user) && method_exists($user, 'getId')) {
                $userId = $user->getId();
            } else {
                $vars = is_object($user) ? get_object_vars($user) : [];
                $userId = $vars['id'] ?? null;
            }
            $reservation->setUserId($userId);
            $reservation->setBookCopyId($copy->getId());
            $reservation->setCreatedAt(date('Y-m-d H:i:s'));
            $reservation->save();

            $items = $session->get('flash_messages', []);
            $items[] = ['type' => 'success', 'message' => 'Kniha bola úspešne rezervovaná.'];
            $session->set('flash_messages', $items);

            return $this->redirect($this->url('book.view', ['id' => $book->getId()]));
        } catch (\Throwable $e) {
            $items = $session->get('flash_messages', []);
            $items[] = ['type' => 'danger', 'message' => 'Rezervácia zlyhala: ' . $e->getMessage()];
            $session->set('flash_messages', $items);
            return $this->redirect($this->url('book.view', ['id' => $book->getId()]));
        }
    }

    public function index(Request $request): Response
    {
        // Simple redirect to book index or a listing of reservations later.
        return $this->redirect($this->url('book.index'));
    }
}
