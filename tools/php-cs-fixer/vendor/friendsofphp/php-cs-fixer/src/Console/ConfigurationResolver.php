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

namespace PhpCsFixer\Console;

use PhpCsFixer\Cache\CacheManagerInterface;
use PhpCsFixer\Cache\Directory;
use PhpCsFixer\Cache\DirectoryInterface;
use PhpCsFixer\Cache\FileCacheManager;
use PhpCsFixer\Cache\FileHandler;
use PhpCsFixer\Cache\NullCacheManager;
use PhpCsFixer\Cache\Signature;
use PhpCsFixer\ConfigInterface;
use PhpCsFixer\ConfigurationException\InvalidConfigurationException;
use PhpCsFixer\Console\Output\Progress\ProgressOutputType;
use PhpCsFixer\Console\Report\FixReport\ReporterFactory;
use PhpCsFixer\Console\Report\FixReport\ReporterInterface;
use PhpCsFixer\Differ\DifferInterface;
use PhpCsFixer\Differ\NullDiffer;
use PhpCsFixer\Differ\UnifiedDiffer;
use PhpCsFixer\Finder;
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerFactory;
use PhpCsFixer\Linter\Linter;
use PhpCsFixer\Linter\LinterInterface;
use PhpCsFixer\RuleSet\RuleSet;
use PhpCsFixer\RuleSet\RuleSetInterface;
use PhpCsFixer\StdinFileInfo;
use PhpCsFixer\ToolInfoInterface;
use PhpCsFixer\Utils;
use PhpCsFixer\WhitespacesFixerConfig;
use PhpCsFixer\WordMatcher;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder as SymfonyFinder;

