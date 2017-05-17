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
namespace SuitUp\FormValidator;

/**
 * Class AbstractFormValidator
 * @package SuitUp\FormValidator
 */
abstract class AbstractFormValidator extends Validation
{
	/**
	 * Check if the $_POST form field is empty.
	 * 
	 * @param mixed $value Form field value to be compared.
	 * @param mixed $options Custom message to error
	 * @return \stdClass
	 */
	public function notEmpty($value, $options = null) {
		$result = new \stdClass();
		$result->error = false;
		$result->message = "";
		
		// Default message pt_BR
		$message = 'Este campo não pode ficar vazio';
		
		// If have message from $options
		if (isset($options['message'])) {
			$message = $options['message'];
		} else if (is_string($options) && !empty($options)) {
			$message = $options;
		}
		
		if (!$value) {
			$result->error = true;
			$result->message = $message;
		}
		return $result;
	}
	
	/**
	 * E-mail validation. <b>do not check if the form field is empty</b>
	 * 
	 * @param string $value Form field value to be compared.
	 * @param mixed $options Custom message to error
	 * @return \stdClass
	 */
	public function isEmail($value, $options = null) {
		$result = new \stdClass();
		$result->error = false;
		$result->message = "";
		
		// Default message pt_BR
		$message = "Preencha com um endereço de e-mail válido";
		
		// If have message from $options
		if (isset($options['message'])) {
			$message = $options['message'];
		} else if (is_string($options) && !empty($options)) {
			$message = $options;
		}
		
		if ($value && !preg_match("/^[a-zA-Z0-9][a-zA-Z0-9\._-]+@([a-zA-Z0-9\._-]+\.)[a-zA-Z-0-9]{2}/", $value)) {
			$result->error = true;
			$result->message = $message;
		}
		return $result;
	}
	
	/**
	 * CEP number validation. CEP is the Zip-Code for shipping system from Brazil.
	 * 
	 * @param string $value Form field value to be compared. CEP number formated like 99999-999 or 999999999
	 * @param mixed $options Custom message to error
	 * @return \stdClass
	 */
	public function isCep($value, $options = null) {
		$result = new \stdClass();
		$result->error = false;
		$result->message = "";
		
		// Default message pt_BR
		$message = "Preencha com um número de CEP válido";
		
		// If have message from $options
		if (isset($options['message'])) {
			$message = $options['message'];
		} else if (is_string($options) && !empty($options)) {
			$message = $options;
		}
		
		// Ignored if the field is empty
		if ($value && !preg_match("/^\d{5}(-?)\d{3}$/", $value)) {
			$result->error = true;
			$result->message = $message;
		}
		return $result;
	}
	
	/**
	 * Verify if this field has the mininum length size indicated by the option.
	 * 
	 * @param mixed $value Form field value to be compared.
	 * @param int $options [size, message]
	 *		size: The minimun length size to the field;
	 *		message: The custom message to be dispatched.
	 * @return \stdClass
	 */
	public function minLen($value, $options = null)
	{
		$result = new \stdClass();
		$result->error = false;
		$result->message = "";
		
		// Length size
		$size = (isset($options['size'])) ? $options['size'] : $options;
		
		// size?
		if (null == $size) {
			throw new \Exception("Required 'size' with the number of min length accepted.");
		}
		
		// Ignored if empty
		if ($value && (strlen($value) < (int) $size)) {
			$result->error = true;
			$result->message = isset($options['message']) ? $options['message'] : "Este campo deve ter pelo menos $size caractéres";
		}
		return $result;
	}
	
	/**
	 * Verify if this field has the maximum length size indicated by the option.
	 * 
	 * @param mixed $value Form field value to be compared.
	 * @param int $options [size, message]
	 *		size: The minimun length size to the field;
	 *		message: The custom message to be dispatched.
	 * @return \stdClass
	 */
	public function maxLen($value, $options = null)
	{
		$result = new \stdClass();
		$result->error = false;
		$result->message = "";
		
		// Length size
		$size = (int) (isset($options['size'])) ? $options['size'] : $options;
		
		// size?
		if (null == $size) {
			throw new \Exception("Required 'size' with the number of max length accepted.");
		}
		
		// Ignored if empty
		if ($value && (strlen($value) > $size)) {
			$result->error = true;
			$result->message = isset($options['message']) ? $options['message'] : "Este campo não deve ter mais que $size caractéres";
		}
		return $result;
	}
	
