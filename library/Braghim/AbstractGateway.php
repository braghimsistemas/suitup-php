<?php
namespace Braghim;

abstract class AbstractGateway
{
	/**
	 * Nome da tabela
	 * @var string
	 */
	protected $name;
	
	/**
	 * Chaves primarias da tabela
	 * @var mixed string|array
	 */
	protected $primary;
	
	/**
	 * Lista de chaves estrangeiras
	 * @var array
	 */
	protected $foreign = array();
	
	/**
	 * @var \Braghim\Database
	 */
	protected $db;
	
	public function __construct() {
		$this->db = Database::getInstance();
		
		// Validação 
		$this->checkGateway();
	}
	
	/**
	 * Encontra e faz a leitura do arquivo .sql baseado no nome da tabela.
	 * 
	 * @param type $filename
	 * @return type
	 * @throws \Exception
	 */
	public function sqlFile($filename) {
		return new SqlFileManager((string) $filename, $this->name);
	}
	
	/**
	 * Cria uma nova query a partir de uma string no lugar de usar arquivo.
	 * 
	 * @param string $query
	 * @return \Braghim\SqlFileManager
	 */
	public function query($query) {
		$sqlFileManager = new SqlFileManager();
		$sqlFileManager->sql = $query;
		$sqlFileManager->split();
		return $sqlFileManager;
	}
	
	/**
	 * Retorna um unico registro por PKs.
	 * 
	 * @return array
	 * @throws \Exception
	 */
	public function get()
	{
		$this->checkGateway();
		
		$id = func_get_args();
		
		// Monta query
		$sql = "SELECT * FROM ".$this->name." WHERE ";
		foreach((array) $this->primary as $key => $primary) {
			
			// Mais chaves primarias que parametros no metodo
			if (!isset($id[$key])) {
				throw new \Exception("O método 'get' só funciona passando TODAS as chaves primárias de uma vez");
			}
			
			// Parametro na query
			$sql .= $primary." = :".$primary." AND ";
			
			// Parametro, query segura
			$this->db->bind($primary, $id[$key]);
		}
		$sql = trim($sql, " AND ");
		
		// Resultado
		return $this->db->row($sql);
	}
	
	/**
	 * Seleciona automaticamente INSERT ou UPDATE. Este método so irá funcionar corretamente
	 * se todas as chaves primárias da tabela forem AUTO INCREMENT, se não é melhor selecionar
	 * o metodo na mão mesmo.
	 * 
	 * @param array $data
	 * @return type
	 * @throws \Exception
	 */
	public function save(array $data)
	{
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
	
	/**
	 * Monta automaticamente a query para inserir um registro no banco.
	 * 
	 * @param array $data
	 * @return type
	 */
	public function insert(array $data)
	{
		$this->checkGateway();
		
		$sql = "INSERT INTO ".$this->name." (";
		
		// Colunas
		foreach(array_keys($data) as $column) {
			$sql .= $column.", ";
		}
		$sql = trim($sql, ', ').") VALUES (";
		
		// Valores
		foreach($data as $column => $value) {
			$sql .= ":".$column.", ";
			
			// Query segura
			$this->db->bind($column, $value);
		}
		$sql = trim($sql, ', ').")";
		
		// Roda query
		$this->db->query($sql);
		
		// Retorna id inserido
		return $this->db->lastInsertId();
	}
	
	/**
	 * Monta automaticamente a query para atualizar um registro no banco.
	 * 
	 * @param array $data
	 * @param array $where
	 * @param type $sure com o $where vazio este parametro permite apagar todos os registros do banco.
	 * @return type
	 * @throws \Exception
	 */
	public function update(array $data, array $where, $sure = false)
	{
		$this->checkGateway();
		
		$sql = "UPDATE ".$this->name." SET ";
		
		// Colunas
		foreach($data as $column => $value) {
			$sql .= $column." = :".$column.", ";
			
			// Query segura
			$this->db->bind($column, $value);
		}
		$sql = trim($sql, ', ');
		
		// Nenhum parametro where, locão
		if (!$where && !$sure) {
			throw new \Exception("Nenhuma coluna apontada no WHERE, se tiver certeza de que quer atualizar todos os registros da tabela informe true no parametro \$sure");
			
		// Sure indica que o WHERE nao vai ser utilizado
		} else if (!$sure) {
			
			// Where
			$sql .= " WHERE ";
			foreach ($where as $column => $value) {
				$sql .= $column." = :w_".$column." AND ";
				
				// Query segura
				$this->db->bind("w_".$column, $value);
			}
			$sql = trim($sql, " AND ");
		}
		
		// Roda query
		return (bool) $this->db->query($sql);
	}
	
	/**
	 * Remove um registro do banco de dados.
	 * 
	 * @return type
	 */
	public function delete(array $where) {
		$this->checkGateway();
		
		// Monta query
		$sql = "DELETE FROM ".$this->name." WHERE ";
		foreach($where as $column => $value) {
			
			// Parametro na query
			$sql .= $column." = :".$column." AND ";
			
			// Parametro, query segura
			$this->db->bind($column, $value);
		}
		$sql = preg_replace("/\sAND\s$/", '', $sql);
		
		// Resultado
		return (bool) $this->db->query($sql);
	}
	
	/**
	 * Verifica se este gateway está configurado corretamente.
	 * 
	 * @return boolean
	 * @throws \Exception
	 */
	private function checkGateway() {
		// Valida noma da tabela e chaves primarias
		if (!$this->name || !$this->primary) {
			throw new \Exception("Indique o nome da tabela e as colunas chave primária (".get_class($this).")");
		}
		return true;
	}
}