<?php

namespace App\Controllers;

use App\Models\Book;
use App\Models\Author;
use App\Models\Category;
use App\Models\Genre;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Responses\JsonResponse;

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
        return $this->html(['books' => $books]);
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
