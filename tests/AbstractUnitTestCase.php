<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Phql\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

use function array_slice;
use function array_unshift;
use function call_user_func_array;
use function func_get_args;
use function is_object;

abstract class AbstractUnitTestCase extends TestCase
{
    /**
     * Calls a private or protected method.
     *
     * @throws ReflectionException
     */
    public function callProtectedMethod(
        string|object $obj,
        string $method
    ): mixed {
        $reflectionClass  = new ReflectionClass($obj);
        $reflectionMethod = $reflectionClass->getMethod($method);
        $reflectionMethod->setAccessible(true);

        if (!is_object($obj)) {
            $obj = $reflectionClass->newInstanceWithoutConstructor();
        }

        $args = array_slice(func_get_args(), 2);
        array_unshift($args, $obj);

        return call_user_func_array(
            [$reflectionMethod, 'invoke'],
            $args
        );
    }

    /**
     * Returns the value of a protected property.
     *
     * @throws ReflectionException
     */
    public function getProtectedProperty(
        object|string $obj,
        string $property
    ): mixed {
        $reflection = new ReflectionClass($obj);
        $property   = $reflection->getProperty($property);
        $property->setAccessible(true);

        return $property->getValue($obj);
    }

    /**
     * Sets a protected property.
     *
     * @throws ReflectionException
     */
    public function setProtectedProperty(
        object|string $obj,
        string $property,
        mixed $value
    ): void {
        $reflection = new ReflectionClass($obj);
        $property   = $reflection->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($obj, $value);
    }
}
