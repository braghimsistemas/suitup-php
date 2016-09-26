<?php

/**
 * An example of a general-purpose implementation that includes the optional
 * functionality of allowing multiple base directories for a single namespace
 * prefix.
 *
 * Given a foo-bar package of classes in the file system at the following
 * paths ...
 *
 *     /path/to/packages/foo-bar/
 *         src/
 *             Baz.php             # Foo\Bar\Baz
 *             Qux/
 *                 Quux.php        # Foo\Bar\Qux\Quux
 *         tests/
 *             BazTest.php         # Foo\Bar\BazTest
 *             Qux/
 *                 QuuxTest.php    # Foo\Bar\Qux\QuuxTest
 *
 * ... add the path to the class files for the \Foo\Bar\ namespace prefix
 * as follows:
 *
 *      <?php
 *      // instantiate the loader
 *      $loader = new \Example\Psr4AutoloaderClass;
 *
 *      // register the autoloader
 *      $loader->register();
 *
 *      // register the base directories for the namespace prefix
 *      $loader->addNamespace('Foo\Bar', '/path/to/packages/foo-bar/src');
 *      $loader->addNamespace('Foo\Bar', '/path/to/packages/foo-bar/tests');
 *
 * The following line would cause the autoloader to attempt to load the
 * \Foo\Bar\Qux\Quux class from /path/to/packages/foo-bar/src/Qux/Quux.php:
 *
 *      <?php
 *      new \Foo\Bar\Qux\Quux;
 *
 * The following line would cause the autoloader to attempt to load the
 * \Foo\Bar\Qux\QuuxTest class from /path/to/packages/foo-bar/tests/Qux/QuuxTest.php:
 *
 *      <?php
 *      new \Foo\Bar\Qux\QuuxTest;
 *
 * @link http://www.php-fig.org/psr/psr-4/examples/
 */
class Psr4AutoloaderClass
{
	/**
	 * An associative array where the key is a namespace prefix and the value
	 * is an array of base directories for classes in that namespace.
	 *
	 * @var array
	 */
	protected $prefixes = array();

	/**
	 * Register loader with SPL autoloader stack.
	 *
	 * @return void
	 */
	public function register() {
		spl_autoload_register(array($this, 'loadClass'));
	}

	/**
	 * Adds a base directory for a namespace prefix.
	 *
	 * @param string $prefix The namespace prefix.
	 * @param string $base_dir A base directory for class files in the
	 * namespace.
	 * @return void
	 */
	public function addNamespace($prefix, $base_dir)
	{
		// normalize namespace prefix
		$prefix = trim($prefix, '\\') . '\\';

		// normalize the base directory with a trailing separator
		$base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR) . '/';

		// initialize the namespace prefix array
		if (isset($this->prefixes[$prefix]) === false) {
			$this->prefixes[$prefix] = array();
		}

		// retain the base directory for the namespace prefix
		array_push($this->prefixes[$prefix], $base_dir);
	}

	/**
	 * Loads the class file for a given class name.
	 *
	 * @param string $class The fully-qualified class name.
	 * @return mixed The mapped file name on success, or boolean false on
	 * failure.
	 */
	public function loadClass($class)
	{
		$fileFound = false;

		foreach ($this->prefixes as $prefix => $paths) {

			// Encontra o prefixo que casa com esta classe
			if (strpos($class, $prefix) === 0) {

				foreach ($paths as $path) {

					// Pode ser que tenha apontado a classe na raiz do namespace
					$rootClass = str_replace($prefix, '', $class);
					$rootFilename = implode(DIRECTORY_SEPARATOR, explode('\\', $path.$rootClass.'.php'));

					// Ou apontado a classe no diretório interior ao namespace
					$filename = implode(DIRECTORY_SEPARATOR, explode('\\', $path.$class.'.php'));

					if (file_exists($rootFilename) && is_readable($rootFilename)) {
						$fileFound = $rootFilename;
						break;
						break;

					} else if (file_exists($filename) && is_readable($filename)) {
						$fileFound = $filename;
						break;
						break;
					}
				}
			}
		}

		// Valendo um include que vale mais que dinheiro!
		if ($fileFound) {
			include_once $fileFound;
			return true;
		}
		return false;
	}
}
