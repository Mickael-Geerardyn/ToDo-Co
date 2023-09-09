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

namespace PhpCsFixer\Console\Output;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class OutputContext
{
    public function __construct(private readonly ?OutputInterface $output, private readonly int $terminalWidth, private readonly int $filesCount)
    {
    }

    public function getOutput(): ?OutputInterface
    {
        return $this->output;
    }

    public function getTerminalWidth(): int
    {
        return $this->terminalWidth;
    }

    public function getFilesCount(): int
    {
        return $this->filesCount;
    }
}
