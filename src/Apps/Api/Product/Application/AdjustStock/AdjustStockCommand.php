<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Product\Application\AdjustStock;

final class AdjustStockCommand
{
    /** @var string */
    public $id;

    /** @var int */
    public $delta;

    public function __construct(string $id, int $delta)
    {
        $this->id = $id;
        $this->delta = $delta;
    }
}
