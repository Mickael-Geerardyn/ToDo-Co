<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;

return static function (RectorConfig $rectorConfig): void {

	// register single rule
	$rectorConfig->rule(TypedPropertyFromStrictConstructorRector::class);

	// here we can define, what sets of rules will be applied
	// tip: use "SetList" class to autocomplete sets with your IDE
	$rectorConfig->sets([
							SetList::CODE_QUALITY,
		SetList::DEAD_CODE,
		SetList::INSTANCEOF,
		LevelSetList::UP_TO_PHP_81
						]);
    $rectorConfig->paths([
        __DIR__ . '/config',
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/tools',
    ]);

    // register a single rule
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    // define sets of rules
    //    $rectorConfig->sets([
    //        LevelSetList::UP_TO_PHP_72
    //    ]);
};
