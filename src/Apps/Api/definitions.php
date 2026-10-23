<?php

declare(strict_types=1);

return array_merge(
    require __DIR__ . '/Admin/Infrastructure/definitions.php',
    require __DIR__ . '/Auth/Infrastructure/definitions.php',
    require __DIR__ . '/Page/Infrastructure/definitions.php',
    require __DIR__ . '/Inquiry/Infrastructure/definitions.php',
    require __DIR__ . '/Category/Infrastructure/definitions.php',
    require __DIR__ . '/Provider/Infrastructure/definitions.php',
    require __DIR__ . '/Product/Infrastructure/definitions.php',
    require __DIR__ . '/Media/Infrastructure/definitions.php',
    require __DIR__ . '/Customer/Infrastructure/definitions.php',
    require __DIR__ . '/Quote/Infrastructure/definitions.php',
    require __DIR__ . '/Invoice/Infrastructure/definitions.php',
    require __DIR__ . '/Maintenance/Infrastructure/definitions.php',
    require __DIR__ . '/Migration/Infrastructure/definitions.php'
);
