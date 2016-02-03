<?php
namespace Braghim;

/**
 * Metodos uteis para algumas funçoes simples relacionadas a strings.
 */
class StringHacks
{
	/**
	 * Este método não valida número de CNPJ, somente formata no padrão
	 * 01 234 567 8911 11 |
	 *              01 23 v
	 * 21.726.860/0001-87
	 * 
	 * @param type $cnpj
	 */
	public static function cnpjFormat($cnpj) {
		$number = preg_replace("/\D+/", "", $cnpj);
		$formatado = substr($number, 0, 2).'.';
		$formatado .= substr($number, 2, 3).'.';
		$formatado .= substr($number, 5, 3).'/';
		$formatado .= substr($number, 8, 4).'-';
		$formatado .= substr($number, 12, 2);
		return $formatado;
	}
}
