<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Product\Application\UpdateProduct;

final class UpdateProductCommand
{
    /** @var string */
    public $id;

    /** @var string */
    public $name;

    /** @var string */
    public $slug;

    /** @var string|null */
    public $description;

    /** @var string|null */
    public $sku;

    /** @var string|null */
    public $categoryId;

    /** @var string|null */
    public $providerId;

    /** @var int */
    public $priceCents;

    /** @var bool */
    public $showPrice;

    /** @var int */
    public $stock;

    /** @var int */
    public $minStock;

    /** @var bool */
    public $featured;

    /** @var bool */
    public $requiresMaintenance;

    /** @var int */
    public $maintenanceDays;

    /** @var string */
    public $status;

    public function __construct(
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
        string $status
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
    }
}
