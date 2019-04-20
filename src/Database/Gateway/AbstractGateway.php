<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Braghim Sistemas
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
namespace SuitUp\Database\Gateway;

use SuitUp\Database\DbAdapterInterface;
use SuitUp\Exception\DatabaseGatewayException;
use SuitUp\Mvc\MvcAbstractController;
use SuitUp\Database\SqlFileManager;
use SuitUp\Database\Database;

/**
 * Class AbstractGateway
 * @package SuitUp\Database\Gateway
 */
abstract class AbstractGateway
{

  protected $name;

  protected $primary;

  protected $onUpdate;

  protected $db;

  private static $defaultAdapter;

  public function __construct(DbAdapterInterface $dbAdapter = null) {

    if ($dbAdapter) {
      // Append database adapter
      $this->db = $dbAdapter;

    } else {

      // If was not set default adapter
      if (self::getDefaultAdapter() == null) {
        throw new DatabaseGatewayException('There is no database connection defined');
      }

      $this->db = self::getDefaultAdapter();
    }

    // Checkup gateway
    $this->checkGateway();
  }

  /**
   * Add to the system a fixed adapter to the database connections.
   *
   * @param DbAdapterInterface $dbAdapter
   */
  public static function setDefaultAdapter(DbAdapterInterface $dbAdapter): void {
    self::$defaultAdapter = $dbAdapter;
  }

  /**
   * The default adapter connection.
   *
   * @return DbAdapterInterface
   */
  public static function getDefaultAdapter(): ?DbAdapterInterface {
    return self::$defaultAdapter;
  }

  public function select($query) {
    $sqlFileManager = new SqlFileManager();
    $sqlFileManager->sql = $query;
    $sqlFileManager->split();
    return $sqlFileManager;
  }

  public function get() {
    $this->checkGateway();

    $id = func_get_args();

    // Monta query
    $sql = "SELECT * FROM " . $this->name . " WHERE ";
    foreach ((array) $this->primary as $key => $primary) {

      // Mais chaves primarias que parametros no metodo
      if (! isset($id[$key])) {
        throw new \Exception("O método 'get' só funciona passando TODAS as chaves primárias de uma vez");
      }

      // Parametro na query
      $sql .= $primary . " = :" . $primary . " AND ";

      // Parametro, query segura
      $this->db->bind($primary, $id[$key]);
    }
    $sql = trim($sql, " AND ");

    // Resultado
    return $this->db->row($sql);
  }

  public function save(array $data) {
    $this->checkGateway();

    // Quando, no final, este estiver vazio é INSERT,
    // se houver algum valor, UPDATE
    // Se não houver a mesma quantidade de pks aqui
    // quanto no atributo $this->primary ERRO
    $validPks = array();
    foreach ((array) $this->primary as $primary) {
      if (isset($data[$primary])) {
        $validPks[$primary] = $data[$primary];
        unset($data[$primary]);
      }
    }

    /**
     * O array para salvar tem um número de PKs diferente do que esta setado no atributo $this->primary
     */
    if ($validPks && (count($validPks) != count($this->primary))) {
      throw new \Exception("Para utilizar o metodo 'save' é necessário informar todos os PKs para UPDATE ou nenhum para INSERT");
    }

    // Seleciona o metodo
    return ($validPks) ? $this->update($data, $validPks) : $this->insert($data);
  }

  public function insert(array $data) {
    $this->checkGateway();

    $sql = "INSERT INTO " . $this->name . " (";

    // Colunas
    foreach (array_keys($data) as $column) {
      $sql .= $column . ", ";
    }
    $sql = trim($sql, ', ') . ") VALUES (";

    // Valores
    foreach ($data as $column => $value) {
      if (! is_null($value)) {
        $sql .= ":" . $column . ", ";

        // Query segura
        $this->db->bind($column, $value);
      } else {
        $sql .= $column . " = NULL, ";
      }
    }
    $sql = trim($sql, ', ') . ")";

    // Roda query
    $this->db->query($sql);

    // Retorna id inserido
    return $this->db->lastInsertId();
  }

  public function update(array $data, array $where, $noWhereForSure = false) {
    $this->checkGateway();

    $sql = "UPDATE " . $this->name . " SET ";

    // Colunas
    foreach ($data as $column => $value) {
      if (! is_null($value)) {
        $sql .= $column . " = :" . $column . ", ";

        // Query segura
        $this->db->bind($column, $value);
      } else {
        $sql .= $column . " = NULL, ";
      }
    }

    /**
     * Indicando este atributo o sistema irá atualizar as colunas
     * em questão em todos os updates sem precisar indicar isso nos
     * arrays.
     */
    if ($this->onUpdate && is_array($this->onUpdate)) {
      foreach ($this->onUpdate as $column => $value) {
        if (! isset($data[$column])) {
          $sql .= $column . " = " . $value . ", ";
        }
      }
    }

    $sql = trim($sql, ', ');

    // Nenhum parametro where, locão
    if (! $where && ! $noWhereForSure) {
      throw new \Exception("Nenhuma coluna apontada no WHERE, se tiver certeza de que quer atualizar todos os registros da tabela informe true no parametro \$sure");

      // Sure indica que o WHERE nao vai ser utilizado
    } else if (! $noWhereForSure) {

      // Where
      $sql .= " WHERE ";
      foreach ($where as $column => $value) {
        $sql .= $column . " = :w_" . $column . " AND ";

        // Query segura
        $this->db->bind("w_" . $column, $value);
      }
      $sql = trim($sql, " AND ");
    }

    // Roda query
    return (bool) $this->db->query($sql);
  }

  public function delete(array $where) {
    $this->checkGateway();

    // Monta query
    $sql = "DELETE FROM " . $this->name . " WHERE ";
    foreach ($where as $column => $value) {

      // Parametro na query
      $sql .= $column . " = :" . $column . " AND ";

      // Parametro, query segura
      $this->db->bind($column, $value);
    }
    $sql = preg_replace("/\sAND\s$/", '', $sql);

    // Resultado
    return (bool) $this->db->query($sql);
  }

  /**
   * Checkup under gateway attributes.
   *
   * @return bool
   * @throws DatabaseGatewayException
   */
  private function checkGateway() {
    if (! $this->name || ! $this->primary) {
      throw new DatabaseGatewayException("Every Gateway file must to inform name and primary fields (".get_class($this).")");
		}
		return true;
	}
}
