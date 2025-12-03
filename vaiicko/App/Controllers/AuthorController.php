<?php

namespace App\Controllers;

use App\Models\Author;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class AuthorController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        if ($action === 'index') {
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
     * Admin management page for authors (list with actions)
     */
    public function manage(Request $request): Response
    {
        $authors = Author::getAll();
        return $this->html(['authors' => $authors], 'manage');
    }

    public function add(Request $request): Response
    {
        // If an id is provided, load the author and pass to view for editing
        $id = $request->value('id');
        if (!empty($id)) {
            $author = Author::getOne((int)$id);
            return $this->html(['author' => $author]);
        }
        return $this->html();
    }


    public function store(Request $request): Response
    {
        // Create author from POST and save
        $author = new Author();
        $id = $request->value('id');
        if (!empty($id)) {
            $author = Author::getOne($id);

        }

        $author->setFirstName($request->value('first_name'));
        $author->setLastName($request->value('last_name'));
        $author->setNationality($request->value('nationality'));
        $author->setBirthDate($request->value('birth_date'));
        // If an uploaded photo path was provided by the uploader, attach it to the model
        $photoPath = $request->value('photo_path');
        if ($photoPath && method_exists($author, 'setPhoto')) {
            $author->setPhoto($photoPath);
        }
        $author->save();
        return $this->redirect($this->url('author.index'));
    }
    public function update(Request $request, int $id): Response
    {
        // Update author with given ID from POST data
        try {
            $author = Author::getOne($id);
        } catch (\Exception $e) {

        }
        if ($author) {
            $author->setFromRequest($request);
            try {
                $author->save();
            } catch (\Exception $e) {

            }
        }
        return $this->redirect($this->url('author.index'));
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
        return $this->redirect($this->url('author.index'));
    }
    public function uploadPhoto(Request $request): Response
    {
        // Handle photo upload for author
        $file = $request->file('photo');
        if (!$file || !$file->isOk()) {
            $msg = $file ? $file->getErrorMessage() : 'No file uploaded';
            return $this->json(['success' => false, 'message' => 'No file uploaded or upload error', 'detail' => $msg])->setStatusCode(400);
        }

        // Basic validation: size limit and MIME check (PNG only as requested)
        $maxBytes = 5 * 1024 * 1024; // 5 MB
        if ($file->getSize() > $maxBytes) {
            return $this->json(['success' => false, 'message' => 'File too large'])->setStatusCode(400);
        }

        $originalName = $file->getName();
        $clientMime = $file->getType();
        $tmp = $file->getFileTempPath();
        // Use getimagesize to discover MIME type reliably from the temporary uploaded file
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

        // ensure upload directory exists
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid('author_', true) . '.png';
        $dest = $uploadDir . DIRECTORY_SEPARATOR . $filename;
        if (!$file->store($dest)) {
            return $this->json(['success' => false, 'message' => 'Failed to save uploaded file'])->setStatusCode(500);
        }
        $relative = '/uploads/author/' . $filename;

        return $this->json(['success' => true, 'path' => $relative, 'filename' => $filename, 'original' => $originalName]);
    }
}
