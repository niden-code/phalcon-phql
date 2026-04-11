<?php

declare(strict_types=1);

namespace Phalcon\Phql\Tests\Unit\Parser;

use Phalcon\Phql\Parser\Status;
use Phalcon\Phql\Scanner\State;
use Phalcon\Phql\Tests\AbstractUnitTestCase;

final class StatusTest extends AbstractUnitTestCase
{
    public function testDefaultState(): void
    {
        $state  = new State('SELECT');
        $status = new Status($state);

        $this->assertSame(Status::PHQL_PARSING_OK, $status->getStatus());
        $this->assertNull($status->getAst());
        $this->assertNull($status->getSyntaxError());
        $this->assertNull($status->getToken());
        $this->assertFalse($status->getEnableLiterals());
    }

    public function testSetAndGetAst(): void
    {
        $state  = new State('SELECT');
        $status = new Status($state);
        $ast    = ['type' => 309, 'select' => []];

        $status->setAst($ast);

        $this->assertSame($ast, $status->getAst());
    }

    public function testSetStatus(): void
    {
        $state  = new State('SELECT');
        $status = new Status($state);

        $status->setStatus(Status::PHQL_PARSING_FAILED);

        $this->assertSame(Status::PHQL_PARSING_FAILED, $status->getStatus());
    }

    public function testSetSyntaxError(): void
    {
        $state  = new State('SELECT');
        $status = new Status($state);

        $status->setSyntaxError('Unexpected token');

        $this->assertSame('Unexpected token', $status->getSyntaxError());
    }

    public function testEnableLiterals(): void
    {
        $state  = new State('SELECT');
        $status = new Status($state);

        $status->setEnableLiterals(true);

        $this->assertTrue($status->getEnableLiterals());
    }

    public function testGetState(): void
    {
        $state  = new State('SELECT');
        $status = new Status($state);

        $this->assertSame($state, $status->getState());
    }

    public function testNoGetRetMethod(): void
    {
        $state  = new State('SELECT');
        $status = new Status($state);

        $this->assertFalse(method_exists($status, 'getRet'));
        $this->assertFalse(method_exists($status, 'setRet'));
    }
}
