<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Message;

class TextMessage extends AbstractMessage
{
    const TYPE_CODE = 0x01;

    /**
     * @var string
     */
    private $text;

    /**
     * @param string $text
     */
    public function __construct(string $text)
    {
        $this->text = $text;
    }

    /**
     * @return string text
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->text;
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
