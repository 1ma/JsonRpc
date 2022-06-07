<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Fixture;

use Opis\JsonSchema\IFormat;

final class PrimeNumberFormat implements IFormat
{
    public function validate($data): bool
    {
        if (!\is_int($data)) {
            return false;
        }

        $i = 2;
        while ($i*$i <= $data) {
            if ($data % $i === 0) {
                return false;
            }

            $i++;
        }

        return true;
    }
}
