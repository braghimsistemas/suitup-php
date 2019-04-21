<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2019 Braghim Sistemas
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

define('KB', 1024);          // Em bytes
define('MB', 1048576);       // Em bytes
define('GB', 1073741824);    // Em bytes
define('TB', 1099511627776); // Em bytes

if (! function_exists('dump')) {

  /**
   * Simple debug function, just like Zend\Debug.
   *
   * @param mixed $var What you want to debug.
   * @param bool $echo If false, this function will return the result.
   * @return string
   */
  function dump($var, $echo = true) {
    ob_start();
    var_dump($var);

    /**
     * $argv when you run this function by command line.
     */
    if (isset($argv)) {
      $output = preg_replace("/\]\=\>(\r|\n|\r\n)(\s+)/m", "] => ", ob_get_clean()) . "\r\n\r\n";
    } else {
      $output = "<pre>" . preg_replace("/\]\=\>(\r|\n|\r\n)(\s+)/m", "] => ", ob_get_clean()) . "</pre>";
    }
    if ($echo) {
      exit($output);
    }
    return $output;
  }
}

if (! function_exists('mctime')) {

  /**
   * Return microtime as float.
   *
   * @return float
   */
  function mctime(): float {
    list ($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
  }
}

if (! function_exists('isClosure')) {

  /**
   * Return true when a given item is a closure function
   * 
   * @return bool
   */
  function isClosure($item): bool {
    return (is_object($item) && ($item instanceof \Closure));
  }
}

if (! function_exists('toCamelCase')) {

  /**
   * @param string $var
   * @param bool $upperFirst
   * @return string
   */
  function toCamelCase(string $var, bool $upperFirst = false): string {
    $var = strtolower($var);
    $var = preg_replace("/[^a-zA-Z0-9]+/", " ", $var);
    $var = ucwords($var);
    $var = preg_replace("/\s+/", "", $var);

    if (!$upperFirst) {
      $var = lcfirst($var);
    }

    return $var;
  }
}


if (! function_exists('toDashCase')) {

  /**
   * @param string $var
   * @return string
   */
  function toDashCase(string $var): string {
    $var = strtolower($var);
    $var = preg_replace("/[^a-zA-Z0-9]+/", "-", $var);
    return $var;
  }
}

/**
 * Render a (p)html view with injected variables
 *
 * @param string $renderViewName Filename to be rendered with .phtml extension
 * @param array|mixed $vars Variables to be used inside view file
 * @param string $renderViewPath Path to the file, it's possible to set full file
 *                               path direct in the first param here
 * @return string
 */
function renderView($renderViewName, $vars = array(), $renderViewPath = null): string {

  // Inject variables to the view
  foreach ($vars as $n => $v) {
    $$n = $v;
  }
  unset($n);
  unset($v);

  ob_start();
  if ($renderViewPath) {
    include $renderViewPath . DIRECTORY_SEPARATOR . $renderViewName;
  } else {
    include $renderViewName;
  }
  return ob_get_clean();
}

/**
 * Render a pagination template.
 *
 * @param SuitUp\Paginate\Paginate $object Objeto de paginacao criado na query.
 * @param string $renderViewName Nome do arquivo .phtml de paginacao
 * @return string Html pronto dos botoes de paginacao
 */
function paginateControl(\SuitUp\Paginate\Paginate $object, $renderViewName = 'paginacao.phtml') {

  // Return
  $items = array();

  $currentPage = ($object->getCurrentPage() > 0) ? $object->getCurrentPage() : 1;
  $totalPages = $object->getTotalPages();
  $pageRange = ($object->getPageRange() === 'total') ? $totalPages : $object->getPageRange();

  // Page range odd
  if ($pageRange % 2 == 0) {
    $pageRange --;
  }

  if ($currentPage <= $totalPages) {
    $bothSides = ($pageRange - 1) / 2;

    // Mount beginning
    for ($i = 0; $i < $bothSides; $i ++) {
      $page = $currentPage - ($bothSides - $i);

      if ($page <= $totalPages) {
        if ($page >= 1)
          $items[] = $page;
      }
    }

    // Half
    if ($currentPage <= $totalPages) {
      if (! in_array($currentPage, $items)) {
        $items[] = (int) $currentPage;
      }
    }

    $itemsCount = count($items);
    $last = end($items);
    $need = $pageRange - $itemsCount;

    // End
    for ($i = 0; $i < $need; $i ++) {
      if (($last + $i + 1) <= $totalPages) {
        $items[] = ($last + $i + 1);
      }
    }

    // If missed any in the beginning.
    if (count($items) < $pageRange) {
      $need = $pageRange - count($items);

      for ($i = 0; $i < $need; $i ++) {
        if ($items[0] - 1 <= $totalPages && ($items[0] - 1) > 0) {
          array_unshift($items, $items[0] - 1);
        }
      }
    }
  } else {
    for ($i = 0; $i < $totalPages; $i ++) {
      $items[] = $totalPages - $i;
    }

    foreach ($items as $kI => $fI) {
      if ($kI > $pageRange - 1) {
        unset($items[$kI]);
      }
    }
    $items = array_reverse($items);
  }

  if (count($items) < 2) {
    $items = array();
  }

  // Define a url base.
  $url = '/' . preg_replace("/\?(" . preg_quote(getenv('QUERY_STRING'), "/") . ")/", "", trim(getenv('REQUEST_URI'), '/')) . "?";
  foreach ((array) filter_input_array(INPUT_GET) as $i => $value) {
    if ($i != 'pagina') {
      $url .= $i . '=' . $value . '&';
    }
  }
  $url = trim(trim($url, '?'), '&');

  // Envia para view que monta o html da paginacao
  return renderView($renderViewName, array(
    'items' => $items,
    'totalPages' => $totalPages,
    'currentPage' => $currentPage,
    'nextPage' => in_array(($currentPage + 1), $items) ? $currentPage + 1 : false,
    'previousPage' => in_array(($currentPage - 1), $items) ? $currentPage - 1 : false,
    'baseUrl' => $url . (preg_match("/\?/", $url) ? '&' : '?')
  ));
}

/**
 * Translate a given Exception trace to string.
 * !!! CAUTION !!! recursive function....
 *
 * @param mixed $args
 * @param bool $root
 * @return string
 */
function getTraceArgsAsString($args, $root = true) {
  $argString = "";

  switch (gettype($args)) {
    case 'string':
      $argString .= '"' . $args . '"';
      break;
    case 'integer':
    case 'float':
    case 'double':
      $argString .= '(' . gettype($args) . ') ' . $args;
      break;
    case 'boolean':
      $argString .= ($args ? 'true' : 'false');
      break;
    case 'array':
      if ($root) {
        foreach ($args as $key => $arg) {
          $argString .= getTraceArgsAsString($arg, false) . ", ";
        }
        $argString = preg_replace("/,(\s)?$/", "", $argString);
      } else {
        foreach ($args as $key => $arg) {
          $argString .= '"' . $key . '" => ' . getTraceArgsAsString($arg, false) . ", ";
        }
        $argString = "array(" . preg_replace("/,(\s)?$/", "", $argString) . ")";
      }
      break;
    case 'NULL':
      $argString .= "NULL";
      break;
    case 'object':
      $argString .= ($args == null) ? "NULL" : get_class($args);
      break;
    default:
      // O proprio type
      $argString .= gettype($args);
  }
  return $argString;
}

if (! function_exists('uploadFileImageBase64')) {

  /**
   * Transform an image data to Base64.
   *
   * <b>It will make the file round to 33% bigger according to PHP Documentation</b>
   *
   * @param array $file Item $_FILES['filename']
   * @param int $maxFilesize Default to 512kb
   * @throws Exception
   * @return string
   */
  function uploadFileImageBase64(array $file, int $maxFilesize = 524288): string {
    // Check errors
    if ($file['error'] != UPLOAD_ERR_OK) {
      throw new Exception("Unexpected default-error, file was not sent, try again");
    }

    // Check size
    if ($file['size'] > $maxFilesize) {
      throw new Exception("Too big file, send one with till " . ($maxFilesize / MB) . "Mb");
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

    // Validate EXT
    $fileExt = preg_replace("/^.+\./", '', $file['name']);
    if (! array_key_exists($fileExt, $mimeTypes)) {
      throw new Exception("Invalid extension, please send one of these: (" . implode(", ", array_keys($mimeTypes)) . ")");
    }

    // Validate MimeType
    if (! isset($mimeTypes[$fileExt]) || ($file['type'] != $mimeTypes[$fileExt])) {
      throw new Exception("Invalid file mime type, please send one of these: (" . implode(", ", array_keys($mimeTypes)) . ")");
    }

    // Validate if file exists
    if (! file_exists($file['tmp_name']) || ! is_readable($file['tmp_name'])) {
      throw new Exception("Something went wrong to upload image, try again");
    }
    // No errors, try to code to base64
    return 'data:' . $mimeTypes[$fileExt] . ';base64,' . base64_encode(file_get_contents($file['tmp_name']));
  }
}

if (!function_exists('formSelect')) {

  /**
   * With this method you can append a <select></select> input tag type
   * as easy peasy and just one code line.
   *
   * @param string $name ID and NAME tag attr's
   * @param array $attrs All other attr's you would like to append to the tag, you probably should like to add class="something" here.
   * @param array $values The options values list with value => text
   * @param string $selected Option selected item
   * @return string
   */
  function formSelect(string $name, array $attrs = array(), array $values = array('' => 'Select One!'), string $selected = null) {

    // Normalize id attr
    $id = preg_replace("/[^0-9a-zA-Z-_]/", '-', $name);
    $id = preg_replace("/\-+/", '-', $id);
    $id = trim($id, '-');

    $html = "<select id=\"$id\" name=\"$name\"";

    foreach ($attrs as $attrName => $attrValue) {
      $html .= " ".$attrName.'="'.$attrValue.'"';
    }
    $html .= ">\r\n";

    foreach($values as $value => $text) {

      /**
       * To assert 'selected' $value must to be identical to $selected
       */
      if ((string) $selected === (string) $value) {
        $html .= "  ".'<option value="'.$value.'" selected="selected">'.$text.'</option>'."\r\n";
      } else {
        $html .= "  ".'<option value="'.$value.'">'.$text.'</option>'."\r\n";
      }
    }
    return $html."</select>\r\n";
  }

  /**
   * Create the input checkbox html string
   *
   * @param string $name The name and id attributes
   * @param array $attrs The list of attributes
   * @param mixed $value Value attribute
   * @param bool $checked Is checked?
   *
   * @return string
   */
  function formCheckbox($name, array $attrs = array(), $value = '1', $checked = false) {

    // Normalize id attr
    $id = preg_replace("/[^0-9a-zA-Z-_]/", '-', $name);
    $id = preg_replace("/\-+/", '-', $id);
    $id = trim($id, '-');

    // Base html
    $html = "<input type=\"checkbox\" id=\"$id\" name=\"$name\"";

    // Value and checked
    $attrs['value'] = $value;
    if ($checked) {
      $attrs['checked'] = 'checked';
    }

    // Attributes
    foreach ($attrs as $attrName => $attrValue) {
      $html .= " ".$attrName.'="'.$attrValue.'"';
    }
    $html .= ">\r\n";
    return $html;
  }
}
