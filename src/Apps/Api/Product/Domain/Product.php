<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Product\Domain;

use Gaby\Shared\Domain\Money;
use Ramsey\Uuid\Uuid;

final class Product
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $slug;

    /** @var string|null */
    private $description;

    /** @var string|null */
    private $sku;

    /** @var string|null */
    private $categoryId;

    /** @var string|null */
    private $providerId;

    /** @var int */
    private $priceCents;

    /** @var bool */
    private $showPrice;

    /** @var int */
    private $stock;

    /** @var int */
    private $minStock;

    /** @var bool */
    private $featured;

    /** @var bool */
    private $requiresMaintenance;

    /** @var int */
    private $maintenanceDays;

    /** @var string */
    private $status;

    /** @var string */
    private $createdAt;

    /** @var string */
    private $updatedAt;

    private function __construct(
        string $id,
        string $name,
        string $slug,
        ?string $description,
        ?string $sku,
        ?string $categoryId,
        ?string $providerId,
        int $priceCents,
        bool $showPrice,
        int $stock,
        int $minStock,
        bool $featured,
        bool $requiresMaintenance,
        int $maintenanceDays,
        string $status,
        string $createdAt,
        string $updatedAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->sku = $sku;
        $this->categoryId = $categoryId;
        $this->providerId = $providerId;
        $this->priceCents = $priceCents;
        $this->showPrice = $showPrice;
        $this->stock = $stock;
        $this->minStock = $minStock;
        $this->featured = $featured;
        $this->requiresMaintenance = $requiresMaintenance;
        $this->maintenanceDays = $maintenanceDays;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function create(
        string $name,
        string $slug,
        ?string $description,
        ?string $sku,
        ?string $categoryId,
        ?string $providerId,
        int $priceCents,
        bool $showPrice,
        int $stock,
        int $minStock,
        bool $featured,
        bool $requiresMaintenance,
        int $maintenanceDays,
        string $status,
        string $now
    ): self {
        return new self(
            Uuid::uuid4()->toString(),
            $name,
            $slug,
            $description,
            $sku,
            $categoryId,
            $providerId,
            $priceCents,
            $showPrice,
            $stock,
            $minStock,
            $featured,
            $requiresMaintenance,
            $maintenanceDays,
            $status,
            $now,
            $now
        );
    }

    public static function fromPersistence(
        string $id,
        string $name,
        string $slug,
        ?string $description,
        ?string $sku,
        ?string $categoryId,
        ?string $providerId,
        int $priceCents,
        bool $showPrice,
        int $stock,
        int $minStock,
        bool $featured,
        bool $requiresMaintenance,
        int $maintenanceDays,
        string $status,
        string $createdAt,
        string $updatedAt
    ): self {
        return new self(
            $id,
            $name,
            $slug,
            $description,
            $sku,
            $categoryId,
            $providerId,
            $priceCents,
            $showPrice,
            $stock,
            $minStock,
            $featured,
            $requiresMaintenance,
            $maintenanceDays,
            $status,
            $createdAt,
            $updatedAt
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function sku(): ?string
    {
        return $this->sku;
    }

    public function categoryId(): ?string
    {
        return $this->categoryId;
    }

    public function providerId(): ?string
    {
        return $this->providerId;
    }

    public function priceCents(): int
    {
        return $this->priceCents;
    }

    public function showPrice(): bool
    {
        return $this->showPrice;
    }

    public function stock(): int
    {
        return $this->stock;
    }

    public function minStock(): int
    {
        return $this->minStock;
    }

    public function featured(): bool
    {
        return $this->featured;
    }

    public function requiresMaintenance(): bool
    {
        return $this->requiresMaintenance;
    }

    public function maintenanceDays(): int
    {
        return $this->maintenanceDays;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function createdAt(): string
    {
        return $this->createdAt;
    }

    public function updatedAt(): string
    {
        return $this->updatedAt;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->minStock;
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    public function updateDetails(
        string $name,
        string $slug,
        ?string $description,
        ?string $sku,
        ?string $categoryId,
        ?string $providerId,
        int $priceCents,
        bool $showPrice,
        int $stock,
        int $minStock,
        bool $featured,
        bool $requiresMaintenance,
        int $maintenanceDays,
        string $status,
        string $now
    ): void {
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->sku = $sku;
        $this->categoryId = $categoryId;
        $this->providerId = $providerId;
        $this->priceCents = $priceCents;
        $this->showPrice = $showPrice;
        $this->stock = $stock;
        $this->minStock = $minStock;
        $this->featured = $featured;
        $this->requiresMaintenance = $requiresMaintenance;
        $this->maintenanceDays = $maintenanceDays;
        $this->status = $status;
        $this->updatedAt = $now;
    }

    public function adjustStock(int $delta): void
    {
        $newStock = $this->stock + $delta;

        if ($newStock < 0) {
            throw \Gaby\Apps\Api\Product\Domain\Exception\InsufficientStockException::forProduct($this->id);
        }

        $this->stock = $newStock;
        $this->updatedAt = date('c');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(bool $public = false): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sku' => $this->sku,
            'categoryId' => $this->categoryId,
            'price' => $public && !$this->showPrice ? null : Money::toDecimal($this->priceCents),
            'showPrice' => $this->showPrice,
            'stock' => $this->stock,
            'inStock' => $this->isInStock(),
            'featured' => $this->featured,
        ];

        if (!$public) {
            $data['providerId'] = $this->providerId;
            $data['minStock'] = $this->minStock;
            $data['lowStock'] = $this->isLowStock();
            $data['requiresMaintenance'] = $this->requiresMaintenance;
            $data['maintenanceDays'] = $this->maintenanceDays;
            $data['status'] = $this->status;
            $data['createdAt'] = $this->createdAt;
            $data['updatedAt'] = $this->updatedAt;
        }

        return $data;
    }
}
