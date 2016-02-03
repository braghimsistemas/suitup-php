<?php
namespace Braghim;

use Braghim\Enum\MsgType;

abstract class MvcAbstractController
{
	const MSG_NSP = 'MSGRedir';

	public static $authNsp = 'LogINAuth';
	
	// Parametros criados ao iniciar o sistema
	public static $params;
	
	// Variaveis que serao passadas para view
	private $view = array();
	
	// Mensagens do sistema
	private $msgs = array();

	/**
	 * Este metodo é chamado antes da ação do controlador.
	 * Se for sobrescrever ele, não esqueça de chama-lo.
	 * 
	 * ex.
	 * public function preDispatch() {
	 *		// Seu codigo aqui
	 * 
	 *		parent::preDispatch();
	 * 
	 *		// ou aqui
	 * }
	 */
	public function preDispatch()
	{
		// Mensagens que vieram por sessao, ou seja, com redirecionamento
		if (isset($_SESSION[$this->getMsgNsp()])) {
			foreach($_SESSION[$this->getMsgNsp()] as $token => $msgNsp) {
				
				// Adiciona ao layout somente mensagens com token diferente
				// do token atual, ou seja, somente mensagem que já existia antes
				// de chegar aqui, de outra página.
				// Loco neh!? ¯\_(-.-)_/¯
				if ($token != MSG_NSP_TOKEN) {
					foreach($msgNsp as $type => $msgs) {
						foreach($msgs as $msg) {
							$this->addMsg($msg, $type);
						}
					}
				}
			}
		}
	}
	
	/**
	 * O namespace eh relativo ao modulo, nao queremos
	 * misturar as mensagens de um modulo com outro
	 * 
	 * @return string
	 */
	public function getMsgNsp() {
		return $this->getModuleName().'_'.self::MSG_NSP;
	}

	/**
	 * Metodos padrão
	 */
	public function init() {}
	public function indexAction() {}
	
	// Error Controller
	public function errorAction() {}
	public function notFoundAction() {
		header("HTTP/1.0 404 Not Found");
	}
	
	/**
	 * Chamado depois da ação
	 */
	public function posDispatch()
	{
		// Adiciona login para ser visivel no layout
		$this->view['login'] = self::getLogin();
		
		// Injeta as variaveis que foram colocadas dentro de view
		foreach ((array) $this->view as $key => $var) {
			$$key = $var;
		}

		$layoutMessages = $this->msgs;

		// Caso tenha excessao
		if (isset(self::$params->exception)) {
			$exception = self::$params->exception;
		}
		
		// Verifica se o arquivo da View existe
		$viewFile = self::$params->viewPath . DIRECTORY_SEPARATOR . self::$params->viewName;
		if (!file_exists($viewFile)) {
			throw new \Exception("View '$viewFile' não existe, se este for um AJAX então utilize o método \$this->ajax()");
		}
		
		// Pega conteúdo da view
		ob_start();
		include $viewFile;
		$content = ob_get_clean();

		// mostra conteúdo do layout já com a view injetada
		include self::$params->layoutPath . DIRECTORY_SEPARATOR . self::$params->layoutName;
		
		// Remove possiveis mensagens de sessão
		// mas somente com o token antigo
		if (isset($_SESSION[$this->getMsgNsp()])) {
			foreach(array_keys($_SESSION[$this->getMsgNsp()]) as $token) {
				if ($token != MSG_NSP_TOKEN) {
					unset($_SESSION[$this->getMsgNsp()][$token]);
				}
			}
		}
	}
	
	public function getModuleName() {
		return str_replace('Module', '', self::$params->moduleName);
	}

	public function getControllerName() {
		return preg_replace("/Controller$/", '', self::$params->controllerName);
	}

	public function getActionName() {
		return preg_replace("/Action$/", '', self::$params->actionName);
	}

	public function getLayoutName() {
		return self::$params->layoutName;
	}
	
	/**
	 * Pega conteúdo da view
	 * Foi feito no arquivo functions.php, para ser usado mesmo pelo terminal.
	 * 
	 * @param type $renderViewName
	 * @param type $vars
	 * @param type $renderViewPath
	 * @return type
	 */
	public function renderView($renderViewName, $vars = array(), $renderViewPath = null) {
		if (!$renderViewPath) {
			$renderViewPath = self::$params->mainPath.DIRECTORY_SEPARATOR.self::$params->moduleName.'/views';
		}
		return renderView($renderViewName, $vars, $renderViewPath);
	}
	
	/**
	 * Adiciona uma variavel qualquer ao conjunto de variaveis que
	 * aparecerao na view.
	 * 
	 * @param string|array $name
	 * @param mixed $value
	 * @return MvcAbstractController
	 */
	protected function addViewVar($name, $value = null) {
		if (is_array($name)) {
			foreach ($name as $key => $val) {
				$this->view[$key] = $val;
			}
		} else {
			$this->view[$name] = $value;
		}
		return $this;
	}

	/**
	 * Troca o layout
	 * 
	 * @param type $name
	 * @param type $path
	 */
	public function setLayout($name, $path = null) {
		self::$params->layoutName = $name;
		if ($path) {
			self::$params->layoutPath = $path;
		}
	}

