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
		$this->gateway = new $gateway();
	}
	
	public function get() {
		return call_user_func_array(array($this->gateway, 'get'), func_get_args());
	}
	
	public function save(array $data) {
		return $this->gateway->save($data);
	}
	
	public function insert(array $data) {
		return $this->gateway->insert($data);
	}
	
	public function update(array $data, array $where, $sure = false) {
		return $this->gateway->update($data, $where, $sure);
	}
	
	public function delete(array $where) {
		return $this->gateway->delete($where);
	}
}
