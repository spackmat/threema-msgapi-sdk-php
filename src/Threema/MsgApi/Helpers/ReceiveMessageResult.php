<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Helpers;

use Threema\MsgApi\Messages\ThreemaMessage;

class ReceiveMessageResult
{
    /**
     * @var ThreemaMessage
     */
    private $threemaMessage;

    /**
     * @var string[]
     */
    private $files = [];

    /**
     * @var string[]
     */
    private $errors = [];

    /**
     * @var string
     */
    private $messageId;

    /**
     * @param string         $messageId
     * @param ThreemaMessage $threemaMessage
     */
    public function __construct(string $messageId, ThreemaMessage $threemaMessage)
    {
        $this->threemaMessage = $threemaMessage;
        $this->messageId      = $messageId;
    }

    /**
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function addError(string $message): self
    {
        $this->errors[] = $message;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return null === $this->errors || count($this->errors) == 0;
    }

    /**
     * @param string $key
     * @param string $file
     * @return $this
     */
    public function addFile(string $key, string $file): self
    {
        $this->files[$key] = $file;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return ThreemaMessage
     */
    public function getThreemaMessage(): ThreemaMessage
    {
        return $this->threemaMessage;
    }

    /**
     * @return string[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }
}
