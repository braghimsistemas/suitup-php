<?php
/**
 * Este arquivo prove funcoes para ajudar a montar um html mais rapidamente.
 * @author Braghim Sistemas <braghim.sistemas@gmail.com>
 */

/**
 * Monta lista de opções para ser usado dentro de um select.
 * 
 * @param mixed $selected
 * @param array $data
 * @return string
 */
function selectOptions($selected, array $data = array('' => 'Selecione!')) {
	$html = "";
	foreach($data as $value => $text) {
		if ($selected == $value) {
			$html .= '<option value="'.$value.'" selected="selected">'.$text.'</option>'."\n";
		} else {
			$html .= '<option value="'.$value.'">'.$text.'</option>'."\n";
		}
	}
	return $html;
}