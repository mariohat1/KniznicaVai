<?php

namespace App\Controllers;

use App\Models\Genre;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Responses\JsonResponse;
use App\Support\Validator;

class GenreController extends BaseController
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
        $genres = Genre::getAll();
        return $this->html(['genres' => $genres], 'index');
    }

    public function manage(Request $request): Response
    {
        // Read optional search query
        $q = trim((string)$request->value('q'));

        $whereParts = [];
        $whereParams = [];
        if ($q !== '') {
            $whereParts[] = 'name LIKE ?';
            $whereParams[] = '%' . $q . '%';
        }
        $where = !empty($whereParts) ? implode(' AND ', $whereParts) : null;

        // Pagination
        $page = max(1, (int)($request->value('page') ?? 1));
        $perPage = 10;

        $total = Genre::getCount($where, $whereParams);
        $pages = ($perPage > 0) ? (int)ceil($total / $perPage) : 1;
        if ($pages < 1) $pages = 1;
        if ($page > $pages) $page = $pages;
        $offset = ($page - 1) * $perPage;

        $genres = Genre::getAll($where, $whereParams, 'name ASC', $perPage, $offset);

        return $this->html([
            'genres' => $genres,
            'filters' => ['q' => $q, 'page' => $page],
            'pagination' => ['page' => $page, 'pages' => $pages, 'perPage' => $perPage, 'total' => $total]
        ], 'manage');
    }

    public function add(Request $request): Response
    {
        $id = $request->value('id');
        if (!empty($id)) {
            $genre = Genre::getOne((int)$id);
            return $this->html(['genre' => $genre]);
        }
        return $this->html();
    }

    /**
     * @throws \Exception
     */
    public function store(Request $request): Response
    {
        if ($request->isJson()) {
            try {
                $data = $request->json();
            } catch (\JsonException $e) {
                return $this->json(['error' => 'Neplatný JSON']);
            }
        }
        $name = $data->name ?? $request->value('name');
        $description = $request->value('description') ?? null;
        $errors = [];
        if ($err = Validator::validateShortName($name, 'name')) $errors['name'] = $err;
        if ($err = Validator::validateDescription($description, 2000, 'description')) $errors['description'] = $err;

        if (!empty($errors)) {
            if ($request->isAjax()) {
                return new JsonResponse(['success' => false, 'errors' => $errors]);
            }
            $referer = $request->server('HTTP_REFERER') ?: $this->url('genre.manage');
            return $this->redirect($referer);
        }

        $genre = new Genre();
        $id = $request->value('id');
        if (!empty($id)) {
            $genre = Genre::getOne($id);
        }
        $genre->setName($name);
        $genre->setDescription($description);
        $genre->save();
        return $this->redirect($this->url('genre.manage'));
    }

    /**
     * @throws \Exception
     */
    public function delete(Request $request): Response
    {
        $id = $request->value('id');

        if (!empty($id)) {
            $genre = Genre::getOne($id);
            $genre?->delete();

        }
        return $this->redirect($this->url('genre.manage'));
    }
}