	/**
	 * Retorna todos os parametros GET
	 * 
	 * @return array
	 */
	public function getParams() {
		return $_GET;
	}

	/**
	 * Pega parametro do GET
	 * 
	 * @param type $name
	 * @param type $default
	 * @return type
	 */
	public function getParam($name, $default = null) {
		$params = $this->getParams();
		return isset($params[$name]) ? $params[$name] : $default;
	}

	public function isPost() {
		return (bool) ($_SERVER['REQUEST_METHOD'] === "POST");
	}
	
	public function getPost($name, $default = null) {
		return isset($_POST[$name]) ? $_POST[$name] : $default;
	}

	public function isLogged() {
		return (bool) isset($_SESSION[self::$authNsp]);
	}

	public static function getLogin($key = null) {
		$login = isset($_SESSION[self::$authNsp]) ? $_SESSION[self::$authNsp] : false;
		
		// Se pediu apenas um item do login e ela existe entao retorna, senao retorna todo o login
		return ($key && isset($login[$key])) ? $login[$key] : $login;
	}
	
	public static function updateLoginKey($key, $value) {
		if (isset($_SESSION[self::$authNsp][$key])) {
			$_SESSION[self::$authNsp][$key] = $value;
		}
	}

	/**
	 * Mensagens do sistema
	 */
	public function addMsg($msg, $type = MsgType::INFO, $withRedirect = false) {
		if ($withRedirect) {
			$_SESSION[$this->getMsgNsp()][MSG_NSP_TOKEN][$type][] = $msg;
		} else {
			$this->msgs[$type][] = $msg;
		}
		return $this;
	}

	/**
	 * Envia um email. Foi feito no arquivo functions.php, para ser usado mesmo pelo terminal.
	 * 
	 * @param array $to
	 * @param type $subject
	 * @param type $htmlBody
	 * @param type $emailType
	 * @return type
	 */
	public function sendEmail(array $to, $subject, $htmlBody, $emailType = 'contact') {
		return sendEmail($to, $subject, $htmlBody, $emailType);
	}
	
	/**
	 * Efetua upload de arquivos.
	 * 
	 * @param array $file Arquivo da variável $_FILES['somefile']
	 * @param string $where Caminho absoluto para onde salvar o arquivo, ex.: FILES_PATH . '/somewhere'
	 * @param string $exitFilename Nome do arquivo no final do processo de upload
	 * @param array $allowedExt Lista de extenssoes permitidas para o upload
	 * @return \stdClass
	 * @throws Exception
	 */
	public function uploadFile($file, $where, $exitFilename = null, array $allowedExt = array('jpeg', 'jpg', 'pdf', 'png', 'gif'))
	{
		$result = new \stdClass();
		$result->filename = "";
		$result->pathAndFilename = "";
		$result->fileExt = "";
		
		$upload = new \upload($file);
		if ($upload->uploaded)
		{
			// Formatos Aceitos (Extensoes)
			if (in_array($upload->file_src_name_ext, $allowedExt)) {
			
				// Formatos Aceitos (Mimetypes)
				$upload->allowed = array(
					'image/jpg',
					'image/pjpeg', // JPG no IE7 e IE8
					'image/jpeg', // JPG no Chrome, Safari, Opera e Firefox
					'image/png',
					'image/gif',
					'application/x-zip-compressed',
					'application/pdf', // PDF p/ IE8 e Mozilla e Chrome e Safari e Opera
					'application/download',
					'application/applefile'
				);
				$upload->Process($where);

				// Upload deu tudo certo?
				if ($upload->processed) {
					
					// Define resultado
					$result->fileExt = $upload->file_src_name_ext;
					$result->filename = $upload->file_dst_name;
					$result->pathAndFilename = $where.$upload->file_dst_name;
					
					// Permissao total no arquivo
					chmod($result->pathAndFilename, 0777);
					
					// Se for para mudar o nome do arquivo de saida
					if ($exitFilename) {
						
						// Renomeia
						if (!rename($result->pathAndFilename, $where.$exitFilename.'.'.$upload->file_src_name_ext)) {
							throw new Exception("Não foi possível mover o arquivo '{$result->pathAndFilename}'");
						}
						
						$result->filename = $exitFilename.'.'.$upload->file_src_name_ext;
						$result->pathAndFilename = $where.$exitFilename.'.'.$upload->file_src_name_ext;
					}
				} else {
					throw new \Exception($upload->error);
				}
			} else {
				throw new \Exception("Extenssão inválida");
			}
		} else {
			throw new \Exception($upload->error);
		}
		return $result;
	}
	
	/**
	 * Efetua redirecionamento
	 * 
	 * @param type $to
	 */
	public function redirect($to) {
		header("Location: $to");
		exit;
	}
	
	/**
	 * Quando a ação for um ajax basta inserir o conteudo do retorno
	 * neste metodo.
	 * 
	 * @param type $data
	 */
	public function ajax($data) {
		header("Content-Type: application/json; Charset=UTF-8");
		echo json_encode($data);
		exit;
	}
}
