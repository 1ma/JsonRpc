<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Internal;

use TypeError;
use UMA\JsonRpc\Procedure;

final class Assert
{
    /**
     * Throws a TypeError exception if $thingy is not a Procedure.
     *
     * @throws TypeError
     */
    public static function isProcedure(Procedure $thingy): Procedure
    {
        return $thingy;
    }
}
