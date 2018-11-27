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
 * This class assign all needs to return results
 * to the form validations. It's like a hack.
 *
 * @package SuitUp\FormValidator
 */
class FormResult extends \stdClass
{

  /**
   * There's an error?
   * @var bool
   */
  public $error = false;

  /**
   * The message
   * @var string|array
   */
  public $message = '';

  /**
   * Set error true or false
   *
   * @param bool $error
   * @return $this
   */
  public function setError($error) {
    $this->error = (bool) $error;
    return $this;
  }

  /**
   * Return error status
   * @return bool
   */
  public function getError() {
    return $this->error;
  }

  /**
   * Set the message about the error
   *
   * @param string $message
   * @return $this
   * @throws Exception
   */
  public function setMessage($message) {

    // Validation
    if (!is_string($message) && !is_array($message)) {
      throw new Exception('Message here must be string or array');
    }

    $this->message = $message;
    return $this;
  }

  /**
   * Return the message error
   *
   * @return type
   */
  public function getMessage() {
    return $this->message;
  }
}
