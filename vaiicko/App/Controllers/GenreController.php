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

        $auth = $this->app->getAuth();
        if (!$auth || !$auth->isLogged()) {
            return false;
        }

        $user = $auth->getUser();
        return $user && strtolower((string)$user->getRole()) === 'admin';
    }

    public function index(Request $request): Response
    {
        $result = $this->paginate($request, 'name ASC', 10);

        return $this->html([
            'genres' => $result['items'],
            'filters' => $result['filters'],
            'pagination' => $result['pagination']
        ], 'index');
    }

    public function manage(Request $request): Response
    {
        $result = $this->paginate($request, 'name ASC', 10);

        return $this->html([
            'genres' => $result['items'],
            'filters' => $result['filters'],
            'pagination' => $result['pagination']
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

        $name = $request->value('name');
        $description = $request->value('description') ?? null;
        $errors = [];
        if ($err = Validator::validateShortName($name, 'name')) $errors[] = $err;
        if ($err = Validator::validateDescription($description, 2000, 'description')) $errors[] = $err;
        $id = $request->value('id');
        if (!empty($id)) {
            $count = Genre::getCount('name = ? AND id != ?', [$name, $id]);
        } else {
            $count = Genre::getCount('name = ?', [$name]);
        }
        if ($count > 0) {
            $errors[] = 'Žáner s týmto názvom už existuje.';
        }

        if (!empty($errors)) {
            if ($request->isAjax()) {
                return new JsonResponse(['success' => false, 'errors' => $errors]);
            }
        }

        $genre = new Genre();
        if (!empty($id)) {
            $loaded = Genre::getOne($id);
            if ($loaded !== null) $genre = $loaded;
        }
        $genre->setName($name);
        $genre->setDescription($description);
        $genre->save();
        if ($request->isAjax()) {
            return $this->json(['success' => true, 'id' => $genre->getId(), 'name' => $genre->getName(), 'redirect' => $this->url('genre.manage')]);
        }
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

    /**
     * Helper: paginate genres (builds where, handles pagination internally)
     */
    private function paginate(Request $request, ?string $orderBy = null, int $perPage = 10): array
    {
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
        $total = Genre::getCount($where, $whereParams);
        $pages = ceil($total / $perPage);
        if ($pages < 1) $pages = 1;
        if ($page > $pages) $page = $pages;
        $offset = ($page - 1) * $perPage;

        $items = Genre::getAll($where, $whereParams, $orderBy, $perPage, $offset);

        return [
            'items' => $items,
            'filters' => ['q' => $q, 'page' => $page],
            'pagination' => ['page' => $page, 'pages' => $pages, 'perPage' => $perPage, 'total' => $total]
        ];
    }
}
