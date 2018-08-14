<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

namespace Threema\MsgApi\Commands;

use Threema\MsgApi\Commands\Results\DownloadFileResult;

class DownloadFile implements CommandInterface
{
    /**
     * @var string
     */
    private $blobId;

    /**
     * @param string $blobId
     */
    public function __construct($blobId)
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
     * @return DownloadFileResult
     */
    public function parseResult(int $httpCode, string $response): \Threema\MsgApi\Commands\Results\Result
    {
        return new DownloadFileResult($httpCode, $response);
    }
}
