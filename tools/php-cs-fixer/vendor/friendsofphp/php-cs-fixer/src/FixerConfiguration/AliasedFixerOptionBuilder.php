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

namespace PhpCsFixer\FixerConfiguration;

/**
 * @author ntzm
 *
 * @internal
 */
final class AliasedFixerOptionBuilder
{
    public function __construct(private readonly FixerOptionBuilder $optionBuilder, private readonly string $alias)
    {
    }

    public function setDefault(mixed $default): self
    {
        $this->optionBuilder->setDefault($default);

        return $this;
    }

    /**
     * @param list<string> $allowedTypes
     */
    public function setAllowedTypes(array $allowedTypes): self
    {
        $this->optionBuilder->setAllowedTypes($allowedTypes);

        return $this;
    }

    /**
     * @param list<null|(callable(mixed): bool)|scalar> $allowedValues
     */
    public function setAllowedValues(array $allowedValues): self
    {
        $this->optionBuilder->setAllowedValues($allowedValues);

        return $this;
    }

    public function setNormalizer(\Closure $normalizer): self
    {
        $this->optionBuilder->setNormalizer($normalizer);

        return $this;
    }

    public function getOption(): AliasedFixerOption
    {
        return new AliasedFixerOption(
            $this->optionBuilder->getOption(),
            $this->alias
        );
    }
}
