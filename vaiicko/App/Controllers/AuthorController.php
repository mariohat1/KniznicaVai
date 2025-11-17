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
        // Allow public viewing of the index (authors list); require login for other actions (add/store)
        if ($action === 'index') {
            return true;
        }
        return $this->app->getAuth()->isLogged();
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
        // Show form to add author
        return $this->html();
    }

    public function store(Request $request): Response
    {
        // Create author from POST and save
        $author = new Author();
        $author->setFromRequest($request);
        $author->save();
        return $this->redirect($this->url('author.index'));
    }
}
