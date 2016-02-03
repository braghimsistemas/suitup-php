<?php

// Helpers de html
include_once 'htmlHacks.php';

/**
 * Funcao CURL para acessar a URL e retornar o status do servidor.
 * 
 * @author Marco A. Braghim <marco.a.braghim@gmail.com>
 * @param type $url
 * @return type
 */
function isOnTheAir($url, $timeout = 30) {
	$mc = mctime();
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_exec($ch);
	
	// Resposta da requisicao
	$info = curl_getinfo($ch);
	$info['response_time'] = number_format(mctime() - $mc, 2);
	return $info;
}

/**
 * Envia e-mails.
 * 
 * @param array $to
 * @param type $subject
 * @param type $htmlBody
 * @param type $emailType
 * @return boolean
 * @throws Exception
 */
function sendEmail(array $to, $subject, $htmlBody, $emailType = 'contact')
{
	//Create a new PHPMailer instance
	$mail = new PHPMailer();
	$mail->CharSet = 'UTF-8';
	$mail->isSMTP();

	//Enable SMTP debugging
	// 0 = off (for production use)
	// 1 = client messages
	// 2 = client and server messages
//	$mail->SMTPDebug = DEVELOPMENT ? 2 : 0;
//	$mail->Debugoutput = 'html';

	//Set the hostname of the mail server
	$mail->Host = 'smtp.gmail.com';
	
	// use
	// $mail->Host = gethostbyname('smtp.gmail.com');
	// if your network does not support SMTP over IPv6

	// Busca parametros de email
	$emailsParams = include 'config/email.config.php';
	if (!isset($emailsParams[$emailType])) {
		throw new Exception("Cofigure o tipo de e-mail '$emailType'");
	}
	$params = $emailsParams[$emailType];
	
	$mail->Port = 587;
	$mail->SMTPSecure = 'tls';
	$mail->SMTPAuth = true;
	$mail->Username = $params['username'];
	$mail->Password = $params['password'];
	$mail->setFrom($params['username'], 'Hybrid');
	
	if (DEVELOPMENT) {
		$subject .= " Teste";
	}
	$mail->Subject = $subject;

	//Set who the message is to be sent to
	foreach ($to as $email => $name) {
		if (is_string($email)) {
			$mail->addAddress($email, $name);
			
		// Não tem nome, só email
		} else {
			$mail->addAddress($name);
		}
	}

	//Read an HTML message body from an external file, convert referenced images to embedded,
	//convert HTML into a basic plain-text alternative body
	$mail->msgHTML($htmlBody);

	//send the message, check for errors
	if (!$mail->send()) {
		error_log($mail->ErrorInfo . "\n\n\n" . $htmlBody, 3, 'var/logs/email/' . date('d-m-Y') . ".log");
		throw new Exception("Email não pode ser enviado");
	}
	return true;
}

/**
 * Tenta remover um arquivo, se ele existir, mas não puder ser excluido
 * o sistema ira criar um log.
 * 
 * @param type $filename
 * @return boolean
 */
function unlinkFile($filename) {
	if (is_file($filename) && (!is_writable($filename) || !unlink($filename))) {
		$logfile = "var/logs/exceptions-".date('dmY').".log";
		
		// Gera arquivo de log
		$log = "\n============\n";
		$log .= "O sistema não está conseguindo apagar imagens de perfis\n";
		$log .= "$filename\n";
		error_log($log, 3, $logfile);
		chmod($logfile, 0777);
		return false;
	}
	return true;
}

/**
 * Renderiza um html incluindo variaveis
 * 
 * @param type $renderViewName
 * @param type $vars
 * @param type $renderViewPath
 * @return type
 */
function renderView($renderViewName, $vars = array(), $renderViewPath = null) {
	if (!$renderViewPath) {
		$renderViewPath = Braghim\MvcAbstractController::$params->layoutPath;
	}
	
	// Injeta variaveis na view
	foreach ($vars as $n => $v) {
		$$n = $v;
	}

	ob_start();
	include $renderViewPath . DIRECTORY_SEPARATOR . $renderViewName;
	return ob_get_clean();
}

/**
 * Imagem que o usuário usou no perfil ou Gravatar
 *
 * @param string $email The email address
 * @param string $size Size in pixels, defaults to 80px [ 1 - 2048 ]
 * @return String containing either just a URL or a complete image tag
 * @source http://gravatar.com/site/implement/images/php/
 */
function getGravatar($email, $size = 80) {
	
	// Por padrao pega o gravatar do email.
	$profileImg = "//www.gravatar.com/avatar/".md5(strtolower(trim($email)))."?s=".$size.'&d=mm';
	
	// Se tem login e imagem de perfil
	$login = $_SESSION[\Braghim\MvcAbstractController::$authNsp];
	if (isset($login['picture']) && $login['picture']) {
		$profileImg = ASSETS_URL.'/image/profiles/'.$login['picture'];
	}
    return $profileImg;
}

/**
 * Funçao para debug simplificada, semelhante ao Zend\Debug.
 * 
 * @author Marco A. Braghim <marco.a.braghim@gmail.com>
 * @param type $var
 * @param type $echo
 */
function dump($var, $echo = true) {
	ob_start();
	var_dump($var);
	if (isset($argv)) {
		$output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", ob_get_clean()) . "\n\n";
	} else {
		$output = "<pre>".preg_replace("/\]\=\>\n(\s+)/m", "] => ", ob_get_clean()) . "</pre>";
	}
	if ($echo) {
		echo $output;
		exit;
	}
	return $output;
}

/**
 * Retorna o microtime em float.
 * 
 * @return type
 */
function mctime() { 
    list($usec, $sec) = explode(" ", microtime()); 
    return ((float)$usec + (float)$sec);
}

/**
 * Captura todas as exceções do sistema
 * 
 * @param Exception $e
 */
function throwNewExceptionFromAnywhere($e) {
	$result = resolve('ModuleError', 'error', 'error', 'library');
	$result->exception = $e;
	
	// Chama metodos por ordem
	$result->controller->preDispatch();
	$result->controller->init();
	$result->controller->{$result->actionName}();
	$result->controller->posDispatch();
}
