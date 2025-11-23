<?php

namespace App\Controllers;

use App\Models\Genre;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\JsonResponse;
use Framework\Http\Responses\Response;

class GenreController extends BaseController
{
    public function authorize(Request $request, string $action): bool
    {
        $auth = $this->app->getAuth();
        if (!$auth || !$auth->isLogged()) {
            return false;
        }
        $user = $auth->getUser();
        if (is_object($user) && method_exists($user, 'getRole')) {
            return (strtolower((string)$user->getRole()) === 'admin');
        }
        $vars = is_object($user) ? get_object_vars($user) : [];
        return (isset($vars['role']) && strtolower((string)$vars['role']) === 'admin');
    }

    public function store(Request $request): Response
    {
        if (!$request->isPost()) {
            return (new JsonResponse(['error' => 'Method not allowed']))->setStatusCode(405);
        }

        $data = null;
        if ($request->isJson()) {
            try {
                $data = $request->json();
            } catch (\JsonException $e) {
                $data = null;
            }
        } else {
            $data = $request->post();
        }

        $name = null;
        if (is_array($data)) {
            $name = trim((string)($data['name'] ?? ''));
        } elseif (is_object($data)) {
            $name = trim((string)($data->name ?? ''));
        }

        if ($name === '') {
            return (new JsonResponse(['error' => 'Missing name']))->setStatusCode(400);
        }

        $genre = new Genre();
        $genre->setName($name);
        $genre->save();

        $id = $genre->getId();
        $label = $genre->getName();

        return (new JsonResponse(['id' => $id, 'name' => $label]))->setStatusCode(201);
    }

    public function index(Request $request): Response
    {
        $all = Genre::getAll();
        return $this->json($all);
    }
}
