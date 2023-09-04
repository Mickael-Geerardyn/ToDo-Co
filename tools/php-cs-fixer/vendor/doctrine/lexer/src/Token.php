<?php

declare(strict_types=1);

namespace Doctrine\Common\Lexer;

use UnitEnum;

use function in_array;

/**
 * @template T of UnitEnum|string|int
 * @template V of string|int
 */
final class Token
{
    /**
     * @param V      $value
     * @param T|null $type
     */
    public function __construct(
        public string|int $value,
        public $type,
        /**
         * The position of the token in the input string
         *
         * @readonly
         */
        public int $position
    )
    {
    }

    /** @param T ...$types */
    public function isA(...$types): bool
    {
        return in_array($this->type, $types, true);
    }
}
