<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2019 Braghim Sistemas
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
declare(strict_types=1);

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
  public function addNamespace($prefix, $base_dir) {
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
  public function loadClass($class) {
    $fileFound = false;

    foreach ($this->prefixes as $prefix => $paths) {

      // Find the prefix which matches with this class
      if (strpos($class, $prefix) === 0) {

        foreach ($paths as $path) {

          // Maybe the class is in the root of the namespace
          $rootClass = str_replace($prefix, '', $class);
          $rootFilename = implode(DIRECTORY_SEPARATOR, explode('\\', $path . $rootClass . '.php'));

          // Or setting the class inside the folder to the namespace
          $filename = implode(DIRECTORY_SEPARATOR, explode('\\', $path . $class . '.php'));

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

    if ($fileFound) {
      include_once $fileFound;
      return true;
    }
    return false;
  }
}
