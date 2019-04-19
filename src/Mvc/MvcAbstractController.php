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

namespace SuitUp\Mvc;

use Exception;
use SuitUp\Exception\StructureException;
use Throwable;
use stdClass;
use ReflectionClass;
use SuitUp\Enum\MsgType;

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
   * @var Throwable
   */
  private $exception;

  /**
   * Store all system messages.
   *
   * @var array
   */
  private $messages = array();

  /**
   * This object will be populated only when view be rendered.
   * Keep it here is like to reserve this object name, avoiding
   * override.
   *
   * @var array
   */
  private $login = array();

  /**
   * This object will be populated only when view be rendered.
   * Keep it here is like to reserve this object name, avoiding
   * override.
   *
   * It is the base URL to build links.
   *
   * @var string
   */
  private $baseUrl = '';

  /**
   * This object will be populated only when view be rendered.
   * Keep it here is like to reserve this object name, avoiding
   * override.
   *
   * It is the base PATH to render resources (files).
   *
   * @var string
   */
  private $basePath = '';

  /**
   * MvcAbstractController constructor.
   *
   * @param FrontController $frontController
   * @throws \ReflectionException
   */
  public function __construct(FrontController $frontController) {

    // Add a respective item to each injected value in the view
    $reflectionClass = new ReflectionClass(get_class());

    // Loop under all class items
    foreach ($reflectionClass->getProperties() as $property) {
      if (!$property->isStatic() && !in_array($property->getName(), array('view', 'frontController'))) {
        $this->view[$property->getName()] = '';
      }
    }

    // Dependency injection
    $this->frontController = $frontController;

    // Messages from previous page
    if (isset($_SESSION[$this->getMsgNsp()])) {

      // Loop over all messages from previous page.
      foreach ($_SESSION[$this->getMsgNsp()] as $type => $messages) {
        foreach ($messages as $message) {
          $this->addMsg($message, $type);
        }
      }

      // Already stored so lets delete it
      unset($_SESSION[$this->getMsgNsp()]);
    }
  }

  /**
   *
   */
  public function preDispatch() { }

  /**
   * An action to run before current action
   */
  public function init() { }

  /**
   * Default action
   */
  public function indexAction() { }

  /**
   * Actions to be done after current action method
   */
  public function posDispatch() { }

  /**
   * This method is dispatched after everything and will only
   * render everything defined till here.
   *
   * <b>Please, avoid to override this method</b>
   *
   * @throws StructureException
   */
  public function render() {

    // If exists some exception
    $this->view['exception'] = $this->getException();

    // Show messages in the view
    $this->view['messages'] = $this->getMessages();

    // Add login variable to be rendered with view
    $this->view['login'] = self::getLogin();

    // Append the base URL to the views
    $this->view['baseUrl'] = $this->baseUrl();

    // Append the path to the root of site
    $this->view['basePath'] = $this->basePath();

    // Inject variables to be used inside view or layout
    foreach ((array) $this->view as $key => $var) {
      $$key = $var;
      $this->$key = $var;
    }
    unset($key); // Remove residue
    unset($var); // Remove residue

    // Get the view file name
    $viewFilename = $this->getFrontController()->resolveViewFilename();

    // Validate
    if (! file_exists($viewFilename)) {
      throw new StructureException("View file '$viewFilename' does not exists. If it is a json response use method jsonResponse()");
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
   * Default default-error type.
   *
   * <b>Avoid override it</b>
   */
  public function errorAction(): void {
    if (!IS_TESTCASE) {
      header(getenv('SERVER_PROTOCOL') . ' 500 Internal Server Error', true, 500);
    }
  }

  /**
   * Error type to page not found.
   *
   * <b>Avoid override it</b>
   */
  public function notFoundAction(): void {
    if (!IS_TESTCASE) {
      header(getenv('SERVER_PROTOCOL') . ' 404 Not Found', true, 404);
    }
  }

  /**
   * This class store all parameters to build application.
   *
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
   * System messages added by user
   *
   * @param string $msg
   * @param string $type
   * @param boolean $withRedirect
   * @return MvcAbstractController
   */
  public function addMsg($msg, $type = MsgType::INFO, $withRedirect = false) {
    if ($withRedirect) {
      $_SESSION[$this->getMsgNsp()][$type][] = $msg;
    } else {
      $this->messages[$type][] = $msg;
    }
    return $this;
  }

  /**
   * Return the current list of messages
   * @return array
   */
  public function getMessages(): array {
    return $this->messages ?? array();
  }

  /**
   * @return Throwable
   */
  public function getException(): ?Throwable {
    return $this->exception;
  }

  /**
   * @param Throwable $exception
   * @return MvcAbstractController
   */
  public function setException(Throwable $exception): MvcAbstractController {
    $this->exception = $exception;
    return $this;
  }

  /**
   * The name of the current module.
   *
   * @return string
   */
  public function getModuleName() {
    return $this->getFrontController()->getModuleName();
  }

  /**
   * The name of the current controller.
   *
   * @return string
   */
  public function getControllerName(): string {
    return $this->getFrontController()->getControllerName();
  }

  /**
   * Name of the current name.
   *
   * @return string
   */
  public function getActionName(): string {
    return $this->getFrontController()->getActionName();
  }

  /**
   * Name of the current layout.
   *
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
   * @throws Exception
   * @return MvcAbstractController
   */
  public function addViewVar($name, $value = null) {
    if (is_array($name)) {
      foreach ($name as $key => $val) {

        // Check if variable is already defined
        if (isset($this->view[$key])) {
          throw new Exception("The view item named '$key' is already defined and can't be override");
        }

        $this->view[$key] = $val;
      }
    } else {

      // Check if variable is already defined
      if (isset($this->view[$name])) {
        throw new Exception("The view item named '$name' is already defined and can't be override");
      }

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
  public function isViewVar($name) {
    return isset($this->view[$name]);
  }

  /**
   * Return the content to the given view var name if exists.
   *
   * @param string $name
   * @return mixed
   */
  public function getViewVar($name) {
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

    return preg_replace('/\/+/', '/', $baseUrl);
  }

  /**
   * Return base DIRECTORY to the current document root. It will
   * help to render files in the view.
   *
   * @param string|null $ref
   * @return string
   */
  public function basePath(string $ref = null): string {
    $basePath = '/'.ltrim($this->getFrontController()->getBasePath(), '/');

    // Append reference
    $append = ltrim(($ref ?? ''), '/');

    if ($append) {
      $basePath .= '/'.$append;
    }

    return preg_replace('/\/+/', '/', $basePath);
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
   *
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
   *
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
   * Return the current login session data.
   *
   * @param string $key
   * @param mixed $default
   * @return mixed
   */
  public static function getLogin(string $key = null, $default = null) {
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
   * Update some key of the current login session data.
   *
   * @param string $key
   * @param mixed $value
   */
  public static function updateLoginKey(string $key, $value): void {
    $login = (array) self::getLogin();

    // @TODO: check why isset does not work here
    foreach (array_keys($login) as $i) {
      if ($key == $i) {
        $_SESSION[self::$authNsp][$key] = $value;
      }
    }
  }

  /**
   * Return the previous page if it is different from current.
   *
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
   * Redirect page to...
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
   * @return string
   */
  public function getSessionFilterNamespace(): string {
    $namespace = implode('.', array(
      $this->getFrontController()->getModuleName(),
      $this->getFrontController()->getControllerName(),
      $this->getFrontController()->getActionName()
    ));

    return $namespace;
  }

  /**
   * Return or create a session specific to create filters.
   * <b>Reserved only one by each action.</b>
   *
   * @return mixed
   */
  public function getSessionFilter() {

    $namespace = $this->getSessionFilterNamespace();

    if (! isset($_SESSION[$namespace])) {
      $_SESSION[$namespace] = array();
    }
    return $_SESSION[$namespace];
  }

  /**
   * Add value by key to the reserved filter session.
   *
   * @param mixed $name
   * @param mixed $value
   * @return array
   */
  public function addSessionFilter($name, $value = null) {

    $namespace = $this->getSessionFilterNamespace();

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
   * Remove item from reserved filter session.
   *
   * @param string $name
   */
  public function removeSessionFilter(string $name) {

    $namespace = $this->getSessionFilterNamespace();

    if (isset($_SESSION[$namespace][$name])) {
      unset($_SESSION[$namespace][$name]);
    }
  }

  /**
   * Clear all reserved filter session.
   *
   * Note that it will clear ONLY the current action filter session.
   */
  public function clearSessionFilter(): void {

    $namespace = $this->getSessionFilterNamespace();
		unset($_SESSION[$namespace]);
	}
}
