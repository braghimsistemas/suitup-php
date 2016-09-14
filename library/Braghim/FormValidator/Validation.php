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
			foreach ($item['validation'] as $methodOrClass => $method) {
				
				
				// Validacao do ZEND =)
				if (class_exists($methodOrClass)) {
					
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
					$validator = new $methodOrClass($options);
					
					if (!$validator->isValid($this->post[$field])) {
						$result = false;
						foreach ($validator->getMessages() as $msg) {
							$this->messages[$field][] = $msg;
						}
					}
					continue;
				}
				
				// É um metodo de validacao dentro da classe, mas que referencia outro campo
				if (method_exists($this, $methodOrClass)) {
					
					$options = $method;
					$validation = $this->$methodOrClass($this->post[$field], $options);
					if ($validation->error) {
						$result = false;
						$this->messages[$field][] = $validation->message;
					}
					continue;
				}
				
				// É um metodo comum de validacao dentro da propria classe
				if (method_exists($this, $method)) {
					$validation = $this->$method($this->post[$field]);
					if ($validation->error) {
						$result = false;
						$this->messages[$field][] = $validation->message;
					}
					continue;
				}
				
				// Se chegar aqui é pq não tem metodo para validar o campo =/
				throw new \Exception("O metodo '$methodOrClass' ou '$method' não existe para validar o campo");
			}
			
			// Filtros
			$this->data[$field]['value'] = $this->post[$field];
			if (isset($item['filter'])) {
				foreach ($item['filter'] as $withOptions => $method) {

					// É um metodo que tem opcoes
					if (method_exists($this, $withOptions)) {

						$filterOptions = $method;
						$this->data[$field]['value'] = $this->$withOptions($this->data[$field]['value'], $filterOptions);

					// É um metodo?
					} else if (method_exists($this, $method)) {
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
