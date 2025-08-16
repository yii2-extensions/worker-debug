<?php

declare(strict_types=1);

namespace yii2\extensions\debug\tests\support\stub;

final class TimeFunctions
{
    private static float|null $mockedMicrotime = null;

    public static function clearMockedMicrotime(): void
    {
        self::$mockedMicrotime = null;
    }

    public static function microtime(bool $as_float = false): float|string
    {
        if (self::$mockedMicrotime !== null) {
            return $as_float ? self::$mockedMicrotime : (string) self::$mockedMicrotime;
        }

        return \microtime($as_float);
    }

    public static function reset(): void
    {
        self::clearMockedMicrotime();
    }

    public static function setMockedMicrotime(float $time): void
    {
        self::$mockedMicrotime = $time;
    }
}
