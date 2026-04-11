<?php

declare(strict_types=1);

namespace Phalcon\Phql\Scanner;

final class Token
{
    public function __construct(
        public readonly ?Opcode $opcode = null,
        public readonly ?string $value = null,
        public readonly int $length = 0,
    ) {
    }
}
