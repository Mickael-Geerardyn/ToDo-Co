<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit068607cd16de13b528a1fdc2e59af770
{
    private static ?\Composer\Autoload\ClassLoader $loader = null;

    public static function loadClassLoader($class)
    {
        if (\Composer\Autoload\ClassLoader::class === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(['ComposerAutoloaderInit068607cd16de13b528a1fdc2e59af770', 'loadClassLoader'], true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(['ComposerAutoloaderInit068607cd16de13b528a1fdc2e59af770', 'loadClassLoader']);

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit068607cd16de13b528a1fdc2e59af770::getInitializer($loader));

        $loader->register(true);

        $filesToLoad = \Composer\Autoload\ComposerStaticInit068607cd16de13b528a1fdc2e59af770::$files;
        $requireFile = \Closure::bind(static function ($fileIdentifier, $file) {
            if (empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
                $GLOBALS['__composer_autoload_files'][$fileIdentifier] = true;

                require $file;
            }
        }, null, null);
        foreach ($filesToLoad as $fileIdentifier => $file) {
            $requireFile($fileIdentifier, $file);
        }

        return $loader;
    }
}
