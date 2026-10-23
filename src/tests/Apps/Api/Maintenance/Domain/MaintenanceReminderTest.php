<?php

declare(strict_types=1);

use Gaby\Apps\Api\Maintenance\Domain\MaintenanceReminder;

function maintenanceReminder(string $purchaseDate, int $days = 365, ?string $notifiedAt = null, ?string $snoozedUntil = null): MaintenanceReminder
{
    return new MaintenanceReminder(
        'line-1',
        'invoice-1',
        'FAC-000001',
        'customer-1',
        'Consorcio Rivadavia',
        '3815550000',
        'product-1',
        'Recarga de matafuego',
        $purchaseDate,
        $days,
        $notifiedAt,
        $snoozedUntil
    );
}

test('calcula el vencimiento sumando los dias de mantenimiento', function (): void {
    $reminder = maintenanceReminder('2026-01-31', 365);

    expect($reminder->dueDate())->toBe('2027-01-31');
    expect($reminder->notifyDate())->toBe('2027-01-31');
});

test('marca pendiente, atrasado o avisado segun la fecha', function (): void {
    $pending = maintenanceReminder('2025-10-24');
    $overdue = maintenanceReminder('2025-10-22');
    $notified = maintenanceReminder('2025-10-22', 365, '2026-10-23');

    expect($pending->status('2026-10-23'))->toBe(MaintenanceReminder::STATUS_PENDING);
    expect($overdue->status('2026-10-23'))->toBe(MaintenanceReminder::STATUS_OVERDUE);
    expect($notified->status('2026-10-23'))->toBe(MaintenanceReminder::STATUS_NOTIFIED);
});

test('cuenta los dias restantes y los dias de atraso', function (): void {
    $tomorrow = maintenanceReminder('2025-10-24');
    $yesterday = maintenanceReminder('2025-10-22');

    expect($tomorrow->daysUntil('2026-10-23'))->toBe(1);
    expect($yesterday->daysUntil('2026-10-23'))->toBe(-1);
});

test('pospone el aviso solo cuando la nueva fecha es posterior al vencimiento', function (): void {
    $reminder = maintenanceReminder('2025-10-24');

    $reminder->snooze('2026-11-10');
    expect($reminder->notifyDate())->toBe('2026-11-10');

    $reminder->snooze('2026-10-01');
    expect($reminder->notifyDate())->toBe('2026-10-24');
});

test('al marcar avisado se limpia la posposicion', function (): void {
    $reminder = maintenanceReminder('2025-10-24', 365, null, '2026-11-10');

    $reminder->markNotified('2026-10-23');

    expect($reminder->notifiedAt())->toBe('2026-10-23');
    expect($reminder->snoozedUntil())->toBeNull();
    expect($reminder->status('2026-10-23'))->toBe(MaintenanceReminder::STATUS_NOTIFIED);
});
