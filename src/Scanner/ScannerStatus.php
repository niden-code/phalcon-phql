<?php

declare(strict_types=1);

namespace Phalcon\Phql\Scanner;

enum ScannerStatus: int
{
    case EOF        = -1;
    case ERR        = -2;
    case IMPOSSIBLE = -3;
    case OK         = 0;
}