/**
 * The resolver that resolves configuration to use by command line options and config.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Katsuhiro Ogawa <ko.fivestar@gmail.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class ConfigurationResolver
{
    public const PATH_MODE_OVERRIDE = 'override';
    public const PATH_MODE_INTERSECTION = 'intersection';

    private ?bool $allowRisky = null;

    private ?\PhpCsFixer\ConfigInterface $config = null;

    /**
     * @var null|string
     */
    private $configFile;

    private ?\PhpCsFixer\Console\Report\FixReport\ReporterInterface $reporter = null;

    private ?bool $isStdIn = null;

    /**
     * @var null|bool
     */
    private $isDryRun;

    /**
     * @var null|FixerInterface[]
     */
    private ?array $fixers = null;

    private ?bool $configFinderIsOverridden = null;

    /**
     * @var array<string, mixed>
     */
    private array $options = [
        'allow-risky' => null,
        'cache-file' => null,
        'config' => null,
        'diff' => null,
        'dry-run' => null,
        'format' => null,
        'path' => [],
        'path-mode' => self::PATH_MODE_OVERRIDE,
        'rules' => null,
        'show-progress' => null,
        'stop-on-violation' => null,
        'using-cache' => null,
        'verbosity' => null,
    ];

    /**
     * @var null|string
     */
    private $cacheFile;

    /**
     * @var null|CacheManagerInterface
     */
    private \PhpCsFixer\Cache\NullCacheManager|\PhpCsFixer\Cache\FileCacheManager|null $cacheManager = null;

    /**
     * @var null|DifferInterface
     */
    private \PhpCsFixer\Differ\UnifiedDiffer|\PhpCsFixer\Differ\NullDiffer|null $differ = null;

    private ?\PhpCsFixer\Cache\Directory $directory = null;

    /**
     * @var null|iterable<\SplFileInfo>
     */
    private ?iterable $finder = null;

    private ?string $format = null;

    private ?\PhpCsFixer\Linter\Linter $linter = null;

    /**
     * @var null|list<string>
     */
    private ?array $path = null;

    private ?string $progress = null;

    private ?\PhpCsFixer\RuleSet\RuleSet $ruleSet = null;

    private ?bool $usingCache = null;

    private ?\PhpCsFixer\FixerFactory $fixerFactory = null;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private ConfigInterface $defaultConfig,
        array $options,
        private string $cwd,
        private readonly ToolInfoInterface $toolInfo
    ) {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }

    public function getCacheFile(): ?string
    {
        if (!$this->getUsingCache()) {
            return null;
        }

        if (null === $this->cacheFile) {
            if (null === $this->options['cache-file']) {
                $this->cacheFile = $this->getConfig()->getCacheFile();
            } else {
                $this->cacheFile = $this->options['cache-file'];
            }
        }

        return $this->cacheFile;
    }

    public function getCacheManager(): CacheManagerInterface
    {
        if (!$this->cacheManager instanceof \PhpCsFixer\Cache\CacheManagerInterface) {
            $cacheFile = $this->getCacheFile();

            if (null === $cacheFile) {
                $this->cacheManager = new NullCacheManager();
            } else {
                $this->cacheManager = new FileCacheManager(
                    new FileHandler($cacheFile),
                    new Signature(
                        PHP_VERSION,
                        $this->toolInfo->getVersion(),
                        $this->getConfig()->getIndent(),
                        $this->getConfig()->getLineEnding(),
                        $this->getRules()
                    ),
                    $this->isDryRun(),
                    $this->getDirectory()
                );
            }
        }

        return $this->cacheManager;
    }

    public function getConfig(): ConfigInterface
    {
        if (!$this->config instanceof \PhpCsFixer\ConfigInterface) {
            foreach ($this->computeConfigFiles() as $configFile) {
                if (!file_exists($configFile)) {
                    continue;
                }

                $configFileBasename = basename($configFile);
                $deprecatedConfigs = [
                    '.php_cs' => '.php-cs-fixer.php',
                    '.php_cs.dist' => '.php-cs-fixer.dist.php',
                ];

                if (isset($deprecatedConfigs[$configFileBasename])) {
                    throw new InvalidConfigurationException("Configuration file `{$configFileBasename}` is outdated, rename to `{$deprecatedConfigs[$configFileBasename]}`.");
                }

                $this->config = self::separatedContextLessInclude($configFile);
                $this->configFile = $configFile;

                break;
            }

            if (!$this->config instanceof \PhpCsFixer\ConfigInterface) {
                $this->config = $this->defaultConfig;
            }
        }

        return $this->config;
    }

    public function getConfigFile(): ?string
    {
        if (null === $this->configFile) {
            $this->getConfig();
        }

        return $this->configFile;
    }

    public function getDiffer(): DifferInterface
    {
        if (!$this->differ instanceof \PhpCsFixer\Differ\DifferInterface) {
            $this->differ = (true === $this->options['diff']) ? new UnifiedDiffer() : new NullDiffer();
        }

        return $this->differ;
    }

    public function getDirectory(): DirectoryInterface
    {
        if (!$this->directory instanceof \PhpCsFixer\Cache\Directory) {
            $path = $this->getCacheFile();
            if (null === $path) {
                $absolutePath = $this->cwd;
            } else {
                $filesystem = new Filesystem();

                $absolutePath = $filesystem->isAbsolutePath($path)
                    ? $path
                    : $this->cwd.\DIRECTORY_SEPARATOR.$path;
                $absolutePath = \dirname($absolutePath);
            }

            $this->directory = new Directory($absolutePath);
        }

        return $this->directory;
    }

    /**
     * @return FixerInterface[] An array of FixerInterface
     */
    public function getFixers(): array
    {
        if (null === $this->fixers) {
            $this->fixers = $this->createFixerFactory()
                ->useRuleSet($this->getRuleSet())
                ->setWhitespacesConfig(new WhitespacesFixerConfig($this->config->getIndent(), $this->config->getLineEnding()))
                ->getFixers()
            ;

            if (!$this->getRiskyAllowed()) {
                $riskyFixers = array_map(
                    static fn (FixerInterface $fixer): string => $fixer->getName(),
                    array_filter(
                        $this->fixers,
                        static fn (FixerInterface $fixer): bool => $fixer->isRisky()
                    )
                );

                if ($riskyFixers !== []) {
                    throw new InvalidConfigurationException(sprintf('The rules contain risky fixers (%s), but they are not allowed to run. Perhaps you forget to use --allow-risky=yes option?', Utils::naturalLanguageJoin($riskyFixers)));
                }
            }
        }

        return $this->fixers;
    }

    public function getLinter(): LinterInterface
    {
        if (!$this->linter instanceof \PhpCsFixer\Linter\Linter) {
            $this->linter = new Linter();
        }

        return $this->linter;
    }

    /**
     * Returns path.
     *
     * @return string[]
     */
    public function getPath(): array
    {
        if (null === $this->path) {
            $filesystem = new Filesystem();
            $cwd = $this->cwd;

            if (1 === (is_countable($this->options['path']) ? \count($this->options['path']) : 0) && '-' === $this->options['path'][0]) {
                $this->path = $this->options['path'];
            } else {
                $this->path = array_map(
                    static function (string $rawPath) use ($cwd, $filesystem): string {
                        $path = trim($rawPath);

                        if ('' === $path) {
                            throw new InvalidConfigurationException("Invalid path: \"{$rawPath}\".");
                        }

                        $absolutePath = $filesystem->isAbsolutePath($path)
                            ? $path
                            : $cwd.\DIRECTORY_SEPARATOR.$path;

                        if (!file_exists($absolutePath)) {
                            throw new InvalidConfigurationException(sprintf(
                                'The path "%s" is not readable.',
                                $path
                            ));
                        }

                        return $absolutePath;
                    },
                    $this->options['path']
                );
            }
        }

        return $this->path;
    }

    /**
     * @throws InvalidConfigurationException
     */
    public function getProgressType(): string
    {
        if (null === $this->progress) {
            if (OutputInterface::VERBOSITY_VERBOSE <= $this->options['verbosity'] && 'txt' === $this->getFormat()) {
                $progressType = $this->options['show-progress'];

                if (null === $progressType) {
                    $progressType = $this->getConfig()->getHideProgress()
                        ? ProgressOutputType::NONE
                        : ProgressOutputType::DOTS;
                } elseif (!\in_array($progressType, ProgressOutputType::AVAILABLE, true)) {
                    throw new InvalidConfigurationException(sprintf(
                        'The progress type "%s" is not defined, supported are %s.',
                        $progressType,
                        Utils::naturalLanguageJoin(ProgressOutputType::AVAILABLE)
                    ));
                }

                $this->progress = $progressType;
            } else {
                $this->progress = ProgressOutputType::NONE;
            }
        }

        return $this->progress;
    }

    public function getReporter(): ReporterInterface
    {
        if (!$this->reporter instanceof \PhpCsFixer\Console\Report\FixReport\ReporterInterface) {
            $reporterFactory = new ReporterFactory();
            $reporterFactory->registerBuiltInReporters();

            $format = $this->getFormat();

            try {
                $this->reporter = $reporterFactory->getReporter($format);
            } catch (\UnexpectedValueException) {
                $formats = $reporterFactory->getFormats();
                sort($formats);

                throw new InvalidConfigurationException(sprintf('The format "%s" is not defined, supported are %s.', $format, Utils::naturalLanguageJoin($formats)));
            }
        }

        return $this->reporter;
    }

    public function getRiskyAllowed(): bool
    {
        if (null === $this->allowRisky) {
            if (null === $this->options['allow-risky']) {
                $this->allowRisky = $this->getConfig()->getRiskyAllowed();
            } else {
                $this->allowRisky = $this->resolveOptionBooleanValue('allow-risky');
            }
        }

        return $this->allowRisky;
    }

    /**
     * Returns rules.
     *
     * @return array<string, array<string, mixed>|bool>
     */
    public function getRules(): array
    {
        return $this->getRuleSet()->getRules();
    }

    public function getUsingCache(): bool
    {
        if (null === $this->usingCache) {
            if (null === $this->options['using-cache']) {
                $this->usingCache = $this->getConfig()->getUsingCache();
            } else {
                $this->usingCache = $this->resolveOptionBooleanValue('using-cache');
            }
        }

        $this->usingCache = $this->usingCache && ($this->toolInfo->isInstalledAsPhar() || $this->toolInfo->isInstalledByComposer());

        return $this->usingCache;
    }

    /**
     * @return iterable<\SplFileInfo>
     */
    public function getFinder(): iterable
    {
        if (null === $this->finder) {
            $this->finder = $this->resolveFinder();
        }

        return $this->finder;
    }

    /**
     * Returns dry-run flag.
     */
    public function isDryRun(): bool
    {
        if (null === $this->isDryRun) {
            $this->isDryRun = $this->isStdIn() ? true : $this->options['dry-run'];
        }

        return $this->isDryRun;
    }

    public function shouldStopOnViolation(): bool
    {
        return $this->options['stop-on-violation'];
    }

    public function configFinderIsOverridden(): bool
    {
        if (null === $this->configFinderIsOverridden) {
            $this->resolveFinder();
        }

        return $this->configFinderIsOverridden;
    }

    /**
     * Compute file candidates for config file.
     *
     * @return string[]
     */
    private function computeConfigFiles(): array
    {
        $configFile = $this->options['config'];

        if (null !== $configFile) {
            if (!file_exists($configFile) || !is_readable($configFile)) {
                throw new InvalidConfigurationException(sprintf('Cannot read config file "%s".', $configFile));
            }

            return [$configFile];
        }

        $path = $this->getPath();

        if ($this->isStdIn() || [] === $path) {
            $configDir = $this->cwd;
        } elseif (1 < \count($path)) {
            throw new InvalidConfigurationException('For multiple paths config parameter is required.');
        } elseif (!is_file($path[0])) {
            $configDir = $path[0];
        } else {
            $dirName = pathinfo($path[0], PATHINFO_DIRNAME);
            $configDir = $dirName ?: $path[0];
        }

        $candidates = [
            $configDir.\DIRECTORY_SEPARATOR.'.php-cs-fixer.php',
            $configDir.\DIRECTORY_SEPARATOR.'.php-cs-fixer.dist.php',
            $configDir.\DIRECTORY_SEPARATOR.'.php_cs', // old v2 config, present here only to throw nice error message later
            $configDir.\DIRECTORY_SEPARATOR.'.php_cs.dist', // old v2 config, present here only to throw nice error message later
        ];

        if ($configDir !== $this->cwd) {
            $candidates[] = $this->cwd.\DIRECTORY_SEPARATOR.'.php-cs-fixer.php';
            $candidates[] = $this->cwd.\DIRECTORY_SEPARATOR.'.php-cs-fixer.dist.php';
            $candidates[] = $this->cwd.\DIRECTORY_SEPARATOR.'.php_cs'; // old v2 config, present here only to throw nice error message later
            $candidates[] = $this->cwd.\DIRECTORY_SEPARATOR.'.php_cs.dist'; // old v2 config, present here only to throw nice error message later
        }

        return $candidates;
    }

    private function createFixerFactory(): FixerFactory
    {
        if (!$this->fixerFactory instanceof \PhpCsFixer\FixerFactory) {
            $fixerFactory = new FixerFactory();
            $fixerFactory->registerBuiltInFixers();
            $fixerFactory->registerCustomFixers($this->getConfig()->getCustomFixers());

            $this->fixerFactory = $fixerFactory;
        }

        return $this->fixerFactory;
    }

    private function getFormat(): string
    {
        if (null === $this->format) {
            $this->format = $this->options['format'] ?? $this->getConfig()->getFormat();
        }

        return $this->format;
    }

    private function getRuleSet(): RuleSetInterface
    {
        if (!$this->ruleSet instanceof \PhpCsFixer\RuleSet\RuleSet) {
            $rules = $this->parseRules();
            $this->validateRules($rules);

            $this->ruleSet = new RuleSet($rules);
        }

        return $this->ruleSet;
    }

    private function isStdIn(): bool
    {
        if (null === $this->isStdIn) {
            $this->isStdIn = 1 === (is_countable($this->options['path']) ? \count($this->options['path']) : 0) && '-' === $this->options['path'][0];
        }

        return $this->isStdIn;
    }

    /**
     * @template T
     *
     * @param iterable<T> $iterable
     *
     * @return \Traversable<T>
     */
    private function iterableToTraversable(iterable $iterable): \Traversable
    {
        return \is_array($iterable) ? new \ArrayIterator($iterable) : $iterable;
    }

    /**
     * @return array<mixed>
     */
    private function parseRules(): array
    {
        if (null === $this->options['rules']) {
            return $this->getConfig()->getRules();
        }

        $rules = trim((string) $this->options['rules']);
        if ('' === $rules) {
            throw new InvalidConfigurationException('Empty rules value is not allowed.');
        }

        if (str_starts_with($rules, '{')) {
            $rules = json_decode($rules, true);

            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new InvalidConfigurationException(sprintf('Invalid JSON rules input: "%s".', json_last_error_msg()));
            }

            return $rules;
        }

        $rules = [];

        foreach (explode(',', (string) $this->options['rules']) as $rule) {
            $rule = trim($rule);

            if ('' === $rule) {
                throw new InvalidConfigurationException('Empty rule name is not allowed.');
            }

            if (str_starts_with($rule, '-')) {
                $rules[substr($rule, 1)] = false;
            } else {
                $rules[$rule] = true;
            }
        }

        return $rules;
    }

    /**
     * @param array<mixed> $rules
     *
     * @throws InvalidConfigurationException
     */
    private function validateRules(array $rules): void
    {
        /**
         * Create a ruleset that contains all configured rules, even when they originally have been disabled.
         *
         * @see RuleSet::resolveSet()
         */
        $ruleSet = [];

        foreach ($rules as $key => $value) {
            if (\is_int($key)) {
                throw new InvalidConfigurationException(sprintf('Missing value for "%s" rule/set.', $value));
            }

            $ruleSet[$key] = true;
        }

        $ruleSet = new RuleSet($ruleSet);

        $configuredFixers = array_keys($ruleSet->getRules());

        $fixers = $this->createFixerFactory()->getFixers();

        $availableFixers = array_map(static fn (FixerInterface $fixer): string => $fixer->getName(), $fixers);

        $unknownFixers = array_diff($configuredFixers, $availableFixers);

        if ($unknownFixers !== []) {
            $renamedRules = [
                'blank_line_before_return' => [
                    'new_name' => 'blank_line_before_statement',
                    'config' => ['statements' => ['return']],
                ],
                'final_static_access' => [
                    'new_name' => 'self_static_accessor',
                ],
                'hash_to_slash_comment' => [
                    'new_name' => 'single_line_comment_style',
                    'config' => ['comment_types' => ['hash']],
                ],
                'lowercase_constants' => [
                    'new_name' => 'constant_case',
                    'config' => ['case' => 'lower'],
                ],
                'no_extra_consecutive_blank_lines' => [
                    'new_name' => 'no_extra_blank_lines',
                ],
                'no_multiline_whitespace_before_semicolons' => [
                    'new_name' => 'multiline_whitespace_before_semicolons',
                ],
                'no_short_echo_tag' => [
                    'new_name' => 'echo_tag_syntax',
                    'config' => ['format' => 'long'],
                ],
                'php_unit_ordered_covers' => [
                    'new_name' => 'phpdoc_order_by_value',
                    'config' => ['annotations' => ['covers']],
                ],
                'phpdoc_inline_tag' => [
                    'new_name' => 'general_phpdoc_tag_rename, phpdoc_inline_tag_normalizer and phpdoc_tag_type',
                ],
                'pre_increment' => [
                    'new_name' => 'increment_style',
                    'config' => ['style' => 'pre'],
                ],
                'psr0' => [
                    'new_name' => 'psr_autoloading',
                    'config' => ['dir' => 'x'],
                ],
                'psr4' => [
                    'new_name' => 'psr_autoloading',
                ],
                'silenced_deprecation_error' => [
                    'new_name' => 'error_suppression',
                ],
                'trailing_comma_in_multiline_array' => [
                    'new_name' => 'trailing_comma_in_multiline',
                    'config' => ['elements' => ['arrays']],
                ],
            ];

            $message = 'The rules contain unknown fixers: ';
            $hasOldRule = false;

            foreach ($unknownFixers as $unknownFixer) {
                if (isset($renamedRules[$unknownFixer])) { // Check if present as old renamed rule
                    $hasOldRule = true;
                    $message .= sprintf(
                        '"%s" is renamed (did you mean "%s"?%s), ',
                        $unknownFixer,
                        $renamedRules[$unknownFixer]['new_name'],
                        isset($renamedRules[$unknownFixer]['config']) ? ' (note: use configuration "'.Utils::toString($renamedRules[$unknownFixer]['config']).'")' : ''
                    );
                } else { // Go to normal matcher if it is not a renamed rule
                    $matcher = new WordMatcher($availableFixers);
                    $alternative = $matcher->match($unknownFixer);
                    $message .= sprintf(
                        '"%s"%s, ',
                        $unknownFixer,
                        null === $alternative ? '' : ' (did you mean "'.$alternative.'"?)'
                    );
                }
            }

            $message = substr($message, 0, -2).'.';

            if ($hasOldRule) {
                $message .= "\nFor more info about updating see: https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/v3.0.0/UPGRADE-v3.md#renamed-ruless.";
            }

            throw new InvalidConfigurationException($message);
        }

        foreach ($fixers as $fixer) {
            $fixerName = $fixer->getName();
            if (isset($rules[$fixerName]) && $fixer instanceof DeprecatedFixerInterface) {
                $successors = $fixer->getSuccessorsNames();
                $messageEnd = [] === $successors
                    ? sprintf(' and will be removed in version %d.0.', Application::getMajorVersion() + 1)
                    : sprintf('. Use %s instead.', str_replace('`', '"', Utils::naturalLanguageJoinWithBackticks($successors)));

                Utils::triggerDeprecation(new \RuntimeException("Rule \"{$fixerName}\" is deprecated{$messageEnd}"));
            }
        }
    }

    /**
     * Apply path on config instance.
     *
     * @return iterable<\SplFileInfo>
     */
    private function resolveFinder(): iterable
    {
        $this->configFinderIsOverridden = false;

        if ($this->isStdIn()) {
            return new \ArrayIterator([new StdinFileInfo()]);
        }

        $modes = [self::PATH_MODE_OVERRIDE, self::PATH_MODE_INTERSECTION];

        if (!\in_array(
            $this->options['path-mode'],
            $modes,
            true
        )) {
            throw new InvalidConfigurationException(sprintf(
                'The path-mode "%s" is not defined, supported are %s.',
                $this->options['path-mode'],
                Utils::naturalLanguageJoin($modes)
            ));
        }

        $isIntersectionPathMode = self::PATH_MODE_INTERSECTION === $this->options['path-mode'];

        $paths = array_filter(array_map(
            static fn (string $path) => realpath($path),
            $this->getPath()
        ));

        if ([] === $paths) {
            if ($isIntersectionPathMode) {
                return new \ArrayIterator([]);
            }

            return $this->iterableToTraversable($this->getConfig()->getFinder());
        }

        $pathsByType = [
            'file' => [],
            'dir' => [],
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                $pathsByType['file'][] = $path;
            } else {
                $pathsByType['dir'][] = $path.\DIRECTORY_SEPARATOR;
            }
        }

        $nestedFinder = null;
        $currentFinder = $this->iterableToTraversable($this->getConfig()->getFinder());

        try {
            $nestedFinder = $currentFinder instanceof \IteratorAggregate ? $currentFinder->getIterator() : $currentFinder;
        } catch (\Exception) {
        }

        if ($isIntersectionPathMode) {
            if (!$nestedFinder instanceof \Traversable) {
                throw new InvalidConfigurationException(
                    'Cannot create intersection with not-fully defined Finder in configuration file.'
                );
            }

            return new \CallbackFilterIterator(
                new \IteratorIterator($nestedFinder),
                static function (\SplFileInfo $current) use ($pathsByType): bool {
                    $currentRealPath = $current->getRealPath();

                    if (\in_array($currentRealPath, $pathsByType['file'], true)) {
                        return true;
                    }

                    foreach ($pathsByType['dir'] as $path) {
                        if (str_starts_with($currentRealPath, $path)) {
                            return true;
                        }
                    }

                    return false;
                }
            );
        }

        if (null !== $this->getConfigFile() && $nestedFinder instanceof \Traversable) {
            $this->configFinderIsOverridden = true;
        }

        if ($currentFinder instanceof SymfonyFinder && !$nestedFinder instanceof \Traversable) {
            // finder from configuration Symfony finder and it is not fully defined, we may fulfill it
            return $currentFinder->in($pathsByType['dir'])->append($pathsByType['file']);
        }

        return Finder::create()->in($pathsByType['dir'])->append($pathsByType['file']);
    }

    /**
     * Set option that will be resolved.
     */
    private function setOption(string $name, mixed $value): void
    {
        if (!\array_key_exists($name, $this->options)) {
            throw new InvalidConfigurationException(sprintf('Unknown option name: "%s".', $name));
        }

        $this->options[$name] = $value;
    }

    private function resolveOptionBooleanValue(string $optionName): bool
    {
        $value = $this->options[$optionName];

        if (!\is_string($value)) {
            throw new InvalidConfigurationException(sprintf('Expected boolean or string value for option "%s".', $optionName));
        }

        if ('yes' === $value) {
            return true;
        }

        if ('no' === $value) {
            return false;
        }

        throw new InvalidConfigurationException(sprintf('Expected "yes" or "no" for option "%s", got "%s".', $optionName, $value));
    }

    private function separatedContextLessInclude(string $path): ConfigInterface
    {
        $config = include $path;

        // verify that the config has an instance of Config
        if (!$config instanceof ConfigInterface) {
            throw new InvalidConfigurationException(sprintf('The config file: "%s" does not return a "PhpCsFixer\ConfigInterface" instance. Got: "%s".', $path, get_debug_type($config)));
        }

        return $config;
    }
}
