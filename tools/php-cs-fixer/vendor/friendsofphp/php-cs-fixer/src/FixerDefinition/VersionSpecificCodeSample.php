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
 * @author Andreas Möller <am@localheinz.com>
 */
final class VersionSpecificCodeSample implements VersionSpecificCodeSampleInterface
{
    private readonly CodeSampleInterface $codeSample;

    /**
     * @param null|array<string, mixed> $configuration
     */
    public function __construct(
        string $code,
        private readonly VersionSpecificationInterface $versionSpecification,
        ?array $configuration = null
    ) {
        $this->codeSample = new CodeSample($code, $configuration);
    }

    public function getCode(): string
    {
        return $this->codeSample->getCode();
    }

    public function getConfiguration(): ?array
    {
        return $this->codeSample->getConfiguration();
    }

    public function isSuitableFor(int $version): bool
    {
        return $this->versionSpecification->isSatisfiedBy($version);
    }
}
