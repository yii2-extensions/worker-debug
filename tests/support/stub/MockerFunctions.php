<?php

declare(strict_types=1);

namespace yii2\extensions\debug\tests\support\stub;

/**
 * Mocks the PHP function with controlled state and inspection.
 *
 * Provides a mock implementation of the core PHP {@see \microtime()} function to enable deterministic and isolated
 * testing of time-dependent logic without side effects or reliance on the system clock.
 *
 * This class allows tests to simulate and manipulate the return value of {@see \microtime()} by maintaining internal
 * state and exposing static methods for fine-grained control.
 *
 * Key features.
 * - Mockable replacement of {@see \microtime()} for timing tests.
 * - State reset capability for test isolation and repeatability.
 *
 * @copyright Copyright (C) 2025 Terabytesoftw.
 * @license https://opensource.org/license/bsd-3-clause BSD 3-Clause License.
 */
final class MockerFunctions
{
    /**
     * Holds the mocked microtime value in seconds; when `null`, the native {@see \microtime()} is used.
     */
    private static float|null $mockedMicrotime = null;

    /**
     * Resets the mocked microtime value to its initial state.
     *
     * Sets internal {@see $mockedMicrotime} property to `null`, ensuring that subsequent calls to {@see microtime()}
     * will delegate to the native PHP implementation until a new mock value is set.
     */
    public static function clearMockedMicrotime(): void
    {
        self::$mockedMicrotime = null;
    }

    /**
     * Returns the mocked or native microtime value based on internal state.
     *
     * If a mocked microtime value is set, returns it as a float or string depending on the '$as_float' argument.
     *
     * Otherwise, delegates to the native {@see \microtime()} implementation.
     *
     * @param bool $as_float Whether to return the result as a float (`true`) or string (`false`).
     *
     * @return float|string Mocked or native microtime value, as float or string.
     */
    public static function microtime(bool $as_float = false): float|string
    {
        if (self::$mockedMicrotime !== null) {
            return $as_float ? self::$mockedMicrotime : (string) self::$mockedMicrotime;
        }

        return \microtime($as_float);
    }

    /**
     * Resets all mocked function state to initial values for test isolation.
     *
     * Invokes {@see clearMockedMicrotime()} to clear the mocked microtime value, ensuring that subsequent calls to
     * {@see microtime()} will use the native PHP implementation until a new mock value is set.
     *
     * This method is intended for use in test setup and teardown to guarantee deterministic and repeatable test
     * conditions by removing any previously set mock state.
     */
    public static function reset(): void
    {
        self::clearMockedMicrotime();
    }

    /**
     * Sets the mocked microtime value for deterministic testing.
     *
     * Assigns the specified float value to the internal {@see $mockedMicrotime} property, overriding the native
     * {@see \microtime()} return value for subsequent calls to {@see microtime()} until cleared.
     *
     * This method enables precise control over time-dependent logic in tests by simulating a fixed microtime value.
     *
     * @param float $time Mocked microtime value to be used in place of the native function.
     */
    public static function setMockedMicrotime(float $time): void
    {
        self::$mockedMicrotime = $time;
    }
}
