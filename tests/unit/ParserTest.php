<?php

declare(strict_types=1);

namespace Phalcon\Phql\Tests\Unit;

use Phalcon\Phql\Exception;
use Phalcon\Phql\Parser;
use Phalcon\Phql\Scanner\Opcode;
use Phalcon\Phql\Tests\AbstractUnitTestCase;

final class ParserTest extends AbstractUnitTestCase
{
    public function testLiteralsDisabledBlocksDouble(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Literals are disabled in PHQL statements');

        (new Parser())->setEnableLiterals(false)->parse('SELECT 1.5 FROM Invoices');
    }

    public function testLiteralsDisabledBlocksFalse(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Literals are disabled in PHQL statements');

        (new Parser())->setEnableLiterals(false)->parse('SELECT FALSE FROM Invoices');
    }

    public function testLiteralsDisabledBlocksHinteger(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Literals are disabled in PHQL statements');

        (new Parser())->setEnableLiterals(false)->parse('SELECT 0xFF FROM Invoices');
    }

    public function testLiteralsDisabledBlocksInteger(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Literals are disabled in PHQL statements');

        (new Parser())->setEnableLiterals(false)->parse('SELECT 1 FROM Invoices');
    }

    public function testLiteralsDisabledBlocksString(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Literals are disabled in PHQL statements');

        (new Parser())->setEnableLiterals(false)->parse("SELECT 'hello' FROM Invoices");
    }

    public function testLiteralsDisabledBlocksTrue(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Literals are disabled in PHQL statements');

        (new Parser())->setEnableLiterals(false)->parse('SELECT TRUE FROM Invoices');
    }

    public function testLiteralsEnabledByDefault(): void
    {
        // Integer literal should parse without error when literals are enabled (default)
        $result = (new Parser())->parse('SELECT 1 FROM Invoices');
        $this->assertIsArray($result);
    }

    public function testParseEmptyStringThrows(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('PHQL statement cannot be NULL');

        (new Parser())->parse('');
    }

    public function testParseSimpleSelect(): void
    {
        $result = (new Parser())->parse('SELECT * FROM Invoices');

        $this->assertIsArray($result);
        $this->assertSame(Opcode::SELECT->value, $result['type']);
        $this->assertArrayHasKey('select', $result);
    }

    public function testScannerErrorLongMessage(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/Scanning error before/');
        $this->expectExceptionMessageMatches('/\.\.\./');

        (new Parser())->parse('#' . str_repeat('x', 20));
    }

    public function testScannerErrorShortMessage(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/Scanning error before/');

        (new Parser())->parse('#');
    }

    public function testSetEnableLiteralsChaining(): void
    {
        // Should not throw — fluent chaining works
        $result = (new Parser())->setEnableLiterals(true)->parse('SELECT * FROM Invoices');
        $this->assertIsArray($result);
    }

    public function testSetEnableLiteralsReturnsSelf(): void
    {
        $parser = new Parser();
        $result = $parser->setEnableLiterals(false);

        $this->assertSame($parser, $result);
    }

    public function testThrowsPhqlException(): void
    {
        try {
            (new Parser())->parse('');
        } catch (\Throwable $e) {
            $this->assertInstanceOf(Exception::class, $e);
            $this->assertNotInstanceOf(\RuntimeException::class, $e);
        }
    }

    public function testUnknownOpcodeThrows(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/Unknown opcode/');

        (new Parser())->parse('SELECT : FROM Invoices');
    }
}
