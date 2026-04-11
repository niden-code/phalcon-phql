<?php

declare(strict_types=1);

namespace Phalcon\Phql\Tests\Unit\Scanner;

use Phalcon\Phql\Scanner\Opcode;
use Phalcon\Phql\Scanner\Token;
use Phalcon\Phql\Tests\AbstractUnitTestCase;

final class TokenTest extends AbstractUnitTestCase
{
    public function testConstructionWithAllValues(): void
    {
        $token = new Token(Opcode::SELECT, null, 6);

        $this->assertSame(Opcode::SELECT, $token->opcode);
        $this->assertNull($token->value);
        $this->assertSame(6, $token->length);
    }

    public function testConstructionWithValue(): void
    {
        $token = new Token(Opcode::IDENTIFIER, 'Invoices', 8);

        $this->assertSame(Opcode::IDENTIFIER, $token->opcode);
        $this->assertSame('Invoices', $token->value);
        $this->assertSame(8, $token->length);
    }

    public function testDefaultConstruction(): void
    {
        $token = new Token();

        $this->assertNull($token->opcode);
        $this->assertNull($token->value);
        $this->assertSame(0, $token->length);
    }

    public function testIsReadonly(): void
    {
        $token = new Token(Opcode::SELECT, null, 6);

        $this->expectException(\Error::class);
        // @phpstan-ignore-next-line
        $token->opcode = Opcode::FROM;
    }
}
