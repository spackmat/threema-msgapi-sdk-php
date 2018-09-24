<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Message;

use Threema\MsgApi\Exceptions\BadMessageException;

/**
 * This is not documented on the Threema Gateway api page.
 *
 * The format is
 * <latitude>,<longitude>[,<accuracy>][\nPOI name][\nPOI address]
 * latitude/longitude: decimal degrees
 * accuracy: meters (optional)
 * POI address: to embed newlines into the POI address, replace them with "\n" (optional)
 *
 * (from personal email communication with Threema Support 17 Aug 2018)
 *
 * Note: we use strings rather than floats here to ensure there are no rounding issues with the original values
 */
class LocationMessage extends AbstractMessage
{
    const TYPE_CODE = 0x10;

    /** @var string decimal degrees */
    private $latitude;

    /** @var string decimal degrees */
    private $longitude;

    /** @var int accuracy, metres, optional */
    private $accuracy = 0;

    /** @var string[] lines of the address */
    private $address = [];

    public function __construct(string $latitude, string $longitude, int $accuracy = 0, array $address = [])
    {
        $this->latitude  = $latitude;
        $this->longitude = $longitude;
        $this->accuracy  = $accuracy;
        $this->address   = array_filter($address);
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
     * @return int
     */
    public function getAccuracy(): int
    {
        return $this->accuracy;
    }

    /**
     * @return string[]
     */
    public function getAddress(): array
    {
        return $this->address;
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
