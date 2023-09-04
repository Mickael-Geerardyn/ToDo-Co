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

namespace PhpCsFixer\Console\Report\FixReport;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class ReportSummary
{
    /**
     * @param array<string, array{appliedFixers: list<string>, diff: string}> $changed
     * @param int                                                             $time    duration in milliseconds
     * @param int                                                             $memory  memory usage in bytes
     */
    public function __construct(private readonly array $changed, private readonly int $filesCount, private readonly int $time, private readonly int $memory, private readonly bool $addAppliedFixers, private readonly bool $isDryRun, private readonly bool $isDecoratedOutput)
    {
    }

    public function isDecoratedOutput(): bool
    {
        return $this->isDecoratedOutput;
    }

    public function isDryRun(): bool
    {
        return $this->isDryRun;
    }

    /**
     * @return array<string, array{appliedFixers: list<string>, diff: string}>
     */
    public function getChanged(): array
    {
        return $this->changed;
    }

    public function getMemory(): int
    {
        return $this->memory;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function getFilesCount(): int
    {
        return $this->filesCount;
    }

    public function shouldAddAppliedFixers(): bool
    {
        return $this->addAppliedFixers;
    }
}
