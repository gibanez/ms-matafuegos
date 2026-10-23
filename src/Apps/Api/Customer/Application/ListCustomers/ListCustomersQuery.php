<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Customer\Application\ListCustomers;

final class ListCustomersQuery
{
    /** @var string|null */
    public $q;

    public function __construct(?string $q = null)
    {
        $this->q = $q;
    }
}
