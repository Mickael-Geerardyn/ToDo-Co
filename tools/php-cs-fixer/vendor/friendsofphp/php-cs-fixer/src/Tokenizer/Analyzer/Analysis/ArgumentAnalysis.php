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
final class ArgumentAnalysis
{
    /**
     * The default value of the argument.
     */
    private readonly ?string $default;

    /**
     * The type analysis of the argument.
     */
    private readonly ?TypeAnalysis $typeAnalysis;

    public function __construct(/**
     * The name of the argument.
     */
    private readonly ?string $name, /**
     * The index where the name is located in the supplied Tokens object.
     */
    private readonly ?int $nameIndex, ?string $default, ?TypeAnalysis $typeAnalysis = null)
    {
        $this->default = $default ?? null;
        $this->typeAnalysis = $typeAnalysis ?? null;
    }

    public function getDefault(): ?string
    {
        return $this->default;
    }

    public function hasDefault(): bool
    {
        return null !== $this->default;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNameIndex(): ?int
    {
        return $this->nameIndex;
    }

    public function getTypeAnalysis(): ?TypeAnalysis
    {
        return $this->typeAnalysis;
    }

    public function hasTypeAnalysis(): bool
    {
        return $this->typeAnalysis instanceof \PhpCsFixer\Tokenizer\Analyzer\Analysis\TypeAnalysis;
    }
}
