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
    public function getParams()
    {
        return [];
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return 'blobs/' . $this->blobId;
    }

    /**
     * @param int    $httpCode
     * @param object $res
     * @return DownloadFileResult
     */
    public function parseResult($httpCode, $res)
    {
        return new DownloadFileResult($httpCode, $res);
    }
}
