<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Category\Infrastructure\Http;

use Gaby\Apps\Api\Category\Application\CreateCategory\CreateCategoryCommand;
use Gaby\Apps\Api\Category\Application\CreateCategory\CreateCategoryHandler;
use Gaby\Apps\Api\Category\Application\DeleteCategory\DeleteCategoryCommand;
use Gaby\Apps\Api\Category\Application\DeleteCategory\DeleteCategoryHandler;
use Gaby\Apps\Api\Category\Application\GetCategory\GetCategoryHandler;
use Gaby\Apps\Api\Category\Application\GetCategory\GetCategoryQuery;
use Gaby\Apps\Api\Category\Application\ListCategories\ListCategoriesHandler;
use Gaby\Apps\Api\Category\Application\ListCategories\ListCategoriesQuery;
use Gaby\Apps\Api\Category\Application\UpdateCategory\UpdateCategoryCommand;
use Gaby\Apps\Api\Category\Application\UpdateCategory\UpdateCategoryHandler;
use Gaby\Apps\Api\Category\Domain\Category;
use Gaby\Apps\Api\Category\Domain\Exception\CategoryNotFoundException;
use Gaby\Apps\Api\Category\Domain\Exception\CategorySlugAlreadyInUseException;
use Gaby\Shared\Domain\Slug;
use Gaby\Shared\Http\JsonResponseTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class CategoryController
{
    use JsonResponseTrait;

    /** @var CreateCategoryHandler */
    private $createCategory;

    /** @var ListCategoriesHandler */
    private $listCategories;

    /** @var GetCategoryHandler */
    private $getCategory;

    /** @var UpdateCategoryHandler */
    private $updateCategory;

    /** @var DeleteCategoryHandler */
    private $deleteCategory;

    public function __construct(
        CreateCategoryHandler $createCategory,
        ListCategoriesHandler $listCategories,
        GetCategoryHandler $getCategory,
        UpdateCategoryHandler $updateCategory,
        DeleteCategoryHandler $deleteCategory
    ) {
        $this->createCategory = $createCategory;
        $this->listCategories = $listCategories;
        $this->getCategory = $getCategory;
        $this->updateCategory = $updateCategory;
        $this->deleteCategory = $deleteCategory;
    }

    public function list(Request $request, Response $response): Response
    {
        return $this->presentMany($response, false);
    }

    public function listPublic(Request $request, Response $response): Response
    {
        return $this->presentMany($response, true);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $category = $this->getCategory->handle(new GetCategoryQuery((string) $args['id']));
        } catch (CategoryNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        }

        return $this->json($response, $category->toArray());
    }

    public function create(Request $request, Response $response): Response
    {
        $data = (array) $request->getParsedBody();
        $name = isset($data['name']) ? trim((string) $data['name']) : '';
        $slugInput = isset($data['slug']) ? trim((string) $data['slug']) : '';
        $slug = Slug::fromText($slugInput !== '' ? $slugInput : $name);
        $description = isset($data['description']) && trim((string) $data['description']) !== ''
            ? trim((string) $data['description'])
            : null;
        $status = $this->normalizeStatus(isset($data['status']) ? (string) $data['status'] : Category::STATUS_ACTIVE);

        if ($name === '' || $slug === '') {
            return $this->json($response, ['error' => 'Name is required.'], 422);
        }

        try {
            $category = $this->createCategory->handle(
                new CreateCategoryCommand($name, $slug, $description, $status)
            );
        } catch (CategorySlugAlreadyInUseException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 409);
        }

        return $this->json($response, $category->toArray(), 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data = (array) $request->getParsedBody();
        $name = isset($data['name']) ? trim((string) $data['name']) : '';
        $slugInput = isset($data['slug']) ? trim((string) $data['slug']) : '';
        $slug = Slug::fromText($slugInput !== '' ? $slugInput : $name);
        $description = isset($data['description']) && trim((string) $data['description']) !== ''
            ? trim((string) $data['description'])
            : null;
        $status = $this->normalizeStatus(isset($data['status']) ? (string) $data['status'] : Category::STATUS_ACTIVE);

        if ($name === '' || $slug === '') {
            return $this->json($response, ['error' => 'Name is required.'], 422);
        }

        try {
            $category = $this->updateCategory->handle(
                new UpdateCategoryCommand((string) $args['id'], $name, $slug, $description, $status)
            );
        } catch (CategoryNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        } catch (CategorySlugAlreadyInUseException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 409);
        }

        return $this->json($response, $category->toArray());
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $this->deleteCategory->handle(new DeleteCategoryCommand((string) $args['id']));
        } catch (CategoryNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        }

        return $response->withStatus(204);
    }

    private function presentMany(Response $response, bool $activeOnly): Response
    {
        $categories = $this->listCategories->handle(new ListCategoriesQuery($activeOnly));

        return $this->json($response, [
            'categories' => array_map(function (Category $category): array {
                return $category->toArray();
            }, $categories),
        ]);
    }

    private function normalizeStatus(string $status): string
    {
        return $status === Category::STATUS_INACTIVE ? Category::STATUS_INACTIVE : Category::STATUS_ACTIVE;
    }
}
