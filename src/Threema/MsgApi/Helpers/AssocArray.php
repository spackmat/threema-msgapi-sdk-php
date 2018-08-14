<?php
/**
 * @author    Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */

declare(strict_types=1);

namespace Threema\MsgApi\Helpers;

use Threema\MsgApi\Exceptions\Exception;

class AssocArray
{
    /**
     * @var array
     */
    private $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        //be sure a array is set
        $this->data = null !== $data ? $data : [];
    }

    /**
     * @param string $string
     * @param array  $requiredKeys
     * @return AssocArray
     * @throws Exception
     */
    public static final function byJsonString($string, array $requiredKeys): AssocArray
    {
        $v = json_decode($string, true);
        if (null === $v || false === $v) {
            throw new Exception('invalid json string');
        }

        //validate array first
        foreach ($requiredKeys as $requiredKey) {
            if (!isset($v[$requiredKey])) {
                throw new Exception('required key ' . $requiredKey . ' is not present');
            }
        }
        return new AssocArray($v);
    }

    /**
     * @param string     $key
     * @param mixed|null $defaultValue
     * @return mixed|null return the key value or the default value
     */
    public function getValue($key, $defaultValue = null)
    {
        return $this->data[$key] ?? $defaultValue;
    }
}
