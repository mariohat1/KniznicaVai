<?php

namespace App\Controllers;

use App\Models\Book;
use App\Models\Author;
use App\Models\Category;
use App\Models\Genre;
use App\Support\AuthView;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Responses\JsonResponse;

class BookController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        // Only admin users can perform non-read actions (add/store). Allow index for everyone.
        // Allow public read actions: index and view
        if (in_array($action, ['index', 'view'])) {
            return true;
        }

        $auth = $this->app->getAuth();
        if (!$auth->isLogged()) {
            return false;
        }

        $user = $auth->getUser();
        // Require explicit role === 'admin' (case-insensitive). No username fallback.
        if (is_object($user)) {
            if (method_exists($user, 'getRole')) {
                return (strtolower((string)$user->getRole()) === 'admin');
            }
            // If role is a public/protected property (no getter), inspect it defensively
            $vars = is_object($user) ? get_object_vars($user) : [];
            if (isset($vars['role'])) {
                return (strtolower((string)$vars['role']) === 'admin');
            }
        }

        return false;
    }

    public function index(Request $request): Response
    {
        $books = Book::getAll();
        // compute available copies per book using model counts (controller handles logic)
        $copies = [];
        foreach ($books as $b) {
            $bookId = $b->getId();
            $total = \App\Models\BookCopy::getCount('book_id = ?', [$bookId]);
            $reserved = \App\Models\Reservation::getCount('book_copy_id IN (SELECT id FROM book_copy WHERE book_id = ?) AND is_active = 1', [$bookId]);
            $available = max(0, $total - $reserved);
            $copies[$bookId] = ['total' => $total, 'available' => $available];
        }
        return $this->html(['books' => $books, 'copies' => $copies], 'index');
    }

    // Admin-only management page (classic list). Protected via authorize() since action != 'index'.
    public function manage(Request $request): Response
    {
        $books = Book::getAll();
        // compute available copies per book using model counts (controller handles logic)
        $copies = [];
        foreach ($books as $b) {
            $bookId = $b->getId();
            $total = \App\Models\BookCopy::getCount('book_id = ?', [$bookId]);
            $reserved = \App\Models\Reservation::getCount('book_copy_id IN (SELECT id FROM book_copy WHERE book_id = ?) AND is_active = 1', [$bookId]);
            $available = max(0, $total - $reserved);
            $copies[$bookId] = ['total' => $total, 'available' => $available];
        }
        // Render the existing classic admin table view (Book/index.view.php)
        return $this->html(['books' => $books, 'copies' => $copies], 'manage');
    }

    /**
     * Display a single book detail view.
     */
    public function view(Request $request): Response
    {
        $id = $request->value('id');
        $book = Book::getOne($id);

        if ($book === null) {
            // not found -> flash + redirect
            $session = $this->app->getSession();
            $items = $session->get('flash_messages', []);
            $items[] = ['type' => 'warning', 'message' => 'Kniha nebola nájdená.'];
            $session->set('flash_messages', $items);
            return $this->redirect($this->url('book.index'));
        }

        $author = null;
        $category = null;
        $genre = null;
        try {
            if ($book->getAuthorId()) $author = Author::getOne($book->getAuthorId());
            if ($book->getCategoryId()) $category = Category::getOne($book->getCategoryId());
            if ($book->getGenreId()) $genre = Genre::getOne($book->getGenreId());
        } catch (\Throwable $e) {
            // ignore related load errors
        }

        return $this->html(['book' => $book, 'author' => $author, 'category' => $category, 'genre' => $genre], 'bookView');
    }

    public function add(Request $request): Response
    {
        // Load lists needed by the add view so it can render selects/checkboxes
        $authors = Author::getAll();
        $categories = Category::getAll();
        $genres = Genre::getAll();

        return $this->html(['authors' => $authors, 'categories' => $categories, 'genres' => $genres]);
    }

    public function store(Request $request): Response
    {
        // Only accept POST
        if (!$request->isPost()) {
            if ($request->isAjax()) {
                return (new JsonResponse(['success' => false, 'message' => 'Method not allowed']))->setStatusCode(405);
            }
            return $this->redirect($this->url('book.add'));
        }

        try {
            $book = new Book();

            // If the request contains JSON (AJAX), populate the model from JSON body so setFromRequest
            // (which reads $_POST) doesn't miss values.
            if ($request->isJson()) {
                try {
                    $data = $request->json();
                } catch (\JsonException $e) {
                    $data = null;
                }

                if (is_array($data) || is_object($data)) {
                    foreach ((array)$data as $k => $v) {
                        // Build setter name from key (e.g. year_published -> setYearPublished)
                        $setter = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $k)));
                        if (method_exists($book, $setter)) {
                            $book->{$setter}($v);
                        } elseif (property_exists($book, $k)) {
                            $book->{$k} = $v;
                        }
                    }
                }
            } else {
                // regular form POST: existing helper
                $book->setFromRequest($request);
            }

            $book->save();

            // If AJAX request, return JSON with created id
            if ($request->isAjax()) {
                return (new JsonResponse(['success' => true, 'id' => $book->getId(), 'message' => 'Book saved']))->setStatusCode(201);
            }

            // non-AJAX: set a flash and redirect to index
            $session = $this->app->getSession();
            $items = $session->get('flash_messages', []);
            $items[] = ['type' => 'success', 'message' => 'Book saved'];
            $session->set('flash_messages', $items);

            return $this->redirect($this->url('book.index'));
        } catch (\Throwable $e) {
            if ($request->isAjax()) {
                return (new JsonResponse(['success' => false, 'message' => 'Save failed: ' . $e->getMessage()]))->setStatusCode(500);
            }
            $session = $this->app->getSession();
            $items = $session->get('flash_messages', []);
            $items[] = ['type' => 'danger', 'message' => 'Save failed: ' . $e->getMessage()];
            $session->set('flash_messages', $items);
            return $this->redirect($this->url('book.add'));
        }
    }
}
