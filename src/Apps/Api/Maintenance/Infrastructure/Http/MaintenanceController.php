<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Maintenance\Infrastructure\Http;

use DateTimeImmutable;
use Gaby\Apps\Api\Maintenance\Application\GetCalendar\GetCalendarHandler;
use Gaby\Apps\Api\Maintenance\Application\GetCalendar\GetCalendarQuery;
use Gaby\Apps\Api\Maintenance\Application\MarkNotified\MarkNotifiedCommand;
use Gaby\Apps\Api\Maintenance\Application\MarkNotified\MarkNotifiedHandler;
use Gaby\Apps\Api\Maintenance\Application\SnoozeReminder\SnoozeReminderCommand;
use Gaby\Apps\Api\Maintenance\Application\SnoozeReminder\SnoozeReminderHandler;
use Gaby\Apps\Api\Maintenance\Domain\Exception\MaintenanceReminderNotFoundException;
use Gaby\Apps\Api\Maintenance\Domain\MaintenanceReminder;
use Gaby\Shared\Http\JsonResponseTrait;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class MaintenanceController
{
    use JsonResponseTrait;

    /** @var GetCalendarHandler */
    private $getCalendar;

    /** @var MarkNotifiedHandler */
    private $markNotified;

    /** @var SnoozeReminderHandler */
    private $snoozeReminder;

    public function __construct(
        GetCalendarHandler $getCalendar,
        MarkNotifiedHandler $markNotified,
        SnoozeReminderHandler $snoozeReminder
    ) {
        $this->getCalendar = $getCalendar;
        $this->markNotified = $markNotified;
        $this->snoozeReminder = $snoozeReminder;
    }

    public function calendar(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        [$weekStart, $weekEnd] = $this->currentWeekRange();

        try {
            $from = $this->parseDate($params['from'] ?? null) ?? $weekStart;
            $to = $this->parseDate($params['to'] ?? null) ?? $weekEnd;
        } catch (InvalidArgumentException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 422);
        }

        if ($from > $to) {
            return $this->json($response, ['error' => 'from must be before or equal to to.'], 422);
        }

        $days = (int) (new DateTimeImmutable($from))->diff(new DateTimeImmutable($to))->days;

        if ($days > 120) {
            return $this->json($response, ['error' => 'The date range cannot exceed 120 days.'], 422);
        }

        $result = $this->getCalendar->handle(new GetCalendarQuery($from, $to));
        $today = (string) $result['today'];

        return $this->json($response, [
            'from' => $from,
            'to' => $to,
            'today' => $today,
            'items' => array_map(function (MaintenanceReminder $reminder) use ($today): array {
                return $reminder->toArray($today);
            }, $result['items']),
            'overdue' => array_map(function (MaintenanceReminder $reminder) use ($today): array {
                return $reminder->toArray($today);
            }, $result['overdue']),
        ]);
    }

    public function notify(Request $request, Response $response, array $args): Response
    {
        try {
            $this->markNotified->handle(new MarkNotifiedCommand((string) $args['id']));
        } catch (MaintenanceReminderNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        }

        return $response->withStatus(204);
    }

    public function snooze(Request $request, Response $response, array $args): Response
    {
        $data = (array) $request->getParsedBody();
        $days = filter_var($data['days'] ?? null, FILTER_VALIDATE_INT);

        if ($days === false || $days < 1 || $days > 365) {
            return $this->json($response, ['error' => 'days must be an integer between 1 and 365.'], 422);
        }

        try {
            $this->snoozeReminder->handle(new SnoozeReminderCommand((string) $args['id'], (int) $days));
        } catch (MaintenanceReminderNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        }

        return $response->withStatus(204);
    }

    /**
     * @param mixed $value
     */
    private function parseDate($value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $raw = trim((string) $value);
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $raw);
        $errors = DateTimeImmutable::getLastErrors();

        if ($date === false) {
            throw new InvalidArgumentException('Invalid date, expected Y-m-d.');
        }

        if (is_array($errors) && ((int) $errors['warning_count'] > 0 || (int) $errors['error_count'] > 0)) {
            throw new InvalidArgumentException('Invalid date, expected Y-m-d.');
        }

        if ($date->format('Y-m-d') !== $raw) {
            throw new InvalidArgumentException('Invalid date, expected Y-m-d.');
        }

        return $raw;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function currentWeekRange(): array
    {
        $today = new DateTimeImmutable('today');
        $offset = (int) $today->format('N') - 1;
        $start = $today->modify('-' . $offset . ' days');

        return [$start->format('Y-m-d'), $start->modify('+6 days')->format('Y-m-d')];
    }
}
