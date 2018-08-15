<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Message;

class LocationMessage extends AbstractMessage
{
    const TYPE_CODE = 0x16;

    /** @var string */
    private $latitude;

    /** @var string */
    private $longitude;

    public function __construct(string $latitude, string $longitude)
    {
        $this->latitude  = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * @return string
     */
    public function getLatitude(): string
    {
        return $this->latitude;
    }

    /**
     * @return string
     */
    public function getLongitude(): string
    {
        return $this->longitude;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'Location: ' . $this->latitude . ',' . $this->longitude;
    }

    /**
     * Get the message type code of this message.
     *
     * @return int message type code
     */
    public function getTypeCode(): int
    {
        return self::TYPE_CODE;
    }
}