	/**
	 * The field which contains this validation have to be
	 * greater than the field indicated in the target option.
	 * This validation is numeric made, not about the length.
	 * 
	 * @deprecated
	 * @see greaterThan
	 * @param mixed $value Form field value to be compared.
	 * @param mixed $options [target, message]
	 * 		Target: Another $_POST form to compare to;
	 * 		Message: A custom message to be dispatch in error case.
	 * @return \stdClass
	 */
	public function maiorQue($value, $options = null) {
		return $this->greaterThan($value, $options);
	}
	
	/**
	 * The field which contains this validation have to be
	 * greater than the field indicated in the target option.
	 * This validation is numeric made, not about the length.
	 * 
	 * @param mixed $value Form field value to be compared.
	 * @param mixed $options [target, message]
	 * 		Target: Another $_POST form to compare to;
	 * 		Message: A custom message to be dispatch in error case.
	 * @return \stdClass
	 */
	public function greaterThan($value, $options = null)
	{
		$result = new \stdClass();
		$result->error = false;
		$result->message = "";
		
		if (isset($options['target'])) {
			$target = $this->post[$options['target']];
			
		} else if (isset($this->post[$options])) {
			$target = $this->post[$options];
			
		} else {
			$target = $options;
		}
		
		// Target?
		if (null == $target) {
			throw new \Exception("Required 'target' with the name of the other field to compare.");
		}
			
		// Ignored if empty
		if ($value && ($this->toDouble($value) < $this->toDouble($target))) {
			$result->error = true;
			$result->message = isset($options['message']) ? $options['message'] : "Verifique que este campo não pode ser menor que o início";
		}
		return $result;
	}
	
	/**
	 * The field which contains this validation have to be
	 * less than the field indicated in the target option.
	 * This validation is numeric made, not about the length.
	 * 
	 * @deprecated
	 * @see lessThan
	 * @param mixed $value Value from $_POST form to validate
	 * @param mixed $options [target, message]
	 * 		Target: Another $_POST form to compare to;
	 * 		Message: A custom message to be dispatch in error case.
	 * @return \stdClass
	 */
	public function menorQue($value, $options = null) {
		return $this->lessThan($value, $options);
	}
	
	/**
	 * The field which contains this validation have to be
	 * less than the field indicated in the target option.
	 * This validation is numeric made, not about the length.
	 * 
	 * @param mixed $value Value from $_POST form to validate
	 * @param mixed $options [target, message]
	 * 		Target: Another $_POST form to compare to;
	 * 		Message: A custom message to be dispatch in error case.
	 * @return \stdClass
	 */
	public function lessThan($value, $options = null)
	{
		$result = new \stdClass();
		$result->error = false;
		$result->message = "";
		
		if (isset($options['target'])) {
			$target = $this->post[$options['target']];
			
		} else if (isset($this->post[$options])) {
			$target = $this->post[$options];
			
		} else {
			$target = $options;
		}
		
		// Target?
		if (null == $target) {
			throw new \Exception("Required 'target' with the name of the other field to compare.");
		}
		
		// Ignored if empty
		if ($value && ($this->toDouble($value) > $this->toDouble($target))) {
			$result->error = true;
			$result->message = isset($options['message']) ? $options['message'] : "Verifique que este campo não pode ser maior que o fim";
		}
		return $result;
	}
	
	/**
	 * Compare two $_POST form fields that must to be identical.
	 * 
	 * @deprecated
	 * @see identicalTo
	 * @param mixed $value Value from $_POST form
	 * @param mixed $options [target, message]
	 * 		Target: Another $_POST form to compare to;
	 * 		Message: A custom message to be dispatch in error case.
	 * @see identicalTo
	 * @return \stdClass
	 */
	public function identico($value, $options = null) {
		return $this->identicalTo($value, $options);
	}
	
