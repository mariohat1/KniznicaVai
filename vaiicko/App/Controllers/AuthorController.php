<?php

namespace App\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Reservation;
use Exception;
use App\Support\AuthHelper;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Responses\JsonResponse;
use App\Support\Validator;
use App\Support\PhotoUpload;

class AuthorController extends BaseController
{
    use AuthHelper;

    public function authorize(Request $request, string $action): bool
    {
        if ($action === 'index' || $action === 'view') return true;

        return $this->isAdmin();
    }

    public function index(Request $request): Response
    {
        $result = $this->paginate($request);
        return $this->html([
            'authors' => $result['items'],
            'filters' => $result['filters'],
            'pagination' => $result['pagination']
        ], 'index');
    }

    /**
     * Public single author view + their books
     * @throws Exception
     */
    public function view(Request $request): Response
    {
        $id = $request->value('id');
        if (empty($id)) {
            return $this->redirect($this->url('author.index'));
        }
        $author = null;
        try {
            $author = Author::getOne((int)$id);
        } catch (\Throwable $e) {
        }
        if (!$author) {
            return $this->redirect($this->url('author.index'));
        }
        $books = [];
        try {
            $totalBooks = Book::getCount('author_id = ?', [$id]);
            $books = Book::getAll('author_id = ?', [$id], 'title ASC', 3, 0);
        } catch (Exception $e) {
            $totalBooks = 0;
        }
        $copies = [];
        foreach ($books as $b) {
            try {
                $bookId = $b->getId();
                $total = BookCopy::getCount('book_id = ? AND available = 1', [$bookId]);
                $reserved = Reservation::getCount('book_copy_id IN (SELECT id FROM book_copy WHERE book_id = ? AND available = 1) AND is_reserved = 1', [$bookId]);
                $available = max(0, $total - $reserved);
                $copies[$bookId] = ['total' => $total, 'available' => $available];
            } catch (\Throwable $ex) {
                $copies[$b->getId()] = ['total' => 0, 'available' => 0];
            }
        }

        return $this->html([
            'author' => $author,
            'books' => $books,
            'copies' => $copies,
            'totalBooks' => $totalBooks
        ], 'view');
    }

    /**
     * Admin management page for authors (list with actions)
     */
    public function manage(Request $request): Response
    {
        $result = $this->paginate($request);

        return $this->html([
            'authors' => $result['items'],
            'filters' => $result['filters'],
            'pagination' => $result['pagination']
        ], 'manage');
    }

    public function add(Request $request): Response
    {
        $id = $request->value('id');
        if (!empty($id)) {
            $author = Author::getOne((int)$id);
            return $this->html(['author' => $author]);
        }
        return $this->html();
    }


    public function store(Request $request): Response
    {
        $first = $request->value('first_name');
        $last = $request->value('last_name');
        $birth_year = $request->value('birth_year');
        $death_year = $request->value('death_year');
        $errors = [];

        if ($err = Validator::validatePersonName($first, 'first name')) {
            $errors[] = $err;
        }

        if ($err = Validator::validatePersonName($last, 'last name')) {
            $errors[] = $err;
        }

        if ($err = Validator::validateYearRange($birth_year, $death_year)) {
            $errors[] = $err;
        }

        if (!empty($errors)) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'errors' => $errors]);
            }
        }

        $author = new Author();
        $id = $request->value('id');
        if (!empty($id)) {
            $author = Author::getOne($id);
        }
        $author->setFirstName($first);
        $author->setLastName($last);
        $author->setDescription($request->value('description'));
        $author->setBirthYear((int)$birth_year);
        $author->setDeathYear((int)$death_year);
        if ($photoError = $this->handlePhotoUpload($request, $author)) {
            $errors[] = $photoError;
        }
        if (!empty($errors)) {
            if ($request->isAjax()) {
                return $this->json(['success' => false, 'errors' => $errors]);
            }
        }
        $author->save();
        if ($request->isAjax()) {
            return $this->json(['success' => true, 'redirect' => $this->url('author.manage')]);
        }

        return $this->redirect($this->url('author.manage'));
    }

    /**
     * Handle photo upload for author
     * @return string|null Error message or null on success
     */
    private function handlePhotoUpload(Request $request, Author $author): ?string
    {
        $photoFile = $request->file('photo');
        if (!$photoFile || !$photoFile->isOk()) {
            return null;
        }

        $path = PhotoUpload::handle($request, 'author', 'author');
        if ($path !== null) {
            $author->setPhoto($path);
            return null;
        }

        $err = PhotoUpload::lastError();
        return 'Photo upload failed: ' . ($err ?? 'Unknown error');
    }

    public function delete(Request $request, ?int $id = null): Response
    {
        $id = $request->value('id');
        if ($id === null) {
            return $this->redirect($this->url('author.manage'));
        }
        $author =null;
        try {
            $author = Author::getOne($id);
        } catch (\Throwable $ex) {
        }
        if ($author === null) {
            return $this->redirect($this->url('author.manage'));
        }
        try {
            $photo = $author->getPhoto();
            if (!empty($photo)) {
                $projectRoot = dirname(__DIR__, 2);
                $photoPath = $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . ltrim($photo, '/\\');
                if (is_file($photoPath)) {
                    unlink($photoPath);
                }
            }
        } catch (\Throwable $ignored) {
            return $this->redirect($this->url('author.manage'));
        }

        try {
            $author->delete();
        } catch (\Throwable $ignored) {
            return $this->redirect($this->url('author.manage'));
        }

        return $this->redirect($this->url('author.manage'));
    }

    private function paginate(Request $request): array
    {
        $q = trim((string)$request->value('q'));
        $whereParts = [];
        $whereParams = [];
        if ($q !== '') {
            $like = '%' . $q . '%';
            $whereParts[] = "(CONCAT(first_name, ' ', last_name) LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
            $whereParams[] = $like;
            $whereParams[] = $like;
            $whereParams[] = $like;
        }
        $where = !empty($whereParts) ? implode(' AND ', $whereParts) : null;

        $perPage = 10;
        $page = max(1, (int)($request->value('page') ?? 1));
        $total = Author::getCount($where, $whereParams);
        $pages = (int)ceil($total / $perPage);
        if ($pages < 1) $pages = 1;
        if ($page > $pages) $page = $pages;
        $offset = ($page - 1) * $perPage;

        $items = Author::getAll($where, $whereParams, 'last_name ASC, first_name ASC', $perPage, $offset);

        return [
            'items' => $items,
            'filters' => ['q' => $q, 'page' => $page],
            'pagination' => ['page' => $page, 'pages' => $pages, 'perPage' => $perPage, 'total' => $total]
        ];
    }
}
