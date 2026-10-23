<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Maintenance\Application\GetCalendar;

final class GetCalendarQuery
{
    /** @var string */
    public $from;

    /** @var string */
    public $to;

    public function __construct(string $from, string $to)
    {
        $this->from = $from;
        $this->to = $to;
    }
}
