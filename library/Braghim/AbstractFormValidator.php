<?php
namespace Braghim;

abstract class AbstractFormValidator extends FormValidator\Validation
{
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
	 * Valida um tamanho mínimo de caracteres para um campo.
	 * 
	 * @param mixed $value
	 * @param int $options [size, message]
	 * @return \stdClass
	 */
	public function minLen($value, $options)
	{
		$result = new \stdClass();
		$result->error = false;
		$result->message = "";
		
		// Valor para validar
		$size = (int) (isset($options['size'])) ? $options['size'] : $options;
		
		// Ignora vazio
		if ($value && (strlen($value) < $size)) {
			$result->error = true;
			$result->message = isset($options['message']) ? $options['message'] : "Este campo deve ter pelo menos $size caractéres";
		}
		return $result;
	}
	
	/**
	 * Valida um tamanho maximo de caracteres para um campo.
	 * 
	 * @param mixed $value
	 * @param int $options [size, message]
	 * @return \stdClass
	 */
	public function maxLen($value, $options)
	{
		$result = new \stdClass();
		$result->error = false;
		$result->message = "";
		
		// Valor para validar
		$size = (int) (isset($options['size'])) ? $options['size'] : $options;
		
		// Ignora vazio
		if ($value && (strlen($value) > $size)) {
			$result->error = true;
			$result->message = isset($options['message']) ? $options['message'] : "Este campo não deve ter mais que $size caractéres";
		}
		return $result;
	}
	
	/**
	 * Valida um campo que deve ser maior que o referenciado $_POST[target].
	 * A validação é feita numericamente, ou seja, não verifica o length de um campo.
	 * 
	 * @param mixed $value
	 * @param mixed $options [target, message]
	 * @return \stdClass
	 */
	public function maiorQue($value, $options)
	{
		$result = new \stdClass();
		$result->error = false;
		$result->message = "";
		
		$target = 0;
		if (isset($options['target'])) {
			$target = $this->post[$options['target']];
			
		} else if (isset($this->post[$options])) {
			$target = $this->post[$options];
			
		} else {
			$target = $options;
		}
			
		// Ignora vazio
		if ($value && ($this->toDouble($value) < $this->toDouble($target))) {
			$result->error = true;
			$result->message = isset($options['message']) ? $options['message'] : "Verifique que este campo não pode ser menor que o início";
		}
		return $result;
	}
	
	/**
	 * Valida um campo que deve ser menor que o referenciado [target].
	 * A validação é feita numericamente, ou seja, não verifica o length de um campo.
	 * 
	 * @param mixed $value
	 * @param mixed $options
	 * @return \stdClass
	 */
	public function menorQue($value, $options)
	{
		$result = new \stdClass();
		$result->error = false;
		$result->message = "";
		
		$target = 0;
		if (isset($options['target'])) {
			$target = $this->post[$options['target']];
			
		} else if (isset($this->post[$options])) {
			$target = $this->post[$options];
			
		} else {
			$target = $options;
		}
		
		// Ignora vazio
		if ($value && ($this->toDouble($value) > $this->toDouble($target))) {
			$result->error = true;
			$result->message = isset($options['message']) ? $options['message'] : "Verifique que este campo não pode ser maior que o fim";
		}
		return $result;
	}
	
	/**
	 * Valida dois campos que devem ser iguais, como senha e confirmação por exemplo.
	 * 
	 * @param mixed $value
	 * @param mixed $options
	 * @return \stdClass
	 */
	public function identico($value, $options)
	{
		$result = new \stdClass();
		$result->error = false;
		$result->message = "";
		
		$target = 0;
		if (isset($options['target'])) {
			$target = $this->post[$options['target']];
			
		} else if (isset($this->post[$options])) {
			$target = $this->post[$options];
			
		} else {
			$target = $options;
		}
		
		// Ignora vazio
		if ($value && ($value != $target)) {
			$result->error = true;
			$result->message = isset($options['message']) ? $options['message'] : "Campos não são idênticos";
		}
		return $result;
	}
	
	// ===============================================================
	//                         FILTROS
	// ===============================================================
	
	/**
	 * Remove espacos no inicio e fim da string.
	 * @param string $value
	 * @return string
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
	 * Retorna apenas os números do valor incluído.
	 * 
	 * @param type $value
	 * @return type
	 */
	public function digits($value) {
		return preg_replace("/\D+/", '', (string) $value);
	}
	
	/**
	 * Transforma valor que chegou em float (double).
	 * Claro que deve haver uma certa coerencia aqui né zé.
	 * Se o campo chegar 0,000.00 já vai dar merda.
	 * @TODO: i18n Necessário reformular para outros tipos de padrões de dinheiros
	 * 
	 * @param string $value Valor para ser filtrado
	 * @return float
	 */
	public function toDouble($value) {
		return (!$value) ? 0.0 : (double) preg_replace(array("/[^0-9,.]/", "/\./", "/\,/"), array('', '', '.'), $value);
	}
}
