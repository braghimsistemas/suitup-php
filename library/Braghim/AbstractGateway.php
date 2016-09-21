<?php
namespace Braghim;

abstract class AbstractGateway
{
	/**
	 * Validacao de instancia da gateway.
	 */
	const SALT = '56fas43df5a7';
	
	/**
	 * Nome da tabela
	 * @var string
	 */
	protected $name;
	
	/**
	 * Chaves primarias da tabela
	 * @var string|array
	 */
	protected $primary;
	
	/**
	 * Eh possivel configurar uma coluna do banco para ser atualizada
	 * em cada update automaticamente.
	 *
	 * @var array 
	 */
	protected $onUpdate;
	
	/**
	 * @var \Braghim\Database
	 */
	protected $db;
	
	/**
	 * O construtor recebe este parâmetro estático
	 * para evitar que um Gateway seja instanciado
	 * por acidente.
	 * 
	 * @param bool|string $valid
	 * @throws \Exception
	 */
	public function __construct($valid = false) {
		
		// Valida instancia da classe
		// @TODO: Avaliar outras possibilidades
		if (!$valid || ($valid != self::SALT)) {
			throw new \Exception("Não utilize uma instância de 'Gateway' fora de sua respectiva 'Business'");
		}
		
		$this->db = Database::getInstance();
		
		// Validação 
		$this->checkGateway();
	}
	
	/**
	 * Encontra e faz a leitura do arquivo .sql baseado no nome da tabela.
	 * 
	 * @param string $filename
	 * @return \Braghim\SqlFileManager
	 * @throws \Exception
	 */
	public function sqlFile($filename) {
		
		// Recupera pasta do modulo e nome da classe (com seus namespaces)
		$folderModule = MvcAbstractController::$params->moduleName;
		$className = get_class($this);
		
		// Trata nome da pasta.
		$modelNspc = trim(preg_replace(
			array("/".$folderModule."/", "/(Gateway).+/"),
			array("", ""),
			$className
		), "\\");
		
		return new SqlFileManager((string) $filename, $this->name, $modelNspc);
	}
	
	/**
	 * Cria uma nova query a partir de uma string no lugar de usar arquivo.
	 * 
	 * @param string $query
	 * @return \Braghim\SqlFileManager
	 */
	public function select($query) {
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
	 * @return bool|string
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
	 * @return string
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
			if (!is_null($value)) {
				$sql .= ":".$column.", ";

				// Query segura
				$this->db->bind($column, $value);
			} else {
				$sql .= $column." = NULL, ";
			}
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
	 * @param array $data Campos para serem modificados e seus valores.
	 * @param array $where Campo com valor necessário para o banco identificar quais registros vao ser atualizados.
	 * @param boolean $noWhereForSure com o $where vazio este parametro permite apagar todos os registros do banco.
	 * @return boolean false Se nenhuma linha foi afetada
	 * @throws \Exception
	 */
	public function update(array $data, array $where, $noWhereForSure = false)
	{
		$this->checkGateway();
		
		$sql = "UPDATE ".$this->name." SET ";
		
		// Colunas
		foreach($data as $column => $value) {
			if (!is_null($value)) {
				$sql .= $column." = :".$column.", ";

				// Query segura
				$this->db->bind($column, $value);
			} else {
				$sql .= $column." = NULL, ";
			}
		}
		
		/**
		 * Indicando este atributo o sistema irá atualizar as colunas
		 * em questão em todos os updates sem precisar indicar isso nos
		 * arrays.
		 */
		if ($this->onUpdate && is_array($this->onUpdate)) {
			foreach ($this->onUpdate as $column => $value) {
				if (!isset($data[$column])) {
					$sql .= $column." = ".$value.", ";
				}
			}
		}
		
		$sql = trim($sql, ', ');
		
		// Nenhum parametro where, locão
		if (!$where && !$noWhereForSure) {
			throw new \Exception("Nenhuma coluna apontada no WHERE, se tiver certeza de que quer atualizar todos os registros da tabela informe true no parametro \$sure");
			
		// Sure indica que o WHERE nao vai ser utilizado
		} else if (!$noWhereForSure) {
			
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
	 * @return bool
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