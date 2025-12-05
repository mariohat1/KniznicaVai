<?php

namespace App\Controllers;

use App\Models\Book;
use App\Models\Author;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Reservation;
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

    /**
     * @throws \Exception
     */
    public function index(Request $request): Response
    {
        $books = Book::getAll();
        $copies = [];
        foreach ($books as $b) {
            $bookId = $b->getId();
            $total = BookCopy::getCount('book_id = ?', [$bookId]);
            $reserved = Reservation::getCount('book_copy_id IN (SELECT id FROM book_copy WHERE book_id = ?) AND is_reserved = 1', [$bookId]);
            $available = max(0, $total - $reserved);
            $copies[$bookId] = ['total' => $total, 'available' => $available];
        }
        return $this->html(['books' => $books, 'copies' => $copies], 'index');
    }


    /**
     * @throws \Exception
     */
    public function manage(Request $request): Response
    {
        $books = Book::getAll();
        $copies = [];
        foreach ($books as $b) {
            $bookId = $b->getId();
            $total = BookCopy::getCount('book_id = ?', [$bookId]);
            $reserved = Reservation::getCount('book_copy_id IN (SELECT id FROM book_copy WHERE book_id = ?) AND is_reserved = 1', [$bookId]);
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
        }
        $reserved = $request->value('reserved') !== null ? (int)$request->value('reserved') : null;
        return $this->html([
            'book' => $book,
            'author' => $author,
            'category' => $category,
            'genre' => $genre,
            'reserved' => $reserved,
        ], 'bookView');
    }

    /**
     * @throws \Exception
     */
    public function add(Request $request): Response
    {
        // If an id is provided, load the book so the add view can be reused for editing
        $id = $request->value('id');
        $book = null;
        if ($id !== null && trim((string)$id) !== '') {
            $book = Book::getOne($id);
            if ($book === null) {
                // If the requested book does not exist, redirect back to manage
                return $this->redirect($this->url('book.manage'));
            }
        }

        $authors = Author::getAll();
        $categories = Category::getAll();
        $genres = Genre::getAll();
        return $this->html(['book' => $book, 'authors' => $authors, 'categories' => $categories, 'genres' => $genres]);
    }

    /**
     * @throws \Exception
     */


    public function store(Request $request): Response
    {
        if (!$request->isPost()) {
            if ($request->isAjax()) {
                return (new JsonResponse(['success' => false, 'message' => 'Method not allowed']))->setStatusCode(405);
            }
            return $this->redirect($this->url('book.add'));
        }

        try {
            // Load existing book (for edit) or create new one
            $book = $this->loadOrCreateBook($request);

            $this->setBookDataFromRequest($request, $book);

            // Apply photo path if provided
            $this->applyPhotoPath($request, $book);

            // Validate ISBN format and uniqueness. If invalid, this returns a Response we must send.
            $isbnValidationResponse = $this->validateIsbnAndUniqueness($book, $request);
            if ($isbnValidationResponse instanceof Response) {
                return $isbnValidationResponse;
            }

            // Save and respond
            $book->save();

            if ($request->isAjax()) {
                return new JsonResponse(['success' => true, 'redirect' => $this->url('book.manage')]);
            }

            return $this->redirect($this->url('book.manage'));
        } catch (\Throwable $e) {
            if ($request->isAjax()) {
                return (new JsonResponse(['success' => false, 'message' => 'Save failed: ' . $e->getMessage()]))->setStatusCode(500);
            }
            return $this->redirect($this->url('book.add'));
        }
    }

    /**
     * Load an existing Book if id provided in request, otherwise return a new Book instance.
     */
    private function loadOrCreateBook(Request $request): Book
    {
        $id = $request->value('id');
        $book = null;
        if ($id !== null && trim((string)$id) !== '') {
            $loaded = Book::getOne($id);
            if ($loaded !== null) {
                $book = $loaded;
            }
        }
        if ($book === null) {
            $book = new Book();
        }

        return $book;
    }

    /**
     * Set book properties from request body (JSON) or form POST.
     */
    private function setBookDataFromRequest(Request $request, Book $book): void
    {
        if ($request->isJson()) {
            try {
                $data = $request->json();
            } catch (\JsonException $e) {
                $data = null;
            }

            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    // Set only known properties on the Book instance
                    if (!property_exists($book, $key)) {
                        continue;
                    }
                    $book->{$key} = $value;
                }
            }
        } else {
            $book->setFromRequest($request);
        }
    }

    /**
     * Apply photo_path value from request to the Book if available.
     */
    private function applyPhotoPath(Request $request, Book $book): void
    {
        $photoPath = $request->value('photo_path');
        if (method_exists($book, 'setPhoto') && $photoPath !== null) {
            if (trim((string)$photoPath) !== '') {
                $book->setPhoto($photoPath);
            } else {
                $book->setPhoto(null);
            }
        }
    }

    /**
     * Validate ISBN format and uniqueness. Returns null when OK or a Response instance to return immediately.
     */
    private function validateIsbnAndUniqueness(Book $book, Request $request): ?Response
    {
        $isbn = $book->getIsbn();
        if (empty($isbn)) {
            return null;
        }

        $normalized = preg_replace('/[^0-9Xx]/', '', (string)$isbn);

        $isValidIsbn = false;
        if (strlen($normalized) === 13 && ctype_digit($normalized)) {
            $sum = 0;
            for ($i = 0; $i < 13; $i++) {
                $digit = (int)$normalized[$i];
                $sum += ($i % 2 === 0) ? $digit : $digit * 3;
            }
            $isValidIsbn = ($sum % 10) === 0;
        } elseif (strlen($normalized) === 10) {
            $sum = 0;
            for ($i = 0; $i < 9; $i++) {
                if (!isset($normalized[$i]) || !ctype_digit($normalized[$i])) { $sum = null; break; }
                $sum += (10 - $i) * (int)$normalized[$i];
            }
            if ($sum !== null) {
                $check = $normalized[9];
                $checkVal = ($check === 'X' || $check === 'x') ? 10 : (ctype_digit($check) ? (int)$check : -1);
                if ($checkVal >= 0) {
                    $sum += 1 * $checkVal;
                    $isValidIsbn = ($sum % 11) === 0;
                }
            }
        }

        if (!$isValidIsbn) {
            if ($request->isAjax()) {
                return (new JsonResponse(['success' => false, 'message' => 'Neplatné ISBN']))->setStatusCode(400);
            }
            return $this->redirect($this->url('book.add'));
        }

        // Check uniqueness (ignore case and spaces). Exclude current book when editing.
        $normalizedIsbn = trim(strtolower($isbn));
        $where = 'TRIM(LOWER(isbn)) = ?';
        $params = [$normalizedIsbn];
        if ($book->getId() !== null) {
            $where .= ' AND `id` <> ?';
            $params[] = $book->getId();
        }
        $conflictCount = Book::getCount($where, $params);
        if ($conflictCount > 0) {
            if ($request->isAjax()) {
                return (new JsonResponse(['success' => false, 'message' => 'ISBN musí být jedinečné']))->setStatusCode(400);
            }
            return $this->redirect($this->url('book.add'));
        }

        return null;
    }

    /**
     * AJAX endpoint: upload a PNG photo for a book.
     */
    public function uploadPhoto(Request $request): Response
    {
        if (!$request->isPost()) {
            return $this->json(['success' => false, 'message' => 'Method not allowed'])->setStatusCode(405);
        }
        $file = $request->file('photo');
        if (!$file || !$file->isOk()) {
            $msg = $file ? $file->getErrorMessage() : 'No file uploaded';
            return $this->json(['success' => false, 'message' => 'No file uploaded or upload error', 'detail' => $msg])->setStatusCode(400);
        }
        $maxBytes = 5 * 1024 * 1024;
        if ($file->getSize() > $maxBytes) {
            return $this->json(['success' => false, 'message' => 'File too large'])->setStatusCode(400);
        }
        $tmp = $file->getFileTempPath();
        $info = @getimagesize($tmp);
        if (!is_array($info) || empty($info['mime'])) {
            return $this->json(['success' => false, 'message' => 'Invalid image'])->setStatusCode(400);
        }
        $mime = $info['mime'];
        if ($mime !== 'image/png') {
            return $this->json(['success' => false, 'message' => 'Only PNG images are allowed', 'detected' => $mime])->setStatusCode(400);
        }

        $projectRoot = dirname(__DIR__, 2);
        $uploadDir = $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'book';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                return $this->json(['success' => false, 'message' => 'Unable to create upload directory: ' . $uploadDir])->setStatusCode(500);
            }
        }
        $filename = uniqid('book_', true) . '.png';
        $dest = $uploadDir . DIRECTORY_SEPARATOR . $filename;
        if (!$file->store($dest)) {
            return $this->json(['success' => false, 'message' => 'Failed to save uploaded file'])->setStatusCode(500);
        }
        $relative = '/uploads/book/' . $filename;
        return $this->json(['success' => true, 'path' => $relative, 'filename' => $filename, 'original' => $file->getName()]);
    }

    public function delete(Request $request): Response
    {
        if (!$request->isPost()) {
            if ($request->isAjax()) {
                return (new JsonResponse(['success' => false, 'message' => 'Method not allowed']))->setStatusCode(405);
            }
            return $this->redirect($this->url('book.manage'));
        }

        $id = $request->value('id');
        if ($id === null) {
            if ($request->isAjax()) {
                return (new JsonResponse(['success' => false, 'message' => 'Missing id']))->setStatusCode(400);
            }
            return $this->redirect($this->url('book.manage'));
        }

        $book = Book::getOne($id);
        if ($book === null) {
            if ($request->isAjax()) {
                return (new JsonResponse(['success' => false, 'message' => 'Book not found']))->setStatusCode(404);
            }
            return $this->redirect($this->url('book.manage'));
        }

        try {
            // Delete the book. DB foreign keys (ON DELETE CASCADE) will remove book_copy rows.
            $book->delete();

            if ($request->isAjax()) {
                return new JsonResponse(['success' => true, 'message' => 'Deleted']);
            }
            return $this->redirect($this->url('book.manage'));
        } catch (\Throwable $e) {
            if ($request->isAjax()) {
                return (new JsonResponse(['success' => false, 'message' => 'Delete failed: ' . $e->getMessage()]))->setStatusCode(500);
            }
            // On failure, go back to manage with a generic message (could be improved to show flash messages)
            return $this->redirect($this->url('book.manage'));
        }
    }
}
