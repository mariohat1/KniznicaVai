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
        // Allow public viewing of the index (authors list)
        if ($action === 'index') {
            return true;
        }

        // For other actions (add/store) require explicit admin role
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
        return $this->html(['authors' => $authors]);
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
}
