<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

namespace Threema\MsgApi\Commands\Results;

class CapabilityResult extends Result
{
    const IMAGE = 'image';
    const TEXT  = 'text';
    const VIDEO = 'video';
    const AUDIO = 'audio';
    const FILE  = 'file';

    /**
     * @var string[]
     */
    private $capabilities = [];

    /**
     * @return string[]
     */
    public function getCapabilities()
    {
        return $this->capabilities;
    }

    /**
     * the threema id can receive text
     * @return bool
     */
    public function canText()
    {
        return $this->can(self::TEXT);
    }

    /**
     * the threema id can receive images
     * @return bool
     */
    public function canImage()
    {
        return $this->can(self::IMAGE);
    }

    /**
     * the threema id can receive videos
     * @return bool
     */
    public function canVideo()
    {
        return $this->can(self::VIDEO);
    }

    /**
     * the threema id can receive files
     * @return bool
     */
    public function canAudio()
    {
        return $this->can(self::AUDIO);
    }

    /**
     * the threema id can receive files
     * @return bool
     */
    public function canFile()
    {
        return $this->can(self::FILE);
    }

    public function can($key)
    {
        return in_array($key, $this->capabilities);
    }

    /**
     * @param string $response
     */
    protected function processResponse(string $response)
    {
        $this->capabilities = array_unique(array_filter(explode(',', $response ?? '')));
    }

    /**
     * @param int $httpCode
     * @return string
     */
    protected function getErrorMessageByErrorCode(int $httpCode): string
    {
        switch ($httpCode) {
            case 401:
                return 'API identity or secret incorrect';
            case 404:
                return 'No matching ID found';
            case 500:
                return 'A temporary internal server error has occurred';
            default:
                return 'Unknown error';
        }
    }
}
