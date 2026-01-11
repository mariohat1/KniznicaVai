<?php

namespace App\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Reservation;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Responses\JsonResponse;
use App\Support\Validator;

class AuthorController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        if ($action === 'index' || $action === 'view') {
            return true;
        }
        if ($action === 'manage') {
            $auth = $this->app->getAuth();
            if (!$auth || !$auth->isLogged()) return false;
            $user = $auth->getUser();
            if (is_object($user) && ($user instanceof \App\Models\User) && method_exists($user, 'getRole')) {
                return (strtolower((string)$user->getRole()) === 'admin');
            }
            return false;
        }
        $auth = $this->app->getAuth();
        if (!$auth->isLogged()) {
            return false;
        }

        $user = $auth->getUser();
        if (is_object($user) && ($user instanceof \App\Models\User) && method_exists($user, 'getRole')) {
            return (strtolower((string)$user->getRole()) === 'admin');
        }

        return false;
    }

    public function index(Request $request): Response
    {
        // Load all authors from DB
        $authors = Author::getAll();

        // Pass authors to the view (view path will be Author/index)
        return $this->html(['authors' => $authors], 'index');
    }

    /**
     * Public single author view + their books
     */
    public function view(Request $request, ?int $id = null): Response
    {
        $useId = $id ?? ($request->value('id') !== null ? (int)$request->value('id') : null);
        if (empty($useId)) {
            return $this->redirect($this->url('author.index'));
        }

        $author = null;
        try {
            $author = Author::getOne((int)$useId);
        } catch (\Throwable $e) {
            // ignore and redirect
        }

        if (!$author) {
            return $this->redirect($this->url('author.index'));
        }

        $books = Book::getAll('author_id = ?', [(int)$useId]);

        $copies = [];
        foreach ($books as $b) {
            try {
                $bookId = $b->getId();
                $total = BookCopy::getCount('book_id = ?', [$bookId]);
                $reserved = Reservation::getCount('book_copy_id IN (SELECT id FROM book_copy WHERE book_id = ?) AND is_reserved = 1', [$bookId]);
                $available = max(0, $total - $reserved);
                $copies[$bookId] = ['total' => $total, 'available' => $available];
            } catch (\Throwable $ex) {
                // on error, fallback to zeros
                $copies[$b->getId()] = ['total' => 0, 'available' => 0];
            }
        }

        return $this->html(['author' => $author, 'books' => $books, 'copies' => $copies], 'view');
    }

    /**
     * Admin management page for authors (list with actions)
     */
    public function manage(Request $request): Response
    {
        $authors = Author::getAll();
        return $this->html(['authors' => $authors], 'manage');
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
        $birth_date = $request->value('birth_date');
        $errors = [];

        if ($err = Validator::validatePersonName($first, 'first name')) {
            $errors[] = $err;
        }

        if ($err = Validator::validatePersonName($last, 'last name')) {
            $errors[] = $err;
        }

        if ($err = Validator::validateBirthDate($birth_date, 'birth_date')) {
            $errors[] = $err;
        }
        if (!empty($errors)) {
            return $this->json(['success' => false, 'errors' => $errors]);
        }

        $author = new Author();
        $id = $request->value('id');
        if (!empty($id)) {
            $author = Author::getOne($id);
        }
        $author->setFirstName($first);
        $author->setLastName($last);
        $author->setDescription($request->value('description'));
        $author->setBirthDate($request->value('birth_date'));
        $photoPath = $request->value('photo_path');
        if ($photoPath && method_exists($author, 'setPhoto')) {
            $author->setPhoto($photoPath);
        }
        $author->save();

        if ($request->isAjax()) {
            return $this->json(['success' => true, 'redirect' => $this->url('author.manage')]);
        }

        return $this->redirect($this->url('author.manage'));

    }

    public function delete(Request $request, ?int $id = null): Response
    {
        // Accept id either from route param ($id) or from POST 'id'
        $idFromReq = $request->value('id');
        $useId = $id ?? ($idFromReq !== null ? (int)$idFromReq : null);
        if (!empty($useId)) {
            try {
                $author = Author::getOne((int)$useId);
                if ($author) {
                    $author->delete();
                }
            } catch (\Throwable $e) {
                // ignore and redirect
            }
        }
        return $this->redirect($this->url('author.manage'));
    }

    public function uploadPhoto(Request $request): Response
    {
        $file = $request->file('photo');
        if (!$file || !$file->isOk()) {
            $msg = $file ? $file->getErrorMessage() : 'No file uploaded';
            return $this->json(['success' => false, 'message' => 'No file uploaded or upload error', 'detail' => $msg])->setStatusCode(400);
        }

        $maxBytes = 5 * 1024 * 1024;
        if ($file->getSize() > $maxBytes) {
            return $this->json(['success' => false, 'message' => 'File too large'])->setStatusCode(400);
        }

        $originalName = $file->getName();
        $clientMime = $file->getType();
        $tmp = $file->getFileTempPath();
        // validate image
        $info = @getimagesize($tmp);
        if ($info === false || empty($info['mime'])) {
            return $this->json(['success' => false, 'message' => 'Invalid image'])->setStatusCode(400);
        }
        $mime = $info['mime'];
        if ($mime !== 'image/png') {
            return $this->json(['success' => false, 'message' => 'Only PNG images are allowed', 'detected' => $mime, 'client' => $clientMime])->setStatusCode(400);
        }
        $projectRoot = dirname(__DIR__, 2);
        $uploadDir = $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'author';

        $filename = uniqid('author_', true) . '.png';
        $dest = $uploadDir . DIRECTORY_SEPARATOR . $filename;
        if (!$file->store($dest)) {
            return $this->json(['success' => false, 'message' => 'Failed to save uploaded file'])->setStatusCode(500);
        }
        // Return public path for client
        $publicPath = '/uploads/author/' . $filename;
        return $this->json(['success' => true, 'path' => $publicPath]);

    }
}
