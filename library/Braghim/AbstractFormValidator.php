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
}
