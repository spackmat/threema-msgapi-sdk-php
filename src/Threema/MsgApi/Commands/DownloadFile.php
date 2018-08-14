<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Commands;

use Threema\MsgApi\Commands\Results\DownloadFileResponse;
use Threema\MsgApi\Commands\Results\Response;

class DownloadFile implements CommandInterface
{
    /**
     * @var string
     */
    private $blobId;

    /**
     * @param string $blobId
     */
    public function __construct(string $blobId)
    {
        $this->blobId = $blobId;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return 'blobs/' . $this->blobId;
    }

    /**
     * @param int    $httpCode
     * @param string $response
     * @return DownloadFileResponse
     */
    public function parseResult(int $httpCode, string $response): Response
    {
        return new DownloadFileResponse($httpCode, $response);
    }
}
