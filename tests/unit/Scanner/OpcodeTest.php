<?php

declare(strict_types=1);

namespace Phalcon\Phql\Tests\Unit\Scanner;

use Phalcon\Phql\Scanner\Opcode;
use Phalcon\Phql\Tests\AbstractUnitTestCase;

final class OpcodeTest extends AbstractUnitTestCase
{
    public function testFromInt(): void
    {
        $this->assertSame(Opcode::SELECT, Opcode::from(309));
        $this->assertSame(Opcode::IDENTIFIER, Opcode::from(265));
    }

    public function testKeyOpcodeValues(): void
    {
        $this->assertSame(43, Opcode::ADD->value);
        $this->assertSame(257, Opcode::IGNORE->value);
        $this->assertSame(258, Opcode::INTEGER->value);
        $this->assertSame(259, Opcode::DOUBLE->value);
        $this->assertSame(260, Opcode::STRING->value);
        $this->assertSame(265, Opcode::IDENTIFIER->value);
        $this->assertSame(300, Opcode::UPDATE->value);
        $this->assertSame(302, Opcode::WHERE->value);
        $this->assertSame(303, Opcode::DELETE->value);
        $this->assertSame(304, Opcode::FROM->value);
        $this->assertSame(306, Opcode::INSERT->value);
        $this->assertSame(309, Opcode::SELECT->value);
        $this->assertSame(322, Opcode::NULL->value);
        $this->assertSame(334, Opcode::TRUE->value);
        $this->assertSame(335, Opcode::FALSE->value);
        $this->assertSame(350, Opcode::FCALL->value);
        $this->assertSame(352, Opcode::STARALL->value);
        $this->assertSame(353, Opcode::DOMAINALL->value);
        $this->assertSame(354, Opcode::EXPR->value);
        $this->assertSame(355, Opcode::QUALIFIED->value);
    }

    public function testLabelFallbackToName(): void
    {
        $this->assertSame('SELECT', Opcode::SELECT->label());
        $this->assertSame('FROM', Opcode::FROM->label());
        $this->assertSame('WHERE', Opcode::WHERE->label());
        $this->assertSame('IDENTIFIER', Opcode::IDENTIFIER->label());
        $this->assertSame('INTEGER', Opcode::INTEGER->label());
    }

    public function testLabelOperators(): void
    {
        $this->assertSame('+', Opcode::ADD->label());
        $this->assertSame('-', Opcode::SUB->label());
        $this->assertSame('*', Opcode::MUL->label());
        $this->assertSame('/', Opcode::DIV->label());
        $this->assertSame('%', Opcode::MOD->label());
        $this->assertSame('=', Opcode::EQUALS->label());
        $this->assertSame('<', Opcode::LESS->label());
        $this->assertSame('>', Opcode::GREATER->label());
        $this->assertSame('<=', Opcode::LESSEQUAL->label());
        $this->assertSame('>=', Opcode::GREATEREQUAL->label());
        $this->assertSame('!', Opcode::NOT->label());
        $this->assertSame('<>', Opcode::NOTEQUALS->label());
        $this->assertSame('&', Opcode::BITWISE_AND->label());
        $this->assertSame('|', Opcode::BITWISE_OR->label());
        $this->assertSame('~', Opcode::BITWISE_NOT->label());
        $this->assertSame('^', Opcode::BITWISE_XOR->label());
        $this->assertSame(':', Opcode::COLON->label());
        $this->assertSame('.', Opcode::DOT->label());
        $this->assertSame('(', Opcode::PARENTHESES_OPEN->label());
        $this->assertSame(')', Opcode::PARENTHESES_CLOSE->label());
    }

    public function testSingleCharOpcodeValues(): void
    {
        $this->assertSame(43, Opcode::ADD->value);      // ord('+')
        $this->assertSame(45, Opcode::SUB->value);      // ord('-')
        $this->assertSame(42, Opcode::MUL->value);      // ord('*')
        $this->assertSame(47, Opcode::DIV->value);      // ord('/')
        $this->assertSame(37, Opcode::MOD->value);      // ord('%')
        $this->assertSame(61, Opcode::EQUALS->value);   // ord('=')
        $this->assertSame(60, Opcode::LESS->value);     // ord('<')
        $this->assertSame(62, Opcode::GREATER->value);  // ord('>')
        $this->assertSame(33, Opcode::NOT->value);      // ord('!')
        $this->assertSame(46, Opcode::DOT->value);      // ord('.')
        $this->assertSame(58, Opcode::COLON->value);    // ord(':')
        $this->assertSame(40, Opcode::PARENTHESES_OPEN->value);   // ord('(')
        $this->assertSame(41, Opcode::PARENTHESES_CLOSE->value);  // ord(')')
    }

    public function testTryFromUnknown(): void
    {
        $this->assertNull(Opcode::tryFrom(9999));
    }
}
