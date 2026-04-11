<?php

declare(strict_types=1);

namespace Phalcon\Phql\Tests\Unit\Scanner;

use Phalcon\Phql\Scanner\Opcode;
use Phalcon\Phql\Scanner\State;
use Phalcon\Phql\Tests\AbstractUnitTestCase;

final class StateTest extends AbstractUnitTestCase
{
    public function testClearActiveToken(): void
    {
        $state = new State('SELECT');
        $state->setActiveToken(Opcode::SELECT);
        $state->setActiveToken(null);

        $this->assertNull($state->getActiveToken());
    }

    public function testConstruction(): void
    {
        $state = new State('SELECT');

        $this->assertSame(6, $state->getBufferLength());
        $this->assertSame(6, $state->getStartLength());
        $this->assertSame(0, $state->getCursor());
        $this->assertSame('S', $state->getStart());
        $this->assertNull($state->getActiveToken());
    }

    public function testEmptyBuffer(): void
    {
        $state = new State('');

        $this->assertSame(0, $state->getBufferLength());
        $this->assertNull($state->getStart());
    }

    public function testIncrementStart(): void
    {
        $state = new State('SELECT');
        $state->incrementStart(1);

        $this->assertSame(1, $state->getCursor());
        $this->assertSame('E', $state->getStart());
    }

    public function testNoSetEndMethod(): void
    {
        $state = new State('SELECT');
        /** @phpstan-ignore function.impossibleType */
        $this->assertFalse(method_exists($state, 'setEnd'));
    }

    public function testSetActiveToken(): void
    {
        $state = new State('SELECT');
        $state->setActiveToken(Opcode::SELECT);

        $this->assertSame(Opcode::SELECT, $state->getActiveToken());
    }

    public function testSetCursor(): void
    {
        $state = new State('SELECT');
        $state->setCursor(3);

        $this->assertSame(3, $state->getCursor());
        $this->assertSame('E', $state->getStart());
    }
}
