<?php

declare(strict_types=1);

namespace Phalcon\Phql\Tests\Unit\Scanner;

use Phalcon\Phql\Scanner\ScannerStatus;
use Phalcon\Phql\Tests\AbstractUnitTestCase;

final class ScannerStatusTest extends AbstractUnitTestCase
{
    public function testCaseValues(): void
    {
        $this->assertSame(-1, ScannerStatus::EOF->value);
        $this->assertSame(-2, ScannerStatus::ERR->value);
        $this->assertSame(-3, ScannerStatus::IMPOSSIBLE->value);
        $this->assertSame(0, ScannerStatus::OK->value);
    }

    public function testFromInt(): void
    {
        $this->assertSame(ScannerStatus::EOF, ScannerStatus::from(-1));
        $this->assertSame(ScannerStatus::ERR, ScannerStatus::from(-2));
        $this->assertSame(ScannerStatus::IMPOSSIBLE, ScannerStatus::from(-3));
        $this->assertSame(ScannerStatus::OK, ScannerStatus::from(0));
    }

    public function testTryFromUnknown(): void
    {
        $this->assertNull(ScannerStatus::tryFrom(99));
    }
}
