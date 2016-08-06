<?php
namespace Braghim\FormValidator;

abstract class Validation
{
	protected $data = array();
	public $messages = array();
	public $post = array();
	private $valid = null;
	
	public function __construct() {
		$this->post = (array) filter_input_array(INPUT_POST);
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
	 * Chame este metodo para capturar os dados do post "limpos", ou seja, filtrados pelas
	 * regras definidas para cada campo.
	 * 
	 * Se o formulário ainda não estiver validado a validação é feita, ou seja, depois de chamar este método
	 * ou o isValid não é mais possível adicionar campos para validação.
	 * 
	 * @return array
	 */
	public function getData() {
		
		// Se o formulario ainda não foi validado vamos valida-lo.
		if ($this->valid === null) {
			$this->validateForm();
		}
		
		// Captura os resultados da validacao
		$data = array();
		foreach ($this->data as $key => $item) {
			$data[$key] = isset($item['value']) ? $item['value'] : '';
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
				$this->messages[$field][] = "Este campo é obrigatório";
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
