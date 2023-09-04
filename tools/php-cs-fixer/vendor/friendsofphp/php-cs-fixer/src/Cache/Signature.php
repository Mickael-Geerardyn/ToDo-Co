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

namespace PhpCsFixer\Cache;

/**
 * @author Andreas Möller <am@localheinz.com>
 *
 * @internal
 */
final class Signature implements SignatureInterface
{
    /**
     * @var array<string, array<string, mixed>|bool>
     */
    private readonly array $rules;

    /**
     * @param array<string, array<string, mixed>|bool> $rules
     */
    public function __construct(private readonly string $phpVersion, private readonly string $fixerVersion, private readonly string $indent, private readonly string $lineEnding, array $rules)
    {
        $this->rules = self::makeJsonEncodable($rules);
    }

    public function getPhpVersion(): string
    {
        return $this->phpVersion;
    }

    public function getFixerVersion(): string
    {
        return $this->fixerVersion;
    }

    public function getIndent(): string
    {
        return $this->indent;
    }

    public function getLineEnding(): string
    {
        return $this->lineEnding;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function equals(SignatureInterface $signature): bool
    {
        return $this->phpVersion === $signature->getPhpVersion()
            && $this->fixerVersion === $signature->getFixerVersion()
            && $this->indent === $signature->getIndent()
            && $this->lineEnding === $signature->getLineEnding()
            && $this->rules === $signature->getRules();
    }

    /**
     * @param array<string, array<string, mixed>|bool> $data
     *
     * @return array<string, array<string, mixed>|bool>
     */
    private function makeJsonEncodable(array $data): array
    {
        array_walk_recursive($data, static function (&$item): void {
            if (\is_string($item) && !mb_detect_encoding($item, 'utf-8', true)) {
                $item = base64_encode($item);
            }
        });

        return $data;
    }
}
