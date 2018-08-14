<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

namespace Threema\MsgApi\Message;

/**
 * Abstract base class of messages that can be sent with end-to-end encryption via Threema.
 * @todo should be an interface
 */
abstract class AbstractMessage
{
    /**
     * Get the message type code of this message.
     *
     * @return int message type code
     */
    abstract public function getTypeCode(): int;

    /**
     * @return string
     */
    abstract public function __toString();
}
