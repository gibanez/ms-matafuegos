<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Migration\Domain\Exception;

use RuntimeException;

final class MigrationFailedException extends RuntimeException
{
    /** @var string */
    private $detail;

    /** @var string */
    private $output;

    private function __construct(string $detail, string $output)
    {
        parent::__construct('Migration failed.');

        $this->detail = $detail;
        $this->output = $output;
    }

    public static function create(string $detail, string $output = ''): self
    {
        return new self($detail, $output);
    }

    public function detail(): string
    {
        return $this->detail;
    }

    public function output(): string
    {
        return $this->output;
    }
}
