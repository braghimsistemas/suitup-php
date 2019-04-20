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
 * FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
declare(strict_types=1);

namespace SuitUp\Database\DbAdapter;

use SuitUp\Exception\StructureException;

final class AdapterFactory
{

  /**
   * @param array $configs
   * @return bool|DbAdapter
   * @throws StructureException
   * @throws \SuitUp\Exception\DbAdapterException
   */
  public static function getAdapter(array $configs)
  {
    $adapter = null;

    foreach (array_keys($configs) as $key) {
      if (in_array($key, array('type', 'dbtype', 'adapter', 'dbadapter'))) {

        // Store and remove type
        $type = $configs[$key];
        unset($configs[$key]);

        switch ($type) {
          case 'mysql':
            $adapter = new Mysql($configs);
            break;
          case 'postgres':
          case 'postgre':
          case 'pgsql':
            $adapter = new Postgres($configs);
            break;
          default:
            throw new StructureException("The database adapter '$key' is not a known adapter type");
        }

        break;
      } else {
        throw new StructureException("Since Suitup PHP 2.0 you need to provide an index called 'adapter' in the database.config.php file");
      }
    }

    if ($adapter) {
      return new \SuitUp\Database\DbAdapter($adapter);
    }
    return false;
  }
}
