<?php
namespace Braghim;

abstract class AbstractFormValidator
{
	protected $data = array();
	public $messages = array();
	public $post = array();
	private $valid = null;
	
	public function __construct() {
		$this->post = (array) filter_input_array(INPUT_POST);
	}
	
	/**
	 * Valida se um campo está vazio.
	 * 
	 * @param type $value
	 * @return \stdClass
	 */
	public function notEmpty($value) {
		$result = new \stdClass();
		$result->error = false;
		$result->message = "";
		
		if (!$value) {
			$result->error = true;
			$result->message = "Este campo não pode ficar vazio";
		}
		return $result;
	}
	
	/**
	 * Validacao de email. <b>Não verifica se o email está vazio</b>
	 * 
	 * @param string $value Se este parametro estiver vazio o resultado será <b>TRUE</b> (Válido)
	 * @return \stdClass
	 */
	public function isEmail($value) {
		$result = new \stdClass();
		$result->error = false;
		$result->message = "";
		
		// Nao verifica validade do email caso ele esteja vazio.
		if ($value && !preg_match("/^[a-zA-Z0-9][a-zA-Z0-9\._-]+@([a-zA-Z0-9\._-]+\.)[a-zA-Z-0-9]{2}/", $value)) {
			$result->error = true;
			$result->message = "Preencha com um endereço de e-mail válido";
		}
		return $result;
	}
	
	/**
	 * Validacao de CEP. <b>Não verifica se o cep está vazio</b>
	 * 
	 * @param string $value Cep no formato 99999-999
	 * @return \stdClass Objeto simples
	 */
	public function isCep($value) {
		$result = new \stdClass();
		$result->error = false;
		$result->message = "";
		
		// Se estiver vazio ignora
		if ($value && !preg_match("/^\d{5}-\d{3}$/", $value)) {
			$result->error = true;
			$result->message = "Preencha com um número de CEP válido";
		}
		return $result;
	}
	
	/**
	 * Filtro
	 * 
	 * @param type $value
	 * @return type
	 */
	public function trim($value) {
		return trim($value);
	}
	
	/**
	 * Filtro. Converte data no formato brasileiro para do banco.
	 * 
	 * @param type $value
	 * @return type
	 */
	public function toDbDate($value) {
		return implode('-', array_reverse(explode('/', $value)));
	}
	
	/**
	 * Verifica de acordo com as regras definidas para cada campo
	 * se todos os campos do formulario sao validos. Soh retorna
	 * true caso TODOS eles sejam validos.
	 * 
	 * @return boolean
	 */
	public function isValid()
	{
		if ($this->valid === null) {
			$this->validateForm();
		}
		return $this->valid;
	}
	
	/**
	 * Chame este metodo apos isValid() para capturar os
	 * dados do post "limpos", ou seja, filtrados pelas
	 * regras definidas para cada campo.
	 * 
	 * @return type
	 */
	public function getData() {
		
		// Se o formulario ainda não foi validado vamos valida-lo.
		if ($this->valid === null) {
			$this->validateForm();
		}
		
		// Captura os resultados da validacao
		$data = array();
		foreach ($this->data as $key => $item) {
			$data[$key] = $item['value'];
		}
		return $data;
	}
	
	/**
	 * Adiciona um item ao array de retorno dos dados.
	 * 
	 * @param type $index
	 * @param type $data
	 */
	public function addData($index, $data) {
		$this->data[$index] = $data;
	}
	
	/**
	 * Retorna lista de mensagens de validacao.
	 * 
	 * @return type
	 */
	public function getMessages() {
		return $this->messages;
	}
	
	/**
	 * Efetua as validacoes necessarias.
	 * 
	 * @throws \Exception
	 * @return bool
	 */
	private function validateForm()
	{
		$result = true;
		foreach ($this->data as $field => $item) {
			
			// Tipos de campos que nem chegaram no post
			if (!isset($this->post[$field])) {
				$this->setErrorMessage($field, "Este campo é obrigatório");
				continue;
			}
			
			// Validacoes
			foreach ($item['validation'] as $zendValidator => $method) {
				
				
				// Validacao do ZEND =)
				if (class_exists($zendValidator)) {
					
					// Campo não vazio, pois é claro que estando
					// vazio toda validacao será falsa.
					// Se quer validar se esta vazio deve utilizar
					// o metodo notEmpty
					if (!$this->post[$field]) {
						continue;
					}
					
					// Como é uma validacao do Zend
					// então nao é um metodo que chega como valor,
					// mas as opções do validador
					$options = $method;
					$validator = new $zendValidator($options);
					
					if (!$validator->isValid($this->post[$field])) {
						$result = false;
						foreach ($validator->getMessages() as $msg) {
							$this->messages[$field][] = $msg;
						}
					}
					continue;
				}
				
				if (!method_exists($this, $method)) {
					throw new \Exception("O metodo '$method' não existe para validar o campo");
				}
				
				$validation = $this->$method($this->post[$field]);
				if ($validation->error) {
					$result = false;
					$this->messages[$field][] = $validation->message;
				}
			}
			
			// Filtros
			$this->data[$field]['value'] = $this->post[$field];
			if (isset($item['filter'])) {
				foreach ($item['filter'] as $method) {
					// É um metodo?
					if (method_exists($this, $method)) {
						$this->data[$field]['value'] = $this->$method($this->data[$field]['value']);

					// É uma função?
					} else if (function_exists($method)) {
						$this->data[$field]['value'] = $method($this->data[$field]['value']);

					// Não! É um erro =S
					} else {
						throw new \Exception("O metodo '$method' não existe para filtrar o campo");
					}
				}
			}
		}
		
		// Se faltou algum campo do post que não
		// levou nenhum tipo de validacao nem filtro
		foreach ($this->post as $key => $value) {
			if (!isset($this->data[$key])) {
				$this->data[$key]['value'] = $value;
			}
		}
		
		// Seta o objeto
		$this->valid = (bool) $result;
		return $this->valid;
	}
}
