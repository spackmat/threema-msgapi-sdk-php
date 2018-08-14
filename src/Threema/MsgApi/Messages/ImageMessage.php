<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

namespace Threema\MsgApi\Messages;

class ImageMessage extends ThreemaMessage
{
    const TYPE_CODE = 0x02;

    /**
     * @var string
     */
    private $blobId;

    /**
     * @var int
     */
    private $length;

    /**
     * @var string
     */
    private $nonce;

    /**
     * @param string $blobId
     * @param int    $length
     * @param string $nonce
     */
    public function __construct(string $blobId, int $length, string $nonce)
    {
        $this->blobId = $blobId;
        $this->length = $length;
        $this->nonce  = $nonce;
    }

    /**
     * @return string
     */
    public function getBlobId()
    {
        return $this->blobId;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @return string
     */
    public function getNonce()
    {
        return $this->nonce;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'image message';
    }

    /**
     * Get the message type code of this message.
     *
     * @return int message type code
     */
    public final function getTypeCode(): int
    {
        return self::TYPE_CODE;
    }
}
