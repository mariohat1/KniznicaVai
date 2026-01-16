<?php

namespace App\Controllers;

use App\Models\Book;
use App\Models\Author;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Reservation;
use App\Support\AuthView;
use App\Support\Validator;
use App\Support\PhotoUpload;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Responses\JsonResponse;
use mysql_xdevapi\Exception;

class BookController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        // Allow public read actions: index and view
        if (in_array($action, ['index', 'view'])) {
            return true;
        }

        $auth = $this->app->getAuth();
        if (!$auth || !$auth->isLogged()) {
            return false;
        }

        $user = $auth->getUser();
        return $user && strtolower((string)$user->getRole()) === 'admin';
    }

    /**
     * @throws \Exception
     */
    public function index(Request $request): Response
    {
        $q = trim((string)$request->value('q'));
        $filterBy = $request->value('filter') ?? 'title';
        $categoryFilter = $request->value('category') ?? '';
        $genreFilter = $request->value('genre') ?? '';
        $whereParts = [];
        $whereParams = [];
        if ($q !== '') {
            if ($filterBy === 'author') {
                $like = '%' . $q . '%';
                $matchingAuthors = Author::getAll("CONCAT(first_name, ' ', last_name) LIKE ?", [$like]);
                $ids = array_map(fn($author) => $author->getId(), $matchingAuthors);
                if (!empty($ids)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $whereParts[] = "author_id IN ($placeholders)";
                    $whereParams = array_merge($whereParams, $ids);
                } else {
                    $whereParts[] = '1 = 0';
                }
            } else {
                $whereParts[] = 'title LIKE ?';
                $whereParams[] = '%' . $q . '%';
            }
        }

        if ($categoryFilter !== null && trim((string)$categoryFilter) !== '') {
            $whereParts[] = 'category_id = ?';
            $whereParams[] = (int)$categoryFilter;
        }

        // Filter by genre
        if ($genreFilter !== null && trim((string)$genreFilter) !== '') {
            $whereParts[] = 'genre_id = ?';
            $whereParams[] = (int)$genreFilter;
        }
        $results = $this->fetchBooksForListing($request, $whereParts, $whereParams);
        $books = $results['books'];
        $copies = $results['copies'];
        $categoriesMap = $results['categories'];
        $genresMap = $results['genres'];
        $page = $results['pagination']['page'];
        $pages = $results['pagination']['pages'];
        $perPage = $results['pagination']['perPage'];
        $totalCount = $results['pagination']['total'];

        $bookMeta = [];
        foreach ($books as $book) {
            $id = $book->getId();
            $authorName = '';
            $categoryName = $categoriesMap[$book->getCategoryId()] ?? '';
            $genreName = $genresMap[$book->getGenreId()] ?? '';
            $author = $book->getOneRelated(Author::class, 'author_id');
            if ($author !== null) {
                $authorName = trim(($author->getFirstName() ?? '') . ' ' . ($author->getLastName() ?? ''));
            }

            $bookMeta[$id] = [
                'author' => $authorName,
                'category' => $categoryName,
                'genre' => $genreName,
            ];
        }


        return $this->html(['books' => $books,
            'copies' => $copies,
            'categories' => $categoriesMap,
            'genres' => $genresMap,
            'bookMeta' => $bookMeta,
            'filters' => ['q' => $q, 'filter' => $filterBy, 'category' => $categoryFilter, 'genre' => $genreFilter, 'page' => $page],
            'pagination' => ['page' => $page, 'pages' => $pages, 'perPage' => $perPage, 'total' => $totalCount],], 'index');
    }


    /**
     * @throws \Exception
     */
    public
    function manage(Request $request): Response
    {
        $q = trim((string)$request->value('q'));
        $categoryFilter = $request->value('category') ?? '';
        $genreFilter = $request->value('genre') ?? '';

        $whereParts = [];
        $whereParams = [];

        if ($q !== '') {
            $whereParts[] = 'title LIKE ?';
            $whereParams[] = '%' . $q . '%';
        }

        if ($categoryFilter !== null && trim((string)$categoryFilter) !== '') {
            $whereParts[] = 'category_id = ?';
            $whereParams[] = (int)$categoryFilter;
        }

        if ($genreFilter !== null && trim((string)$genreFilter) !== '') {
            $whereParts[] = 'genre_id = ?';
            $whereParams[] = (int)$genreFilter;
        }

        $results = $this->fetchBooksForListing($request, $whereParts, $whereParams);
        $books = $results['books'];
        $copies = $results['copies'];
        $categoriesMap = $results['categories'];
        $genresMap = $results['genres'];
        $page = $results['pagination']['page'];
        $pages = $results['pagination']['pages'];
        $perPage = $results['pagination']['perPage'];
        $total = $results['pagination']['total'];

        return $this->html([
            'books' => $books,
            'copies' => $copies,
            'categories' => $categoriesMap,
            'genres' => $genresMap,
            'filters' => ['q' => $q, 'category' => $categoryFilter, 'genre' => $genreFilter, 'page' => $page],
            'pagination' => ['page' => $page, 'pages' => $pages, 'perPage' => $perPage, 'total' => $total],
        ], 'manage');
    }

    /**
     * Display a single book detail view.
     */
    public
    function view(Request $request): Response
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
        // reservedSuccess: indicates a recent successful reservation (request param).
        $reservedSuccess = $request->value('reserved') !== null ? (int)$request->value('reserved') : null;

        // compute copies availability using centralized helper (same logic as listing)
        $bookId = $book->getId();
        $summary = $this->computeCopiesForBook($bookId);
        $total = $summary['total'];
        $available = $summary['available'];
        $reservedCount = $summary['reserved'];
        $copiesList = $summary['copies'];

        return $this->html([
            'book' => $book,
            'author' => $author,
            'category' => $category,
            'genre' => $genre,
            // flag showing a recent successful reservation (used for alert)
            'reservedSuccess' => $reservedSuccess,
            // numeric counts the UI needs: available (not reserved) and reserved (count)
            'available' => $available,
            'reserved' => $reservedCount,
            'total' => $summary['total'],
            'copies' => $copiesList,
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
                return $this->json(['success' => false, 'message' => 'Method not allowed']);
            }
        }
        $errors = [];
        $isbn = $request->value('isbn');
        $year = $request->value('year');
        $description = $request->value('description');
        $authorId = (int)trim(($request->value('author_id') ?? ''));
        $categoryId = (int)trim(($request->value('category_id') ?? ''));
        $genreId = (int)trim(($request->value('genre_id') ?? ''));

        if ($err = Validator::validateAuthorId($authorId)) {
            $errors[] = $err;
        }
        if ($err = Validator::validateCategoryId($categoryId)) {
            $errors[] = $err;
        }
        if ($err = Validator::validateGenreId($genreId)) {
            $errors[] = $err;
        }
        if ($err = Validator::validateIsbn($isbn)) {
            $errors[] = $err;
        }
        if ($err = Validator::validateYear($year, 'year_published')) {
            $errors[] = $err;
        }
        if ($err = Validator::validateDescription($description)) {
            $errors[] = $err;
        }

        if (!empty($errors)) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'errors' => $errors]);
            }
            return $this->redirect($this->url('book.add'));
        }

        $book = $this->loadOrCreateBook($request);
        $book->setIsbn(trim((string)$isbn));
        if ($err = $this->checkISBNUniqueness($book)) {
            $errors[] = $err;
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'message' => $err]);
            }
            return $this->redirect($this->url('book.add'));
        }
        $book->setFromRequest($request);

        // Handle photo upload
        if ($photoError = $this->handlePhotoUpload($request, $book)) {
            $errors[] = $photoError;
        }

        if (!empty($errors)) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'errors' => $errors]);
            }
            return $this->redirect($this->url('book.add'));
        }

        $book->save();

        if ($request->isAjax()) {
            return $this->json(['success' => true, 'redirect' => $this->url('book.manage')]);
        }

        return $this->redirect($this->url('book.manage'));
    }

    /**
     * Load an existing Book if id provided in request, otherwise return a new Book instance.
     */
    private function loadOrCreateBook(Request $request): Book
    {
        $id = $request->value('id');
        $book = null;
        if ($id !== null) {
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
     * Handle photo upload for book
     * @return string|null Error message or null on success
     */
    private function handlePhotoUpload(Request $request, Book $book): ?string
    {
        $photoFile = $request->file('photo');
        if (!$photoFile || !$photoFile->isOk()) {
            return null; // No file uploaded, that's OK
        }

        $path = PhotoUpload::handle($request, 'book', 'book');
        if ($path !== null) {
            $book->setPhoto($path);
            return null;
        }

        $err = PhotoUpload::lastError();
        return 'Photo upload failed: ' . ($err ?? 'Unknown error');
    }

    /**
     * Check ISBN uniqueness. Returns an error message string when not unique, or null when OK.
     */
    private function checkISBNUniqueness(Book $book): ?string
    {
        $isbn = $book->getIsbn();
        $where = 'isbn = ?';
        $params = [$isbn];
        if ($book->getId() !== null) {
            $where .= ' AND `id` <> ?';
            $params[] = $book->getId();
        }
        $conflictCount = Book::getCount($where, $params);
        if ($conflictCount > 0) {
            return 'ISBN musí byť jedinečné';
        }
        return null;
    }


    public function delete(Request $request): Response
    {
        $id = $request->value('id');
        if ($id === null) {
            return $this->redirect($this->url('book.manage'));
        }
        try {
            $book = Book::getOne($id);
        } catch (Exception $e) {
            $book = null;
        }
        if ($book === null) {
            return $this->redirect($this->url('book.manage'));
        }

        try {
            $photo = $book->getPhoto();
            if (!empty($photo)) {
                $projectRoot = dirname(__DIR__, 2);
                $photoPath = $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . ltrim($photo, '/\\');
                if (is_file($photoPath)) {
                    unlink($photoPath);
                }
            }
        } catch (\Throwable $ignored) {
            return $this->redirect($this->url('book.manage'));

        }

        try {
            $book->delete();
        } catch (\Throwable $e) {
            return $this->redirect($this->url('book.manage'));

        }
        return $this->redirect($this->url('book.manage'));
    }


    /**
     * Helper that runs the where clause, pagination, loads books, copies and maps for categories/genres.
     * Returns an array with keys: books, copies, categories, genres, pagination, whereClause, whereParams
     */
    private
    function fetchBooksForListing(Request $request, array $whereParts = [], array $whereParams = [], int $perPage = 5): array
    {
        $whereClause = null;
        if (!empty($whereParts)) {
            $whereClause = implode(' AND ', $whereParts);
        }

        $page = max(1, (int)($request->value('page') ?? 1));

        $totalCount = Book::getCount($whereClause, $whereParams);
        $pages = (int)ceil($totalCount / $perPage);
        if ($pages < 1) $pages = 1;
        if ($page > $pages) $page = $pages;

        $offset = ($page - 1) * $perPage;

        $books = Book::getAll($whereClause, $whereParams, 'title ASC', $perPage, $offset);

        $copies = [];
        foreach ($books as $b) {
            $bookId = $b->getId();
            // Use centralized helper to compute totals so listing and detail match
            $summary = $this->computeCopiesForBook($bookId);
            // provide counts: available (not reserved), total (copies flagged available) and reserved (count)
            $copies[$bookId] = ['available' => $summary['available'], 'total' => $summary['total'], 'reserved' => $summary['reserved']];
        }

        $categoriesMap = [];
        $cats = Category::getAll();
        foreach ($cats as $c) {
            $id = $c->getId();
            $name = $c->getName();
            $categoriesMap[$id] = $name;
        }

        $genresMap = [];
        $gens = Genre::getAll();
        foreach ($gens as $g) {
            $id = $g->getId();
            $name = $g->getName();
            $genresMap[$id] = $name;
        }

        return [
            'books' => $books,
            'copies' => $copies,
            'categories' => $categoriesMap,
            'genres' => $genresMap,
            'pagination' => ['page' => $page, 'pages' => $pages, 'perPage' => $perPage, 'total' => $totalCount],
            'whereClause' => $whereClause,
            'whereParams' => $whereParams,
        ];
    }

    /**
     * Compute copies summary for a single book.
     * Returns ['physicalTotal' => int, 'total' => int, 'available' => int, 'copies' => array]
     *
     * Logic: keep the same calculation used in listing: 'total' counts copies marked available (available = 1),
     * then available = total - reservedCount (reserved referencing those available copies). Also return
     * physicalTotal (all copies regardless of available flag) and the full copies list for detail views.
     */
    private function computeCopiesForBook($bookId): array
    {
        $physicalTotal = 0;
        $total = 0;
        $available = 0;
        $reservedCount = 0;
        $copiesList = [];
        try {
            $physicalTotal = BookCopy::getCount('book_id = ?', [$bookId]);
            // total = copies currently marked as available
            $total = BookCopy::getCount('book_id = ? AND available = 1', [$bookId]);
            $reservedCount = Reservation::getCount('book_copy_id IN (SELECT id FROM book_copy WHERE book_id = ? AND available = 1) AND is_reserved = 1', [$bookId]);
            $available = max(0, $total - $reservedCount);
            $copiesList = BookCopy::getAll('book_id = ?', [$bookId]);
        } catch (\Throwable $e) {
            // ignore and return defaults
        }

        return ['physicalTotal' => (int)$physicalTotal, 'total' => (int)$total, 'available' => (int)$available, 'reserved' => (int)$reservedCount, 'copies' => $copiesList];
    }
}
