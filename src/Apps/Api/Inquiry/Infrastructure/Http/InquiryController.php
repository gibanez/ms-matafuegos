<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Inquiry\Infrastructure\Http;

use Gaby\Apps\Api\Inquiry\Application\CreateInquiry\CreateInquiryCommand;
use Gaby\Apps\Api\Inquiry\Application\CreateInquiry\CreateInquiryHandler;
use Gaby\Apps\Api\Inquiry\Application\ListInquiries\ListInquiriesHandler;
use Gaby\Apps\Api\Inquiry\Application\ListInquiries\ListInquiriesQuery;
use Gaby\Apps\Api\Inquiry\Domain\Inquiry;
use Gaby\Shared\Http\JsonResponseTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class InquiryController
{
    use JsonResponseTrait;

    /** @var CreateInquiryHandler */
    private $createInquiry;

    /** @var ListInquiriesHandler */
    private $listInquiries;

    public function __construct(CreateInquiryHandler $createInquiry, ListInquiriesHandler $listInquiries)
    {
        $this->createInquiry = $createInquiry;
        $this->listInquiries = $listInquiries;
    }

    public function create(Request $request, Response $response): Response
    {
        $data = (array) $request->getParsedBody();
        $name = isset($data['name']) ? trim((string) $data['name']) : '';
        $email = isset($data['email']) ? trim((string) $data['email']) : '';
        $phone = isset($data['phone']) ? trim((string) $data['phone']) : '';
        $message = isset($data['message']) ? trim((string) $data['message']) : '';

        if ($name === '' || $email === '' || $phone === '' || $message === '') {
            return $this->json(
                $response,
                ['error' => 'Name, email, WhatsApp number and message are required.'],
                422
            );
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return $this->json($response, ['error' => 'Invalid email address.'], 422);
        }

        $phoneDigits = preg_replace('/\D+/', '', $phone);

        if ($phoneDigits === null || strlen($phoneDigits) < 8 || strlen($phoneDigits) > 15) {
            return $this->json($response, ['error' => 'Invalid WhatsApp number.'], 422);
        }

        $inquiry = $this->createInquiry->handle(
            new CreateInquiryCommand($name, $email, $phone, $message)
        );

        return $this->json($response, $inquiry->toArray(), 201);
    }

    public function list(Request $request, Response $response): Response
    {
        $inquiries = $this->listInquiries->handle(new ListInquiriesQuery());

        return $this->json($response, [
            'inquiries' => array_map(function (Inquiry $inquiry): array {
                return $inquiry->toArray();
            }, $inquiries),
        ]);
    }
}
