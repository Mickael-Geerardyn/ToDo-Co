<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (\PHP_VERSION_ID >= 70400 && extension_loaded('curl')) {
    /**
     * @property string $data
     */
    class CURLStringFile extends CURLFile
    {
        /**
         * @var string
         */
        public $name;

        public function __construct()
        {
        }

        public function __set(string $name, $value): void
        {
            if ('data' !== $name) {
                $this->$name = $value;

                return;
            }

            if (is_object($value) ? !method_exists($value, '__toString') : !is_scalar($value)) {
                throw new \TypeError('Cannot assign '.gettype($value).' to property CURLStringFile::$data of type string');
            }

            $this->name = 'data://application/octet-stream;base64,'.base64_encode((string) $value);
        }

        public function __isset(string $name): bool
        {
            return property_exists($this, 'name') && $this->$name !== null;
        }

        public function &__get(string $name)
        {
            return $this->$name;
        }
    }
}
