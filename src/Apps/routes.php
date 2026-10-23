<?php

declare(strict_types=1);

use Slim\App;

return static function (App $app): void {
    require __DIR__ . '/Api/routes.php';
};