	/**
	 * Compare two $_POST form fields that must to be identical.
	 * 
	 * @param mixed $value Value from $_POST form
	 * @param mixed $options [target, message]
	 * 		Target: Another $_POST form to compare to;
	 * 		Message: A custom message to be dispatch in error case.
	 * @return \stdClass
	 */
	public function identicalTo($value, $options = null)
	{
		$result = new \stdClass();
		$result->error = false;
		$result->message = "";
		
		if (isset($options['target'])) {
			$target = $this->post[$options['target']];
			
		} else if (isset($this->post[$options])) {
			$target = $this->post[$options];
			
		} else {
			$target = $options;
		}
		
		// Target?
		if (null == $target) {
			throw new \Exception("Required 'target' with the name of the other field to compare.");
		}
		
		// Ignora vazio
		if ($value && ($value != $target)) {
			$result->error = true;
			$result->message = isset($options['message']) ? $options['message'] : "Campos não são idênticos";
		}
		return $result;
	}

	/**
	 * Check if the field $_POST value exists in the given array by options.
	 * 
	 * @param $value Form field value to be compared.
	 * @param array $options You have the option of use default message and give just the array
	 * 		list to search for the field $_POST value, but can use $options to give a custom message:
	 * 		message: Custom message in error case;
	 *		values: Array list to search for the field $_POST value;
	 * @return \stdClass
	 */
	public function inArray($value, array $options = array()) {
		$result = new \stdClass();
		$result->error = false;
		$result->message = "";

		// Array values to search value
		$compare = array();
		
		// Default message pt_BR
		$message = "Este valor não está entre as opções";
		
		// Custom message
		if (isset($options['message'])) {
			
			$message = $options['message'];
			
			// Have the array values to search?
			if (!isset($options['values'])) {
				throw new Exception('If $options["message"] is setted, please set $options["values"] as the array values to search for.');
			}
			$compare = $options['values'];
			
		} else {
			$compare = $options;
		}
		
		if ($value && !in_array($value, $compare)) {
			$result->error = true;
			$result->message = $message;
		}
		return $result;
	}
	
	// ===============================================================
	//                         FILTERS
	// ===============================================================

	/**
	 * Remove white spaces from begin and the end of the form field and
	 * protect against tags injection.
	 * 
	 * @param $value Form field value to be filtered.
	 * @return string
	 */
	public function string($value) {
		return strip_tags(trim($value));
	}

	/**
	 * Remove white spaces from begin and the end of the form field.
	 * 
	 * @param string $value Form field value to be filtered.
	 * @return string
	 */
	public function trim($value) {
		return trim($value);
	}

	/**
	 * Convertion from brazilian date format (dd/mm/yyyy) to database format (yyyy-mm-dd).
	 * 
	 * @param string $value Form field value to be filtered.
	 * @return string
	 */
	public function toDbDate($value) {
		return implode('-', array_reverse(explode('/', $value)));
	}
	
	/**
	 * Remove everything that is not a number from the form field.
	 * 
	 * @param string $value Form field value to be filtered.
	 * @return string
	 */
	public function digits($value) {
		return preg_replace("/\D+/", '', (string) $value);
	}
	
	/**
	 * Transform the value from form field to float (double). This method
	 * is used by others methods and have to get the string format (9.999,99).
	 * <b>If you use the format 9,999.99 so we recomand you to override these methdos</b>.
	 * 
	 * @TODO: i18n Necessário reformular para outros tipos de padrões de dinheiros
	 * 
	 * @param string $value Form field value to be filtered.
	 * @param array $options Default value
	 * @throws \Exception Obrigatori um indice 'default' nas opcoes
	 * @return float
	 */
	public function toDouble($value, $options = array('default' => 0.0)) {

		if (!isset($options['default'])) {
			throw new \Exception("O filtro 'toDouble' necessita de um índice de opções 'default'");
		}
		
		// Already is float or double
		if (gettype($value) == 'float' || gettype($value) == 'double') {
			return $value;
			
		} else if (gettype($value) == 'integer') {
			return (double) $value;
		}
		
		return (!$value) ? $options['default'] : (double) preg_replace(array("/[^0-9,.]/", "/\./", "/\,/"), array('', '', '.'), $value);
	}
}
