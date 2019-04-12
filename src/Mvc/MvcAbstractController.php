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
declare(strict_types=1);

namespace Suitup\Mvc;

use Exception;
use stdClass;
use Suitup\Enum\MsgType;

/**
 * Class MvcAbstractController
 * @package SuitUp\Mvc
 */
abstract class MvcAbstractController
{
  const MSG_NSP = 'MSGRedir';

  /**
   * @var string
   */
  public static $authNsp = 'LogINAuth';

  /**
   * @var FrontController
   */
  private $frontController;

  /**
   * @var array
   */
  private $view = array();

  /**
   * @var array
   */
  private $msgs = array();

  /**
   * @var \Throwable
   */
  private $exception;

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
    UPLOAD_ERR_EXTENSION => 'UPLOAD_ERR_EXTENSION'
  );

  public function __construct(FrontController $frontController) {

    // Dependency injection
    $this->frontController = $frontController;

    // Messages from previous page
    if (isset($_SESSION[$this->getMsgNsp()])) {
      foreach ($_SESSION[$this->getMsgNsp()] as $index => $msgNsp) {
        foreach ($msgNsp as $type => $msgs) {
          foreach ($msgs as $msg) {
            $this->addMsg($msg, $type);
          }
        }

        // Already stored so lets delete it
        unset($_SESSION[$this->getMsgNsp()][$index]);
      }
    }
  }

  /**
   * Este metodo é chamado antes da ação do controlador.
   * Se for sobrescrever ele, não esqueça de chama-lo.
   *
   * ex.
   * <pre>
   * public function preDispatch() {
   *		// your code
   *
   *		parent::preDispatch();
   *
   *		// your code
   * }
   * </pre>
   */
  public function preDispatch() { }

  /**
   * Default accessible methods
   */

  /**
   *
   */
  public function init() { }

  /**
   *
   */
  public function indexAction() {}

  /**
   * Default error type
   */
  public function errorAction() {
    header(getenv('SERVER_PROTOCOL') . ' 500 Internal Server Error', true, 500);
  }

  /**
   * Error type to page not found
   */
  public function notFoundAction() {
    header(getenv('SERVER_PROTOCOL') . ' 404 Not Found', true, 404);
  }

  /**
   *
   * @throws Exception
   */
  public function posDispatch() {

    // If exists some exception
    $this->view['exception'] = $this->exception;

    // Show messages in the view
    $this->view['messages'] = $this->msgs;

    // Add login variable to be rendered with view
    $this->view['login'] = self::getLogin();

    // Inject variables to be used inside view or layout
    foreach ((array) $this->view as $key => $var) {
      $$key = $var;
    }
    unset($key); // Remove residue
    unset($var); // Remove residue

    // Get the view file name
    $viewFilename = $this->getFrontController()->resolveViewFilename();

    // Validate
    if (! file_exists($viewFilename)) {
      throw new Exception("View file '$viewFilename' does not exists. If it is a json response use method jsonResponse()");
    }

    // Collect pieces to discover layout filename
    $layoutFilename = $this->getFrontController()->resolveLayoutFilename();

    // Store view content
    ob_start();
    include $viewFilename;
    $content = ob_get_clean();

    // If there is no layout file we simple render view file directly
    if (! file_exists($layoutFilename)) {
      exit($content);
    }

    // Render layout content
    include $layoutFilename;
  }

  /**
   * @return FrontController
   */
  public function getFrontController(): FrontController {
    return $this->frontController;
  }

  /**
   * Namespace is unique per module, so it will not mix messages from different modules.
   *
   * @return string
   */
  public function getMsgNsp() {
    return $this->getFrontController()->getModuleName() . '_' . self::MSG_NSP;
  }

  /**
   * @return \Throwable
   */
  public function getException(): \Throwable {
    return $this->exception;
  }

  /**
   * @param \Throwable $exception
   * @return MvcAbstractController
   */
  public function setException(\Throwable $exception): MvcAbstractController {
    $this->exception = $exception;
    return $this;
  }

  /**
   * @return string
   */
  public function getModuleName() {
    return $this->getFrontController()->getModuleName();
  }

  /**
   * @return string
   */
  public function getControllerName(): string {
    return $this->getFrontController()->getControllerName();
  }

  /**
   * @return string
   */
  public function getActionName(): string {
    return $this->getFrontController()->getActionName();
  }

  /**
   * Nome do arquivo de layout.
   * @return string
   */
  public function getLayoutName(): string {
    return $this->getFrontController()->getLayoutName();
  }

  /**
   * Change layout
   *
   * @param string $name
   * @param string $path
   * @return MvcAbstractController
   */
  public function setLayout(string $name, string $path = null): MvcAbstractController {
    $this->getFrontController()->setLayoutName($name);
    if ($path) {
      $this->getFrontController()->setLayoutPath($path);
    }
    return $this;
  }

  /**
   * Render whatever view file. Done in the functions.php file to be used
   * everywhere.
   *
   * @param string $renderViewName Filename to be rendered
   * @param array $vars Variables accessible on the view
   * @param string $renderViewPath Path to the view file
   * @return string
   */
  public function renderView($renderViewName, $vars = array(), $renderViewPath = null): string {
    if (! $renderViewPath) {
      $renderViewPath = $this->getFrontController()->getViewsPath();
    }
    return renderView($renderViewName, $vars, $renderViewPath);
  }

  /**
   * Add a variable to be used inside the view
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
   * Check if a view var already exists.
   *
   * @param string $name
   * @return bool
   */
  protected function isViewVar($name) {
    return isset($this->view[$name]);
  }

  /**
   * Return the content to the given view var name if exists.
   *
   * @param string $name
   * @return mixed
   */
  protected function getViewVar($name) {
    return $this->isViewVar($name) ? $this->view[$name] : false;
  }

  /**
   * Resolve base url to links consistent.
   *
   * @param string|null $ref
   * @return string
   */
  public function baseUrl(string $ref = null): string {
    $baseUrl = '/'.ltrim($this->getFrontController()->getBasePath(), '/');

    // If is not from default module
    if ($this->getFrontController()->getModule() != 'default') {
      $baseUrl .= '/'.$this->getFrontController()->getModule();
    }

    // Append reference
    $append = ltrim(($ref ?? ''), '/');
    $baseUrl .= $append ? '/'.$append : '';

    return $baseUrl;
  }

  /**
   * Alias for @see baseUrl()
   *
   * @param string|null $ref
   * @return string
   */
  public function basePath(string $ref = null): string {
    return $this->baseUrl($ref);
  }

  /**
   * Return all route params.
   *
   * @return array
   */
  public function getParams() {
    return $this->getFrontController()->getParams();
  }

  /**
   * Return a given param or it's pre defined default value;
   *
   * @param string $name
   * @param mixed $default
   * @return mixed
   */
  public function getParam($name, $default = null) {
    $params = $this->getParams();
    return isset($params[$name]) ? $params[$name] : $default;
  }

  /**
   * True if the request method is POST.
   * @return bool
   */
  public function isPost() {
    return (bool) (getenv('REQUEST_METHOD') === "POST");
  }

  /**
   * Return a given index from $_POST.
   *
   * @param string $name
   * @param mixed $default
   * @return array
   */
  public function getPost($name = null, $default = null) {
    $post = (array) filter_input_array(INPUT_POST);

    if ($name) {
      return isset($post[$name]) ? $post[$name] : $default;
    }
    return $post;
  }

  /**
   * True if user is logged on with current self::$authNsp.
   * @return bool
   */
  public static function isLogged() {
    return (bool) isset($_SESSION[self::$authNsp]);
  }

  /**
   * Set session with data. Can use a non default namespace
   *
   * @param array $data
   * @param string $namespace
   */
  public static function setLogin(array $data, $namespace = null) {
    if (!$namespace) {
      $namespace = self::$authNsp;
    }
    $_SESSION[$namespace] = $data;
  }

  /**
   * Clear the login session. Can use a non default namespace
   *
   * @param string $namespace
   */
  public static function clearLogin($namespace = null) {
    if (!$namespace) {
      $namespace = self::$authNsp;
    }
    if (isset($_SESSION[$namespace])) {
			$_SESSION[$namespace] = null;
		}
  }

  /**
   * Retorna tudo que está gravado na sessão de login.
   *
   * @param string $key
   * @param mixed $default
   * @return mixed
   */
  public static function getLogin($key = null, $default = null) {
    $login = isset($_SESSION[self::$authNsp]) ? $_SESSION[self::$authNsp] : false;

    if ($key) {
      if (! isset($login[$key]) || ! $login[$key]) {
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
   * @param string $key
   * @param mixed $value
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
   * @param string $msg
   * @param string $type
   * @param boolean $withRedirect
   * @return MvcAbstractController
   */
  public function addMsg($msg, $type = MsgType::INFO, $withRedirect = false) {
    if ($withRedirect) {
      // @todo: Check it out
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
  public function uploadFile($file, $where, $exitFilename = null, array $allowedExt = array('jpeg', 'jpg', 'pdf', 'png', 'gif', 'svg')) {
    $result = new stdClass();
    $result->filename = "";
    $result->pathAndFilename = "";
    $result->fileExt = "";

    $upload = new Upload($file);
    if ($upload->uploaded) {
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
        $upload->process($where);

        // Upload deu tudo certo?
        if ($upload->processed) {

          // Define resultado
          $result->fileExt = $upload->file_src_name_ext;
          $result->filename = $upload->file_dst_name;
          $result->pathAndFilename = $where . $upload->file_dst_name;

          // Permissao total no arquivo
          chmod($result->pathAndFilename, 0777);

          // Se for para mudar o nome do arquivo de saida
          if ($exitFilename) {

            // Renomeia
            if (! rename($result->pathAndFilename, $where . $exitFilename . '.' . $upload->file_src_name_ext)) {
              throw new Exception("Não foi possível mover o arquivo '{$result->pathAndFilename}'");
            }

            $result->filename = $exitFilename . '.' . $upload->file_src_name_ext;
            $result->pathAndFilename = $where . $exitFilename . '.' . $upload->file_src_name_ext;
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
   * @param array $file Item $_FILES['arquivo']
   * @param int $maxFilesize 512kb por padrão.
   * @throws \Exception
   * @return string
   */
  public function uploadFileImageBase64($file, $maxFilesize = 524288) {
    // Valida erros
    if ($file['error'] != UPLOAD_ERR_OK) {
      throw new Exception("Erro ao fazer o upload da imagem, tente novamente");
    }

    // Valida tamanho
    if ($file['size'] > $maxFilesize) {
      throw new Exception("Arquivo muito grande, por favor envie um arquivo até " . ($maxFilesize / MB) . "Mb");
    }

    // Define exts e mimetypes
    $mimeTypes = array(
      'jpg' => 'image/jpeg',
      'jpeg' => 'image/jpeg',
      'jpe' => 'image/jpeg',
      'gif' => 'image/gif',
      'png' => 'image/png',
      'bmp' => 'image/bmp'
    );

    // Valida EXT
    $fileExt = preg_replace("/^.+\./", '', $file['name']);
    if (! array_key_exists($fileExt, $mimeTypes)) {
      throw new Exception("Extensão do arquivo é inválida, por favor envie arquivos desta lista (" . implode(", ", array_keys($mimeTypes)) . ")");
    }

    // Valida MimeType
    if (! isset($mimeTypes[$fileExt]) || ($file['type'] != $mimeTypes[$fileExt])) {
      throw new Exception("Arquivo inválido, por favor envie arquivos desta lista (" . implode(", ", array_keys($mimeTypes)) . ")");
    }

    // Valida se o arquivo existe.
    if (! file_exists($file['tmp_name']) || ! is_readable($file['tmp_name'])) {
      throw new Exception("Erro inesperado ao fazer upload do arquivo, tente novamente");
    }
    // Não deu erro até aqui, então codifica o cara para base64
    return 'data:' . $mimeTypes[$fileExt] . ';base64,' . base64_encode(file_get_contents($file['tmp_name']));
  }

  /**
   * Pega a pagina anterior se houver e for diferente da atual.
   * @return bool|string
   */
  public function getReferer() {

    // Data from server
    $referer = getenv('HTTP_REFERER');
    $page = getenv('REQUEST_SCHEME') . '://' . getenv('HTTP_HOST') . getenv('REQUEST_URI');

    // if referer is different from current page
    if ($referer != $page) {
      return $referer;
    }
    return false;
  }

  /**
   * Efetua redirecionamento
   *
   * @param string $to
   * @return string
   */
  public function redirect($to) {
    header("Location: $to");
    return exit();
  }

  /**
   * Alias to @see jsonResponse
   *
   * @param array $data
   */
  public function ajax(array $data): void {
    $this->jsonResponse($data);
  }

  /**
   * @param array $data
   */
  public function jsonResponse(array $data): void {
    header("Content-Type: application/json; Charset=UTF-8");
    exit(json_encode($data));
  }

  /**
   * Retorna o que tem gravado na sessao de filtros.
   *
   * @return mixed
   */
  public function getSessionFilter() {
    $namespace = implode('.', array(
      $this->getFrontController()->getModuleName(),
      $this->getFrontController()->getControllerName(),
      $this->getFrontController()->getActionName()
    ));
    if (! isset($_SESSION[$namespace])) {
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
    $namespace = implode('.', array(
      $this->getFrontController()->getModuleName(),
      $this->getFrontController()->getControllerName(),
      $this->getFrontController()->getActionName()
    ));

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
    $namespace = implode('.', array(
      $this->getFrontController()->getModuleName(),
      $this->getFrontController()->getControllerName(),
      $this->getFrontController()->getActionName()
    ));

    if ($key && $_SESSION[$namespace][$key]) {
      unset($_SESSION[$namespace][$key]);
    }
  }

  /**
   * Limpa todos os filtros deste namespace
   */
  public function clearSessionFilter() {
    $namespace = implode('.', array(
      $this->getFrontController()->getModuleName(),
      $this->getFrontController()->getControllerName(),
      $this->getFrontController()->getActionName()
    ));

		unset($_SESSION[$namespace]);
	}
}
