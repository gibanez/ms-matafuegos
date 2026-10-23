<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Product\Infrastructure\Http;

use Gaby\Apps\Api\Category\Domain\Category;
use Gaby\Apps\Api\Category\Domain\CategoryRepositoryInterface;
use Gaby\Apps\Api\Media\Domain\Media;
use Gaby\Apps\Api\Media\Domain\MediaRepositoryInterface;
use Gaby\Apps\Api\Product\Application\AdjustStock\AdjustStockCommand;
use Gaby\Apps\Api\Product\Application\AdjustStock\AdjustStockHandler;
use Gaby\Apps\Api\Product\Application\CreateProduct\CreateProductCommand;
use Gaby\Apps\Api\Product\Application\CreateProduct\CreateProductHandler;
use Gaby\Apps\Api\Product\Application\DeleteProduct\DeleteProductCommand;
use Gaby\Apps\Api\Product\Application\DeleteProduct\DeleteProductHandler;
use Gaby\Apps\Api\Product\Application\GetProduct\GetProductHandler;
use Gaby\Apps\Api\Product\Application\GetProduct\GetProductQuery;
use Gaby\Apps\Api\Product\Application\GetProductBySlug\GetProductBySlugHandler;
use Gaby\Apps\Api\Product\Application\GetProductBySlug\GetProductBySlugQuery;
use Gaby\Apps\Api\Product\Application\ListProducts\ListProductsHandler;
use Gaby\Apps\Api\Product\Application\ListProducts\ListProductsQuery;
use Gaby\Apps\Api\Product\Application\UpdateProduct\UpdateProductCommand;
use Gaby\Apps\Api\Product\Application\UpdateProduct\UpdateProductHandler;
use Gaby\Apps\Api\Product\Domain\Exception\InsufficientStockException;
use Gaby\Apps\Api\Product\Domain\Exception\InvalidProductReferenceException;
use Gaby\Apps\Api\Product\Domain\Exception\ProductNotFoundException;
use Gaby\Apps\Api\Product\Domain\Exception\ProductSlugAlreadyInUseException;
use Gaby\Apps\Api\Product\Domain\Exception\SkuAlreadyInUseException;
use Gaby\Apps\Api\Product\Domain\Product;
use Gaby\Apps\Api\Provider\Domain\Provider;
use Gaby\Apps\Api\Provider\Domain\ProviderRepositoryInterface;
use Gaby\Shared\Domain\Money;
use Gaby\Shared\Domain\Slug;
use Gaby\Shared\Http\JsonResponseTrait;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ProductController
{
    use JsonResponseTrait;

    /** @var CreateProductHandler */
    private $createProduct;

    /** @var ListProductsHandler */
    private $listProducts;

    /** @var GetProductHandler */
    private $getProduct;

    /** @var GetProductBySlugHandler */
    private $getProductBySlug;

    /** @var UpdateProductHandler */
    private $updateProduct;

    /** @var DeleteProductHandler */
    private $deleteProduct;

    /** @var AdjustStockHandler */
    private $adjustStock;

    /** @var CategoryRepositoryInterface */
    private $categories;

    /** @var ProviderRepositoryInterface */
    private $providers;

    /** @var MediaRepositoryInterface */
    private $media;

    public function __construct(
        CreateProductHandler $createProduct,
        ListProductsHandler $listProducts,
        GetProductHandler $getProduct,
        GetProductBySlugHandler $getProductBySlug,
        UpdateProductHandler $updateProduct,
        DeleteProductHandler $deleteProduct,
        AdjustStockHandler $adjustStock,
        CategoryRepositoryInterface $categories,
        ProviderRepositoryInterface $providers,
        MediaRepositoryInterface $media
    ) {
        $this->createProduct = $createProduct;
        $this->listProducts = $listProducts;
        $this->getProduct = $getProduct;
        $this->getProductBySlug = $getProductBySlug;
        $this->updateProduct = $updateProduct;
        $this->deleteProduct = $deleteProduct;
        $this->adjustStock = $adjustStock;
        $this->categories = $categories;
        $this->providers = $providers;
        $this->media = $media;
    }

    public function list(Request $request, Response $response): Response
    {
        $filters = $this->filtersFromQuery($request, false);

        return $this->presentMany($response, $filters, false);
    }

    public function publicList(Request $request, Response $response): Response
    {
        $filters = $this->filtersFromQuery($request, true);

        return $this->presentMany($response, $filters, true);
    }

    public function lowStock(Request $request, Response $response): Response
    {
        $filters = $this->filtersFromQuery($request, false);
        $filters['lowStock'] = true;

        return $this->presentMany($response, $filters, false);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $product = $this->getProduct->handle(new GetProductQuery((string) $args['id']));
        } catch (ProductNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        }

        return $this->json($response, $this->present($product, false));
    }

    public function publicShow(Request $request, Response $response, array $args): Response
    {
        try {
            $product = $this->getProductBySlug->handle(
                new GetProductBySlugQuery((string) $args['slug'], true)
            );
        } catch (ProductNotFoundException $error) {
            return $this->json($response, ['error' => 'Product not found.'], 404);
        }

        return $this->json($response, $this->present($product, true));
    }

    public function create(Request $request, Response $response): Response
    {
        try {
            $input = $this->parseInput((array) $request->getParsedBody());
        } catch (InvalidArgumentException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 422);
        }

        if ($input['name'] === '' || $input['slug'] === '') {
            return $this->json($response, ['error' => 'Name is required.'], 422);
        }

        try {
            $product = $this->createProduct->handle(new CreateProductCommand(
                $input['name'],
                $input['slug'],
                $input['description'],
                $input['sku'],
                $input['categoryId'],
                $input['providerId'],
                $input['priceCents'],
                $input['showPrice'],
                $input['stock'],
                $input['minStock'],
                $input['featured'],
                $input['requiresMaintenance'],
                $input['maintenanceDays'],
                $input['status']
            ));
        } catch (ProductSlugAlreadyInUseException | SkuAlreadyInUseException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 409);
        } catch (InvalidProductReferenceException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 422);
        }

        return $this->json($response, $this->present($product, false), 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $input = $this->parseInput((array) $request->getParsedBody());
        } catch (InvalidArgumentException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 422);
        }

        if ($input['name'] === '' || $input['slug'] === '') {
            return $this->json($response, ['error' => 'Name is required.'], 422);
        }

        try {
            $product = $this->updateProduct->handle(new UpdateProductCommand(
                (string) $args['id'],
                $input['name'],
                $input['slug'],
                $input['description'],
                $input['sku'],
                $input['categoryId'],
                $input['providerId'],
                $input['priceCents'],
                $input['showPrice'],
                $input['stock'],
                $input['minStock'],
                $input['featured'],
                $input['requiresMaintenance'],
                $input['maintenanceDays'],
                $input['status']
            ));
        } catch (ProductNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        } catch (ProductSlugAlreadyInUseException | SkuAlreadyInUseException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 409);
        } catch (InvalidProductReferenceException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 422);
        }

        return $this->json($response, $this->present($product, false));
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $this->deleteProduct->handle(new DeleteProductCommand((string) $args['id']));
        } catch (ProductNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        }

        return $response->withStatus(204);
    }

    public function adjustStock(Request $request, Response $response, array $args): Response
    {
        $data = (array) $request->getParsedBody();
        $delta = filter_var($data['delta'] ?? null, FILTER_VALIDATE_INT);

        if ($delta === false) {
            return $this->json($response, ['error' => 'delta must be an integer.'], 422);
        }

        try {
            $product = $this->adjustStock->handle(
                new AdjustStockCommand((string) $args['id'], (int) $delta)
            );
        } catch (ProductNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        } catch (InsufficientStockException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 409);
        }

        return $this->json($response, $this->present($product, false));
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function presentMany(Response $response, array $filters, bool $public): Response
    {
        $products = $this->listProducts->handle(new ListProductsQuery($filters));

        return $this->json($response, [
            'products' => array_map(function (Product $product) use ($public): array {
                return $this->present($product, $public);
            }, $products),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Product $product, bool $public): array
    {
        $data = $product->toArray($public);

        if ($product->categoryId() !== null) {
            $category = $this->categories->findById($product->categoryId());

            if ($category instanceof Category) {
                $data['category'] = [
                    'id' => $category->id(),
                    'name' => $category->name(),
                    'slug' => $category->slug(),
                ];
            }
        }

        if (!$public && $product->providerId() !== null) {
            $provider = $this->providers->findById($product->providerId());

            if ($provider instanceof Provider) {
                $data['provider'] = [
                    'id' => $provider->id(),
                    'name' => $provider->name(),
                ];
            }
        }

        $data['media'] = array_map(function (Media $media): array {
            return $media->toArray();
        }, $this->media->findByOwner(Media::OWNER_PRODUCT, $product->id()));

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function filtersFromQuery(Request $request, bool $public): array
    {
        $params = $request->getQueryParams();
        $filters = [];

        if ($public) {
            $filters['activeOnly'] = true;
        } elseif (isset($params['active'])) {
            $filters['activeOnly'] = $this->toBool($params['active']);
        }

        if (isset($params['featured'])) {
            $filters['featured'] = $this->toBool($params['featured']);
        }

        if (isset($params['categoryId']) && trim((string) $params['categoryId']) !== '') {
            $filters['categoryId'] = trim((string) $params['categoryId']);
        }

        if (isset($params['q']) && trim((string) $params['q']) !== '') {
            $filters['q'] = trim((string) $params['q']);
        }

        return $filters;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function parseInput(array $data): array
    {
        $name = isset($data['name']) ? trim((string) $data['name']) : '';
        $slugInput = isset($data['slug']) ? trim((string) $data['slug']) : '';
        $slug = Slug::fromText($slugInput !== '' ? $slugInput : $name);

        $priceCents = 0;

        if (array_key_exists('price', $data) && $data['price'] !== null && $data['price'] !== '') {
            try {
                $priceCents = Money::toCents($data['price']);
            } catch (InvalidArgumentException $error) {
                throw new InvalidArgumentException('Invalid price.');
            }
        }

        if ($priceCents < 0) {
            throw new InvalidArgumentException('Price cannot be negative.');
        }

        return [
            'name' => $name,
            'slug' => $slug,
            'description' => $this->nullableString($data, 'description'),
            'sku' => $this->nullableString($data, 'sku'),
            'categoryId' => $this->nullableString($data, 'categoryId'),
            'providerId' => $this->nullableString($data, 'providerId'),
            'priceCents' => $priceCents,
            'showPrice' => array_key_exists('showPrice', $data) ? $this->toBool($data['showPrice']) : true,
            'stock' => $this->nonNegativeInt($data, 'stock'),
            'minStock' => $this->nonNegativeInt($data, 'minStock'),
            'featured' => array_key_exists('featured', $data) ? $this->toBool($data['featured']) : false,
            'requiresMaintenance' => array_key_exists('requiresMaintenance', $data)
                ? $this->toBool($data['requiresMaintenance'])
                : false,
            'maintenanceDays' => $this->maintenanceDays($data),
            'status' => (isset($data['status']) && (string) $data['status'] === Product::STATUS_INACTIVE)
                ? Product::STATUS_INACTIVE
                : Product::STATUS_ACTIVE,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function maintenanceDays(array $data): int
    {
        if (!array_key_exists('maintenanceDays', $data) || $data['maintenanceDays'] === null || $data['maintenanceDays'] === '') {
            return 365;
        }

        $value = filter_var($data['maintenanceDays'], FILTER_VALIDATE_INT);

        if ($value === false || $value < 1 || $value > 3650) {
            throw new InvalidArgumentException('maintenanceDays must be an integer between 1 and 3650.');
        }

        return (int) $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function nullableString(array $data, string $key): ?string
    {
        if (!isset($data[$key])) {
            return null;
        }

        $value = trim((string) $data[$key]);

        return $value === '' ? null : $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function nonNegativeInt(array $data, string $key): int
    {
        $value = filter_var($data[$key] ?? 0, FILTER_VALIDATE_INT);

        if ($value === false || $value < 0) {
            throw new InvalidArgumentException($key . ' must be a non-negative integer.');
        }

        return (int) $value;
    }

    /**
     * @param mixed $value
     */
    private function toBool($value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'yes'], true);
    }
}
