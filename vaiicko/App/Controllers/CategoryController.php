<?php

namespace App\Controllers;

use App\Models\Category;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Responses\JsonResponse;
use App\Support\Validator;

class CategoryController extends BaseController
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
        $categories = Category::getAll();
        return $this->html(['categories' => $categories], 'index');
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

        $total = Category::getCount($where, $whereParams);
        $pages = ($perPage > 0) ? (int)ceil($total / $perPage) : 1;
        if ($pages < 1) $pages = 1;
        if ($page > $pages) $page = $pages;
        $offset = ($page - 1) * $perPage;

        $categories = Category::getAll($where, $whereParams, 'name ASC', $perPage, $offset);

        return $this->html([
            'categories' => $categories,
            'filters' => ['q' => $q, 'page' => $page],
            'pagination' => ['page' => $page, 'pages' => $pages, 'perPage' => $perPage, 'total' => $total]
        ], 'manage');
    }

    public function add(Request $request): Response
    {
        $id = $request->value('id');
        if (!empty($id)) {
            $category = Category::getOne((int)$id);
            return $this->html(['category' => $category]);
        }
        return $this->html();
    }

    /**
     * @throws \Exception
     */
    public function store(Request $request): Response
    {

        $name = $data->name ?? $request->value('name');
        $description = $request->value('description') ?? null;
        $errors = [];
        if ($err = Validator::validateShortName($name, 'name')) {
            $errors[] = $err;
        };
        if ($err = Validator::validateDescription($description, 2000, 'description')) {
            $errors[] = $err;
        }

        if (!empty($errors)) {
            if ($request->isAjax()) {
                return new JsonResponse(['success' => false, 'errors' => $errors]);
            }
        }
        $category = new Category();
        $id = $request->value('id');
        if (!empty($id)) {
            $loaded = Category::getOne($id);
            if ($loaded !== null) $category = $loaded;
        }
        $category->setName($name);
        $category->setDescription($description);
        $category->save();
        if ($request->isAjax()) {
            return $this->json(['success' => true, 'id' => $category->getId(), 'name' => $category->getName(),
                'redirect' => $this->url('category.manage')]);
        };

        return $this->redirect($this->url('category.manage'));
    }


    /**
     * @throws \Exception
     */
    public function delete(Request $request): Response
    {
        $id = $request->value('id');
        if (!empty($id)) {
            $category = Category::getOne($id);
            $category?->delete();
        }
        return $this->redirect($this->url('category.manage'));
    }
}
