<?php


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
