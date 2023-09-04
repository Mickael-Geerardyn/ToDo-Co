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

namespace PhpCsFixer\FixerDefinition;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class FixerDefinition implements FixerDefinitionInterface
{
    /**
     * @param list<CodeSampleInterface> $codeSamples      array of samples, where single sample is [code, configuration]
     * @param null|string               $riskyDescription null for non-risky fixer
     */
    public function __construct(
        private readonly string $summary,
        private readonly array $codeSamples,
        /**
         * Description of Fixer and benefit of using it.
         */
        private readonly ?string $description = null,
        /**
         * Description why Fixer is risky.
         */
        private readonly ?string $riskyDescription = null
    )
    {
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getRiskyDescription(): ?string
    {
        return $this->riskyDescription;
    }

    public function getCodeSamples(): array
    {
        return $this->codeSamples;
    }
}
