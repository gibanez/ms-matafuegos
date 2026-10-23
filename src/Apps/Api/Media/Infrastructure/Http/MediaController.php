<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Media\Infrastructure\Http;

use Gaby\Apps\Api\Media\Application\CreateMedia\CreateMediaCommand;
use Gaby\Apps\Api\Media\Application\CreateMedia\CreateMediaHandler;
use Gaby\Apps\Api\Media\Application\DeleteMedia\DeleteMediaCommand;
use Gaby\Apps\Api\Media\Application\DeleteMedia\DeleteMediaHandler;
use Gaby\Apps\Api\Media\Application\ListMedia\ListMediaHandler;
use Gaby\Apps\Api\Media\Application\ListMedia\ListMediaQuery;
use Gaby\Apps\Api\Media\Application\UpdateMediaPosition\UpdateMediaPositionCommand;
use Gaby\Apps\Api\Media\Application\UpdateMediaPosition\UpdateMediaPositionHandler;
use Gaby\Apps\Api\Media\Domain\Exception\InvalidMediaException;
use Gaby\Apps\Api\Media\Domain\Exception\MediaNotFoundException;
use Gaby\Apps\Api\Media\Domain\Exception\MediaOwnerNotFoundException;
use Gaby\Apps\Api\Media\Domain\Media;
use Gaby\Apps\Api\Media\Domain\OwnerExistenceCheckerInterface;
use Gaby\Apps\Api\Media\Infrastructure\Storage\ProductMediaStorage;
use Gaby\Shared\Http\JsonResponseTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use Throwable;

final class MediaController
{
    use JsonResponseTrait;

    /** @var CreateMediaHandler */
    private $createMedia;

    /** @var ListMediaHandler */
    private $listMedia;

    /** @var UpdateMediaPositionHandler */
    private $updatePosition;

    /** @var DeleteMediaHandler */
    private $deleteMedia;

    /** @var ProductMediaStorage */
    private $storage;

    /** @var OwnerExistenceCheckerInterface */
    private $owners;

    public function __construct(
        CreateMediaHandler $createMedia,
        ListMediaHandler $listMedia,
        UpdateMediaPositionHandler $updatePosition,
        DeleteMediaHandler $deleteMedia,
        ProductMediaStorage $storage,
        OwnerExistenceCheckerInterface $owners
    ) {
        $this->createMedia = $createMedia;
        $this->listMedia = $listMedia;
        $this->updatePosition = $updatePosition;
        $this->deleteMedia = $deleteMedia;
        $this->storage = $storage;
        $this->owners = $owners;
    }

    public function list(Request $request, Response $response, array $args): Response
    {
        $ownerId = (string) $args['id'];

        try {
            $items = $this->listMedia->handle(new ListMediaQuery(Media::OWNER_PRODUCT, $ownerId));
        } catch (MediaOwnerNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        }

        return $this->json($response, [
            'media' => array_map(function (Media $media): array {
                return $media->toArray();
            }, $items),
        ]);
    }

    public function create(Request $request, Response $response, array $args): Response
    {
        $ownerId = (string) $args['id'];

        if (!$this->owners->exists(Media::OWNER_PRODUCT, $ownerId)) {
            return $this->json($response, ['error' => 'Product not found.'], 404);
        }

        $data = (array) $request->getParsedBody();
        $position = null;

        if (isset($data['position']) && $data['position'] !== '') {
            $parsed = filter_var($data['position'], FILTER_VALIDATE_INT);

            if ($parsed === false || $parsed < 0) {
                return $this->json($response, ['error' => 'position must be a non-negative integer.'], 422);
            }

            $position = (int) $parsed;
        }

        $files = $request->getUploadedFiles();
        $file = $files['file'] ?? null;

        if (!$file instanceof UploadedFileInterface) {
            return $this->json($response, ['error' => 'file is required.'], 422);
        }

        try {
            $stored = $this->storage->store($file, $ownerId);
        } catch (InvalidMediaException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 422);
        }

        try {
            $media = $this->createMedia->handle(new CreateMediaCommand(
                Media::OWNER_PRODUCT,
                $ownerId,
                $stored['type'],
                $stored['path'],
                $stored['originalFilename'],
                $stored['mimeType'],
                $position
            ));
        } catch (MediaOwnerNotFoundException $error) {
            $this->storage->delete($stored['path']);

            return $this->json($response, ['error' => $error->getMessage()], 404);
        } catch (InvalidMediaException $error) {
            $this->storage->delete($stored['path']);

            return $this->json($response, ['error' => $error->getMessage()], 422);
        } catch (Throwable $error) {
            $this->storage->delete($stored['path']);

            throw $error;
        }

        return $this->json($response, $media->toArray(), 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data = (array) $request->getParsedBody();
        $position = filter_var($data['position'] ?? null, FILTER_VALIDATE_INT);

        if ($position === false || $position < 0) {
            return $this->json($response, ['error' => 'position must be a non-negative integer.'], 422);
        }

        try {
            $media = $this->updatePosition->handle(new UpdateMediaPositionCommand(
                (string) $args['mediaId'],
                Media::OWNER_PRODUCT,
                (string) $args['id'],
                (int) $position
            ));
        } catch (MediaNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        }

        return $this->json($response, $media->toArray());
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $this->deleteMedia->handle(new DeleteMediaCommand(
                (string) $args['mediaId'],
                Media::OWNER_PRODUCT,
                (string) $args['id']
            ));
        } catch (MediaNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        }

        return $response->withStatus(204);
    }
}
