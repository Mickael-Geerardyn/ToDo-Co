<?php

/*
 * This file is part of composer/semver.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Composer\Semver;

use Composer\Semver\Constraint\Constraint;

class Interval
{
    public function __construct(private readonly \Composer\Semver\Constraint\Constraint $start, private readonly \Composer\Semver\Constraint\Constraint $end)
    {
    }

    /**
     * @return Constraint
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return Constraint
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @return Constraint
     */
    public static function fromZero()
    {
        static $zero;

        if (null === $zero) {
            $zero = new Constraint('>=', '0.0.0.0-dev');
        }

        return $zero;
    }

    /**
     * @return Constraint
     */
    public static function untilPositiveInfinity()
    {
        static $positiveInfinity;

        if (null === $positiveInfinity) {
            $positiveInfinity = new Constraint('<', PHP_INT_MAX.'.0.0.0');
        }

        return $positiveInfinity;
    }

    /**
     * @return self
     */
    public static function any()
    {
        return new self(self::fromZero(), self::untilPositiveInfinity());
    }

    /**
     * @return array{'names': string[], 'exclude': bool}
     */
    public static function anyDev()
    {
        // any == exclude nothing
        return ['names' => [], 'exclude' => true];
    }

    /**
     * @return array{'names': string[], 'exclude': bool}
     */
    public static function noDev()
    {
        // nothing == no names included
        return ['names' => [], 'exclude' => false];
    }
}
