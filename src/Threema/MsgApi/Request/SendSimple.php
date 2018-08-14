<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Request;

use Threema\MsgApi\Response\SendSimpleResponse;

class SendSimple implements RequestInterface
{
    /** @var string */
    private $text;

    /** @var string */
    private $to;

    /** @var string */
    private $address;

    protected function __construct(string $to, string $address, string $text)
    {
        $this->text    = $text;
        $this->to      = $to;
        $this->address = $address;
    }

    public static function toThreemaID(string $threemaID, string $text): self
    {
        return new self('to', strtoupper($threemaID), $text);
    }

    public static function toEmail(string $email, string $text): self
    {
        return new self('email', strtolower(trim($email)), $text);
    }

    public static function toPhoneNo(string $phoneNo, string $text): self
    {
        return new self('phone', preg_replace('/[^0-9]/', '', $phoneNo), $text);
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        $p[$this->to] = $this->address;
        $p['text']    = $this->text;
        return $p;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return 'send_simple';
    }

    /**
     * @param int    $httpCode
     * @param string $response
     * @return SendSimpleResponse
     */
    public function parseResult(int $httpCode, string $response): \Threema\MsgApi\Response\Response
    {
        return new SendSimpleResponse($httpCode, $response);
    }
}
