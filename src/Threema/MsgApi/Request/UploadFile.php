<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Request;

use Threema\MsgApi\Response\Response;
use Threema\MsgApi\Response\UploadFileResponse;

class UploadFile implements MultiPartRequestInterface
{
    /**
     * @var string
     */
    private $encryptedFileData;

    /**
     * @param string $encryptedFileData (binary) the encrypted file data
     */
    public function __construct(string $encryptedFileData)
    {
        $this->encryptedFileData = $encryptedFileData;
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
        return 'upload_blob';
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->encryptedFileData;
    }

    /**
     * @param int    $httpCode
     * @param string $response
     * @return UploadFileResponse
     */
    public function parseResult(int $httpCode, string $response): Response
    {
        return new UploadFileResponse($httpCode, $response);
    }
}
