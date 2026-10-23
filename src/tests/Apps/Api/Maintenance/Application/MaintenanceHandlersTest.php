<?php

declare(strict_types=1);

use Gaby\Apps\Api\Maintenance\Application\GetCalendar\GetCalendarHandler;
use Gaby\Apps\Api\Maintenance\Application\GetCalendar\GetCalendarQuery;
use Gaby\Apps\Api\Maintenance\Application\MarkNotified\MarkNotifiedCommand;
use Gaby\Apps\Api\Maintenance\Application\MarkNotified\MarkNotifiedHandler;
use Gaby\Apps\Api\Maintenance\Application\SnoozeReminder\SnoozeReminderCommand;
use Gaby\Apps\Api\Maintenance\Application\SnoozeReminder\SnoozeReminderHandler;
use Gaby\Apps\Api\Maintenance\Domain\Exception\MaintenanceReminderNotFoundException;
use Gaby\Apps\Api\Maintenance\Domain\MaintenanceReminder;
use Gaby\Tests\Apps\Api\Maintenance\Doubles\InMemoryMaintenanceRepository;

function maintenanceFixture(string $id, string $purchaseDate, ?string $notifiedAt = null): MaintenanceReminder
{
    return new MaintenanceReminder(
        $id,
        'invoice-' . $id,
        'FAC-' . $id,
        'customer-' . $id,
        'Cliente ' . $id,
        '3815550000',
        'product-1',
        'Recarga de matafuego',
        $purchaseDate,
        365,
        $notifiedAt
    );
}

function maintenanceDaysAgo(int $days): string
{
    return (new DateTimeImmutable('today'))->modify('-' . $days . ' days')->format('Y-m-d');
}

test('devuelve los avisos del rango y los atrasados por separado', function (): void {
    $today = date('Y-m-d');
    $from = (new DateTimeImmutable('today'))->modify('-7 days')->format('Y-m-d');
    $to = (new DateTimeImmutable('today'))->modify('+7 days')->format('Y-m-d');

    $repository = new InMemoryMaintenanceRepository([
        maintenanceFixture('hoy', maintenanceDaysAgo(365)),
        maintenanceFixture('atrasado', maintenanceDaysAgo(400)),
        maintenanceFixture('lejano', maintenanceDaysAgo(100)),
        maintenanceFixture('avisado', maintenanceDaysAgo(365), $today),
    ]);

    $result = (new GetCalendarHandler($repository))->handle(new GetCalendarQuery($from, $to));

    $ids = array_map(function (MaintenanceReminder $reminder): string {
        return $reminder->id();
    }, $result['items']);

    expect($result['today'])->toBe($today);
    expect($ids)->toContain('hoy');
    expect($ids)->toContain('avisado');
    expect($ids)->not->toContain('lejano');
    expect($ids)->not->toContain('atrasado');
    expect(array_map(function (MaintenanceReminder $reminder): string {
        return $reminder->id();
    }, $result['overdue']))->toBe(['atrasado']);
});

test('marca un aviso como avisado y es idempotente', function (): void {
    $repository = new InMemoryMaintenanceRepository([maintenanceFixture('uno', maintenanceDaysAgo(365))]);
    $handler = new MarkNotifiedHandler($repository);

    $handler->handle(new MarkNotifiedCommand('uno'));
    $handler->handle(new MarkNotifiedCommand('uno'));

    expect($repository->findById('uno')->notifiedAt())->toBe(date('Y-m-d'));
    expect($repository->findById('uno')->status(date('Y-m-d')))->toBe(MaintenanceReminder::STATUS_NOTIFIED);
});

test('pospone un aviso atrasado desde hoy', function (): void {
    $repository = new InMemoryMaintenanceRepository([maintenanceFixture('atrasado', maintenanceDaysAgo(400))]);
    $handler = new SnoozeReminderHandler($repository);

    $handler->handle(new SnoozeReminderCommand('atrasado', 7));

    $expected = (new DateTimeImmutable('today'))->modify('+7 days')->format('Y-m-d');
    expect($repository->findById('atrasado')->notifyDate())->toBe($expected);
});

test('rechaza avisos inexistentes', function (): void {
    $repository = new InMemoryMaintenanceRepository();

    expect(function () use ($repository): void {
        (new MarkNotifiedHandler($repository))->handle(new MarkNotifiedCommand('nope'));
    })->toThrow(MaintenanceReminderNotFoundException::class);

    expect(function () use ($repository): void {
        (new SnoozeReminderHandler($repository))->handle(new SnoozeReminderCommand('nope', 7));
    })->toThrow(MaintenanceReminderNotFoundException::class);
});
