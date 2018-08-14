<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

namespace Threema\MsgApi\Messages;

class DeliveryReceipt extends ThreemaMessage
{
    const TYPE_CODE = 0x80;

    public const MESSAGE_RECEIVED    = 1;
    public const MESSAGE_READ        = 2;
    public const MESSAGE_THUMBS_UP   = 3; // or accepted
    public const MESSAGE_THUMBS_DOWN = 4; // or declined

    /**
     * @var string[]
     */
    private const RECEIPT_NAMES = [
        self::MESSAGE_RECEIVED    => 'received',
        self::MESSAGE_READ        => 'read',
        self::MESSAGE_THUMBS_UP   => 'userack',
        self::MESSAGE_THUMBS_DOWN => 'userdec'];

    /**
     * the type of this receipt
     * @var int
     */
    private $receiptType;

    /**
     * list of message IDs acknowledged by this delivery receipt
     * @var string[]
     */
    private $ackedMessageIds;

    /**
     * create instance
     * @param int   $receiptType     the type of this receipt
     * @param array $ackedMessageIds list of message IDs acknowledged by this delivery receipt
     */
    public function __construct($receiptType, array $ackedMessageIds)
    {
        $this->receiptType     = $receiptType;
        $this->ackedMessageIds = $ackedMessageIds;
    }

    /**
     * Get the type of this delivery receipt as a numeric code (e.g. 1, 2, 3).
     *
     * @return int
     */
    public function getReceiptType()
    {
        return $this->receiptType;
    }

    /**
     * Get the type of this delivery receipt as a string (e.g. 'received', 'read', 'userack').
     *
     * @return string
     */
    public function getReceiptTypeName(): string
    {
        return self::RECEIPT_NAMES[$this->receiptType] ?? '';
    }

    /**
     * Get the acknowledged message ids
     * @return array
     */
    public function getAckedMessageIds()
    {
        return $this->ackedMessageIds;
    }

    /**
     * Convert to string
     *
     * @return string
     */
    public function __toString()
    {
        $str = "Delivery receipt (" . $this->getReceiptTypeName() . "): ";
        $str .= join(", ", $this->ackedMessageIds);
        return $str;
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
