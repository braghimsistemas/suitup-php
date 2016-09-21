<?php
namespace Braghim;

abstract class AbstractBusiness
{
	/**
	 * @var AbstractGateway
	 */
	protected $gateway;
	
	public function __construct()
	{
		// Nome da classe
		$className = explode('\\', get_class($this));
		$className = array_pop($className);
		
		// Nome do gateway
		$gateway =  str_replace($className, 'Gateway', get_class($this)) .'\\'. str_replace('Business', '', $className);
		$this->gateway = new $gateway(AbstractGateway::SALT);
	}
	
	/**
	 * Retorna um unico registro por PKs.
	 * 
	 * @return array
	 * @throws \Exception
	 */
	public function get() {
		return call_user_func_array(array($this->gateway, 'get'), func_get_args());
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
