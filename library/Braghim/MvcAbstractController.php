<?php
namespace Braghim;

use Braghim\Enum\MsgType;
use Exception;
use ModuleManager\ModelLogs\Gateway\Log;
use stdClass;
use System\Exception\ExceptionErroSistema;
use upload;

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
	 * Tipos de erro de upload de arquivos.
	 * @var array
	 */
	public static $uploadErrors = array(
		UPLOAD_ERR_OK => 'UPLOAD_ERR_OK',
		UPLOAD_ERR_INI_SIZE => 'UPLOAD_ERR_INI_SIZE',
		UPLOAD_ERR_FORM_SIZE => 'UPLOAD_ERR_FORM_SIZE',
		UPLOAD_ERR_PARTIAL => 'UPLOAD_ERR_PARTIAL',
		UPLOAD_ERR_NO_FILE => 'UPLOAD_ERR_NO_FILE',
		UPLOAD_ERR_NO_TMP_DIR => 'UPLOAD_ERR_NO_TMP_DIR',
		UPLOAD_ERR_CANT_WRITE => 'UPLOAD_ERR_CANT_WRITE',
		UPLOAD_ERR_EXTENSION => 'UPLOAD_ERR_EXTENSION',
	);

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
	 * Metodos padrão
	 */
	public function init() {}
	public function indexAction() {}
	
	// Error Controller
	public function errorAction() {
		header(getenv('SERVER_PROTOCOL').' 500 Internal Server Error', true, 500);
	}
	public function notFoundAction() {
		header(getenv('SERVER_PROTOCOL').' 404 Not Found', true, 404);
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
		
		// Expressao regular para remover barras repetidas.
		$er = "/\\".DIRECTORY_SEPARATOR."\\".DIRECTORY_SEPARATOR."+/";
		
		// Verifica se o arquivo da View existe
		$viewFile =  preg_replace($er, DIRECTORY_SEPARATOR, self::$params->viewPath.DIRECTORY_SEPARATOR.self::$params->viewName);
		if (!file_exists($viewFile)) {
			throw new Exception("View '$viewFile' não existe, se este for um AJAX então utilize o método \$this->ajax()");
		}
		
		// Inclui o arquivo de layout
		$layoutfile = preg_replace($er, DIRECTORY_SEPARATOR, self::$params->layoutPath.DIRECTORY_SEPARATOR.self::$params->layoutName);
		if (!file_exists($layoutfile)) {
			throw new Exception("Arquivo de layout '$layoutfile' não existe, se este for um AJAX então utilize o método \$this->ajax()");
		}
		
		// Lista de instruções SQL rodadas nesta pagina
		$queryLog = Database::getInstance()->getQueryLog();
		
		// Pega conteúdo da view
		ob_start();
		include $viewFile;
		$content = ob_get_clean();

		// mostra conteúdo do layout já com a view injetada
		include $layoutfile;
		
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
	 * Retorna o nome do módulo.
	 * @return type
	 */
	public function getModuleName() {
		return lcfirst(str_replace('Module', '', self::$params->moduleName));
	}

	/**
	 * Nome do controlador
	 * 
	 * @return type
	 */
	public function getControllerName() {
		return lcfirst(preg_replace("/Controller$/", '', self::$params->controllerName));
	}

	/**
	 * Nome da ação.
	 * 
	 * @return type
	 */
	public function getActionName() {
		return lcfirst(preg_replace("/Action$/", '', self::$params->actionName));
	}

	/**
	 * Nome do arquivo de layout.
	 * @return type
	 */
	public function getLayoutName() {
		return self::$params->layoutName;
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
	 * Retorna true se a variavel existe na lista.
	 * 
	 * @param string $name
	 * @return bool
	 */
	protected function isViewVar($name) {
		return isset($this->view[$name]);
	}
	
	/**
	 * Se a variavel da view com este nome existir retorna seu conteudo.
	 * 
	 * @param string $name
	 * @return mixed
	 */
	protected function getViewVar($name) {
		return $this->isViewVar($name) ? $this->view[$name] : false;
	}

	/**
	 * Retorna todos os parametros GET
	 * 
	 * @return array
	 */
	public function getParams() {
		$routeParams = Routes::getInstance()->getParams();
		return array_merge((array) filter_input_array(INPUT_GET), $routeParams);
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

	/**
	 * True caso houve um post.
	 * @return type
	 */
	public function isPost() {
		return (bool) (getenv('REQUEST_METHOD') === "POST");
	}
	
	/**
	 * Retorna indice do POST.
	 * 
	 * @param string $name Indice desejado.
	 * @param mixed $default Valor que será retornado caso o indice nao exista.
	 * @return type
	 */
	public function getPost($name = null, $default = null) {
		$post = (array) filter_input_array(INPUT_POST);
		
		if ($name) {
			return isset($post[$name]) ? $post[$name] : $default;
		}
		return $post;
	}

	/**
	 * True caso o usuário tenha sessão de login.
	 * @return type
	 */
	public static function isLogged() {
		return (bool) isset($_SESSION[self::$authNsp]);
	}

	/**
	 * Retorna tudo que está gravado na sessão de login.
	 * 
	 * @param type $key
	 * @return type
	 */
	public static function getLogin($key = null, $default = null) {
		$login = isset($_SESSION[self::$authNsp]) ? $_SESSION[self::$authNsp] : false;
		
		if ($key) {
			if (!isset($login[$key]) || !$login[$key]) {
				return $default;

			} else {
				return $login[$key];
			}
		} else {
			return $login;
		}
	}
	
	/**
	 * Atualiza um indice da sessão de login.
	 * 
	 * @param type $key
	 * @param type $value
	 */
	public static function updateLoginKey($key, $value) {
		$login = (array) self::getLogin();
		
		// Isset não funciona aqui, maior loucura... =/
		foreach (array_keys($login) as $i) {
			if ($key == $i) {
				$_SESSION[self::$authNsp][$key] = $value;
			}
		}
	}

	/**
	 * Mensagens do sistema com ou sem redirecionamento
	 * 
	 * @param type $msg
	 * @param type $type
	 * @param type $withRedirect
	 * @return MvcAbstractController
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
	 * Efetua upload de arquivos.
	 * 
	 * @param array $file Arquivo da variável $_FILES['somefile']
	 * @param string $where Caminho absoluto para onde salvar o arquivo, ex.: FILES_PATH . '/somewhere'
	 * @param string $exitFilename Nome do arquivo no final do processo de upload
	 * @param array $allowedExt Lista de extenssoes permitidas para o upload
	 * @return stdClass
	 * @throws Exception
	 */
	public function uploadFile($file, $where, $exitFilename = null, array $allowedExt = array('jpeg', 'jpg', 'pdf', 'png', 'gif'))
	{
		$result = new stdClass();
		$result->filename = "";
		$result->pathAndFilename = "";
		$result->fileExt = "";
		
		$upload = new upload($file);
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
					throw new Exception($upload->error);
				}
			} else {
				throw new Exception("Extenssão inválida");
			}
		} else {
			throw new Exception($upload->error);
		}
		return $result;
	}
	
	/**
	 * Captura uma imagem e transforma seu conteúdo para Base64.
	 * - Deixa a imagem em torno de 33% maior segundo a documentação do PHP.
	 * 
	 * @param type $file Item $_FILES['arquivo']
	 * @param int $maxFilesize 512kb por padrão.
	 */
	public function uploadFileImageBase64($file, $maxFilesize = 524288)
	{
		// Valida erros
		if ($file['error'] != UPLOAD_ERR_OK) {
			
			ExceptionErroSistema::createLog(new Exception("Erro de Upload: ".self::$uploadErrors[$file['error']]), Log::WARN);
			throw new Exception("Erro ao fazer o upload da imagem, tente novamente");
		}
		
		// Valida tamanho
		if ($file['size'] > $maxFilesize) {
			throw new Exception("Arquivo muito grande, por favor envie um arquivo até ".($maxFilesize/MB)."Mb");
		}
		
		// Define exts e mimetypes
		$mimeTypes = array(
			'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpe' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'bmp' => 'image/bmp',
		);
		
		// Valida EXT
		$fileExt = preg_replace("/^.+\./", '', $file['name']);
		if (!array_key_exists($fileExt, $mimeTypes)) {
			throw new Exception("Extensão do arquivo é inválida, por favor envie arquivos desta lista (".implode(", ", array_keys($mimeTypes)).")");
		}
		
		// Valida MimeType
		if (!isset($mimeTypes[$fileExt]) || ($file['type'] != $mimeTypes[$fileExt])) {
			throw new Exception("Arquivo inválido, por favor envie arquivos desta lista (".implode(", ", array_keys($mimeTypes)).")");
		}
		
		// Valida se o arquivo existe.
		if (!file_exists($file['tmp_name']) || !is_readable($file['tmp_name'])) {
			throw new Exception("Erro inesperado ao fazer upload do arquivo, tente novamente");
		}
		// Não deu erro até aqui, então codifica o cara para base64
		return 'data:'.$mimeTypes[$fileExt].';base64,'.base64_encode(file_get_contents($file['tmp_name']));
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
	public function ajax(array $data) {
		header("Content-Type: application/json; Charset=UTF-8");
		echo json_encode($data);
		exit;
	}
	
	/**
	 * Retorna o que tem gravado na sessao de filtros.
	 * 
	 * @return type
	 */
	public function getSessionFilter() {
		$namespace = implode('.', array($this->getModuleName(), $this->getControllerName(), $this->getActionName()));
		if (!isset($_SESSION[$namespace])) {
			$_SESSION[$namespace] = array();
		}
		return $_SESSION[$namespace];
	}
	
	/**
	 * Aqui nos controlamos as sessoes de filtros.
	 * 
	 * @param mixed $name
	 * @param mixed $value
	 * @return array
	 */
	public function addSessionFilter($name, $value = null) {
		$namespace = implode('.', array($this->getModuleName(), $this->getControllerName(), $this->getActionName()));
		
		if (is_array($name)) {
			foreach ($name as $i => $v) {
				$_SESSION[$namespace][$i] = $v;
			}
		} else {
			$_SESSION[$namespace][$name] = $value;
		}
		return $_SESSION[$namespace];
	}
	
	/**
	 * Remove um item de sessao de filtro.
	 * 
	 * @param mixed $key
	 */
	public function removeSessionFilter($key = null) {
		$namespace = implode('.', array($this->getModuleName(), $this->getControllerName(), $this->getActionName()));
		
		if ($key && $_SESSION[$namespace][$key]) {
			unset($_SESSION[$namespace][$key]);
		}
	}
	
	/**
	 * Limpa todos os filtros deste namespace
	 */
	public function clearSessionFilter() {
		$namespace = implode('.', array($this->getModuleName(), $this->getControllerName(), $this->getActionName()));
		unset($_SESSION[$namespace]);
	}
}
