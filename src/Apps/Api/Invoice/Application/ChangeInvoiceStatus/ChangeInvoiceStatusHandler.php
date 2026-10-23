<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Invoice\Application\ChangeInvoiceStatus;

use Gaby\Apps\Api\Invoice\Domain\Exception\InvoiceNotFoundException;
use Gaby\Apps\Api\Invoice\Domain\Invoice;
use Gaby\Apps\Api\Invoice\Domain\InvoiceRepositoryInterface;
use Gaby\Apps\Api\Product\Domain\ProductRepositoryInterface;
use Throwable;

final class ChangeInvoiceStatusHandler
{
    /** @var InvoiceRepositoryInterface */
    private $invoices;

    /** @var ProductRepositoryInterface */
    private $products;

    public function __construct(
        InvoiceRepositoryInterface $invoices,
        ProductRepositoryInterface $products
    ) {
        $this->invoices = $invoices;
        $this->products = $products;
    }

    public function handle(ChangeInvoiceStatusCommand $command): Invoice
    {
        $invoice = $this->invoices->findById($command->id);

        if ($invoice === null) {
            throw InvoiceNotFoundException::withId($command->id);
        }

        $hadStockApplied = $invoice->hasStockApplied();
        $invoice->changeStatus($command->status, date('c'));
        $hasStockApplied = $invoice->hasStockApplied();
        $changes = [];

        if (!$hadStockApplied && $hasStockApplied) {
            foreach ($invoice->productQuantities() as $productId => $quantity) {
                $changes[$productId] = -$quantity;
            }
        } elseif ($hadStockApplied && !$hasStockApplied) {
            foreach ($invoice->productQuantities() as $productId => $quantity) {
                $changes[$productId] = $quantity;
            }
        }

        if ($changes !== []) {
            $this->products->adjustStock($changes);
        }

        try {
            $this->invoices->save($invoice);
        } catch (Throwable $error) {
            if ($changes !== []) {
                $reverse = [];

                foreach ($changes as $productId => $delta) {
                    $reverse[$productId] = -$delta;
                }

                $this->products->adjustStock($reverse);
            }

            throw $error;
        }

        return $invoice;
    }
}
