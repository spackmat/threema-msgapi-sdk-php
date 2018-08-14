<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Messages;

class FileMessage extends ThreemaMessage
{
    const TYPE_CODE = 0x17;

    /**
     * @var string
     */
    private $blobId;

    /**
     * @var string
     */
    private $thumbnailBlobId;

    /**
     * @var string
     */
    private $encryptionKey;

    /**
     * @var string
     */
    private $mimeType;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var int
     */
    private $size;

    /**
     * @param string $blobId
     * @param string $thumbnailBlobId
     * @param string $encryptionKey
     * @param string $mimeType
     * @param string $filename
     * @param int    $size
     */
    public function __construct(string $blobId, string $thumbnailBlobId, string $encryptionKey, string $mimeType,
        string $filename, int $size)
    {
        $this->blobId          = $blobId;
        $this->thumbnailBlobId = $thumbnailBlobId;
        $this->encryptionKey   = $encryptionKey;
        $this->mimeType        = $mimeType;
        $this->filename        = $filename;
        $this->size            = $size;
    }

    /**
     * @return string
     */
    public function getBlobId()
    {
        return $this->blobId;
    }

    /**
     * @return string
     */
    public function getEncryptionKey(): string
    {
        return $this->encryptionKey;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getThumbnailBlobId(): string
    {
        return $this->thumbnailBlobId;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'file message';
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
