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
namespace SuitUp\Database\Business;

use SuitUp\Database\Gateway\AbstractGateway;

/**
 * Class AbstractBusiness
 * @package SuitUp\Database\Business
 */
abstract class AbstractBusiness
{

  /**
   * @var \SuitUp\Database\Gateway\AbstractGateway
   */
  protected $gateway;

  /**
   * AbstractBusiness constructor.
   */
  public function __construct() {
    // Nome da classe
    $className = explode('\\', get_class($this));
    $className = array_pop($className);

    // Nome do gateway
    $gateway = str_replace($className, 'Gateway', get_class($this)) . '\\' . str_replace('Business', '', $className);
    $this->gateway = new $gateway();
  }

  /**
   * Retorna um unico registro por PKs.
   * 
   * @return array
   * @throws \Exception
   */
  public function get() {
    return call_user_func_array(array(
      $this->gateway,
      'get'
    ), func_get_args());
  }

  /**
   * Seleciona automaticamente INSERT ou UPDATE. Este método so irá funcionar corretamente
   * se todas as chaves primárias da tabela forem AUTO INCREMENT, se não é melhor selecionar
   * o metodo na mão mesmo.
   * 
   * @param array $data
   * @return bool|string
   * @throws \Exception
   */
  public function save(array $data) {
    return $this->gateway->save($data);
  }

  /**
   * Monta automaticamente a query para inserir um registro no banco.
   * 
   * @param array $data
   * @return string
   */
  public function insert(array $data) {
    return $this->gateway->insert($data);
  }

  /**
   * Monta automaticamente a query para atualizar um registro no banco.
   * 
   * @param array $data Campos para serem modificados e seus valores.
   * @param array $where Campo com valor necessário para o banco identificar quais registros vao ser atualizados.
   * @param boolean $noWhereForSure com o $where vazio este parametro permite apagar todos os registros do banco.
   * @return boolean <b>false</b> Se nenhuma linha foi afetada
   * @throws \Exception
   */
  public function update(array $data, array $where, $noWhereForSure = false) {
    return $this->gateway->update($data, $where, $noWhereForSure);
  }

  /**
   * Remove um registro do banco de dados.
   *
   * @param array $where O que deve ser deletado
   * @return bool
   */
  public function delete(array $where) {
    return $this->gateway->delete($where);
  }
}
