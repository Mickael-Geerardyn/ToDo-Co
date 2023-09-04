<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Tokenizer\Analyzer\Analysis;

/**
 * @internal
 */
final class NamespaceUseAnalysis implements StartEndTokenAwareAnalysis
{
    public const TYPE_CLASS = 1; // "classy" could be class, interface or trait
    public const TYPE_FUNCTION = 2;
    public const TYPE_CONSTANT = 3;

    public function __construct(
        /**
         * The fully qualified use namespace.
         */
        private readonly string $fullName,
        /**
         * The short version of use namespace or the alias name in case of aliased use statements.
         */
        private readonly string $shortName,
        /**
         * Is the use statement being aliased?
         */
        private readonly bool $isAliased,
        /**
         * The start index of the namespace declaration in the analyzed Tokens.
         */
        private readonly int $startIndex,
        /**
         * The end index of the namespace declaration in the analyzed Tokens.
         */
        private readonly int $endIndex,
        /**
         * The type of import: class, function or constant.
         */
        private readonly int $type
    )
    {
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getShortName(): string
    {
        return $this->shortName;
    }

    public function isAliased(): bool
    {
        return $this->isAliased;
    }

    public function getStartIndex(): int
    {
        return $this->startIndex;
    }

    public function getEndIndex(): int
    {
        return $this->endIndex;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function isClass(): bool
    {
        return self::TYPE_CLASS === $this->type;
    }

    public function isFunction(): bool
    {
        return self::TYPE_FUNCTION === $this->type;
    }

    public function isConstant(): bool
    {
        return self::TYPE_CONSTANT === $this->type;
    }
}
