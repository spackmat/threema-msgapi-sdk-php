<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Helpers;

class FileAnalysisResult
{
    /**
     * @var string
     */
    private $mimeType;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $path;

    /**
     * @param string $mimeType
     * @param int    $size
     * @param string $path
     */
    public function __construct(string $mimeType, int $size, string $path)
    {
        $this->mimeType = $mimeType;
        $this->size     = $size;
        $this->path     = realpath($path) ?: $path;
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
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return basename($this->path);
    }
}
