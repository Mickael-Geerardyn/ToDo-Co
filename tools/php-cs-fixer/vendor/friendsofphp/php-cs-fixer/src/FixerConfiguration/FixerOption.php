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

final class FixerOption implements FixerOptionInterface
{
    private readonly bool $isRequired;

    /**
     * @var mixed
     */
    private $default;

    /**
     * @var null|list<null|(callable(mixed): bool)|scalar>
     */
    private readonly ?array $allowedValues;

    private ?\Closure $normalizer = null;

    /**
     * @param null|list<string>                              $allowedTypes
     * @param null|list<null|(callable(mixed): bool)|scalar> $allowedValues
     */
    public function __construct(
        private readonly string $name,
        private readonly string $description,
        bool $isRequired = true,
        mixed $default = null,
        private readonly ?array $allowedTypes = null,
        ?array $allowedValues = null,
        ?\Closure $normalizer = null
    ) {
        if ($isRequired && null !== $default) {
            throw new \LogicException('Required options cannot have a default value.');
        }

        if (null !== $allowedValues) {
            foreach ($allowedValues as &$allowedValue) {
                if ($allowedValue instanceof \Closure) {
                    $allowedValue = $this->unbind($allowedValue);
                }
            }
        }
        $this->isRequired = $isRequired;
        $this->default = $default;
        $this->allowedValues = $allowedValues;

        if ($normalizer instanceof \Closure) {
            $this->normalizer = $this->unbind($normalizer);
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function hasDefault(): bool
    {
        return !$this->isRequired;
    }

    public function getDefault()
    {
        if (!$this->hasDefault()) {
            throw new \LogicException('No default value defined.');
        }

        return $this->default;
    }

    public function getAllowedTypes(): ?array
    {
        return $this->allowedTypes;
    }

    public function getAllowedValues(): ?array
    {
        return $this->allowedValues;
    }

    public function getNormalizer(): ?\Closure
    {
        return $this->normalizer;
    }

    /**
     * Unbinds the given closure to avoid memory leaks.
     *
     * The closures provided to this class were probably defined in a fixer
     * class and thus bound to it by default. The configuration will then be
     * stored in {@see AbstractFixer::$configurationDefinition}, leading to the
     * following cyclic reference:
     *
     *     fixer -> configuration definition -> options -> closures -> fixer
     *
     * This cyclic reference prevent the garbage collector to free memory as
     * all elements are still referenced.
     *
     * See {@see https://bugs.php.net/bug.php?id=69639 Bug #69639} for details.
     */
    private function unbind(\Closure $closure): \Closure
    {
        return $closure->bindTo(null);
    }
}
