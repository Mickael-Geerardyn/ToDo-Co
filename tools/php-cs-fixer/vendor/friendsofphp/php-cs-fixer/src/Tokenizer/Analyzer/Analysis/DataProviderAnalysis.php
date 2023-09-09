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

final class DataProviderAnalysis
{
    /**
     * @param array<int> $usageIndices
     */
    public function __construct(private readonly string $name, private readonly int $nameIndex, private readonly array $usageIndices)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNameIndex(): int
    {
        return $this->nameIndex;
    }

    /**
     * @return array<int>
     */
    public function getUsageIndices(): array
    {
        return $this->usageIndices;
    }
}
