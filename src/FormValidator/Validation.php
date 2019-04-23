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

namespace SuitUp\FormValidator;

use SuitUp\Exception\FormValidatorException;

/**
 * Class Validation
 * @package SuitUp\FormValidator
 */
abstract class Validation {

  /**
   * @var array Parameters to be validated
   */
  protected $data = array();

  /**
   * @var array Error messages
   */
  public $messages = array();

  /**
   * @var array POST data
   */
  public $post = array();

  /**
   * @var null|bool
   */
  private $valid = null;

  /**
   * Validation constructor.
   *
   * @param int $method It could be INPUT_GET
   */
  public function __construct($method = INPUT_POST) {
    $this->post = (array) filter_input_array($method);
  }

  /**
   * Check if all data validation defined is true.
   *
   * @return bool|null
   * @throws FormValidatorException
   */
  public function isValid() {
    if ($this->valid === null) {
      $this->validateForm();
    }
    return $this->valid;
  }

  /**
   * Call this method to get all validated data filtered by functions defined in the Form class.
   * If called before isValid method so the validation will be done.
   *
   * @return array
   * @throws FormValidatorException
   */
  public function getData() {

    // If form is not validated yet, do it.
    if ($this->valid === null) {
      $this->validateForm();
    }

    // Get validated data to return
    $data = array();
    foreach ($this->data as $key => $item) {
      $data[$key] = isset($item['value']) ? $item['value'] : '';
    }
    return $data;
  }

  /**
   * Append some value to the result data.
   *
   * @param string $index
   * @param mixed $data
   */
  public function addData($index, $data) {
    $this->data[$index]['value'] = $data;
  }

  /**
   * Return the list of error messages.
   *
   * @return array
   */
  public function getMessages() {
    return $this->messages;
  }

  /**
   * Make the validation
   *
   * @return bool|null
   * @throws FormValidatorException
   */
  private function validateForm() {
    $result = true;
    foreach ($this->data as $field => $item) {

      // Fields that is not on the dataset.
      if (!isset($this->post[$field])) {
        $this->post[$field] = '';
      }

      // Loop under rules
      foreach ($item['validation'] as $methodOrClass => $method) {

        // Zend validations
        if (is_string($methodOrClass) && class_exists($methodOrClass)) {

          // If field is empty, next
          if (!$this->post[$field]) {
            continue;
          }

          // Instead of value it is the Zend Validation options
          $options = $method;

          // Make the Zend Validation
          $validator = new $methodOrClass($options);

          if (!$validator->isValid($this->post[$field])) {
            $result = false;
            foreach ($validator->getMessages() as $msg) {
              $this->messages[$field][] = $msg;
            }
          }
          continue;
        }

        // It is a validation with options
        if (is_string($methodOrClass) && method_exists($this, $methodOrClass)) {

          $options = $method;
          $validation = $this->$methodOrClass($this->post[$field], $options);
          if ($validation->error) {
            $result = false;

            // Create index if not exists
            if (!isset($this->messages[$field])) {
              $this->messages[$field] = array();
            }

            // We may return an array with more than one message
            if (is_array($validation->message)) {
              $this->messages[$field] += $validation->message;
            } else {
              $this->messages[$field][] = $validation->message;
            }
          }
          continue;
        }

        // Normal validation option
        if (method_exists($this, $method)) {
          $validation = $this->$method($this->post[$field]);
          if ($validation->error) {
            $result = false;

            // Create index if not exists
            if (!isset($this->messages[$field])) {
              $this->messages[$field] = array();
            }

            // We may return an array with more than one message
            if (is_array($validation->message)) {
              $this->messages[$field] += $validation->message;
            } else {
              $this->messages[$field][] = $validation->message;
            }
          }
          continue;
        }

        // @TODO: Create a closure type function validator

        // We don't know where this validation method can be
        throw new FormValidatorException("The method '$methodOrClass' ou '$method' does not exists to validate the field");
      }

      // Filters
      $this->data[$field]['value'] = $this->post[$field];
      if (isset($item['filter'])) {
        foreach ($item['filter'] as $withOptions => $method) {

          // It is a filter with options
          if (is_string($withOptions) && method_exists($this, $withOptions)) {

            $filterOptions = $method;
            $this->data[$field]['value'] = $this->$withOptions($this->data[$field]['value'], $filterOptions);

          } else if (is_string($method) && method_exists($this, $method)) {

            // A method
            $this->data[$field]['value'] = $this->$method($this->data[$field]['value']);

          } else if (is_string($method) && isClosure($method)) {

            // A closure function
            $this->data[$field]['value'] = $method($this->data[$field]['value']);

          } else if (function_exists($method)) {

            // A function
            $this->data[$field]['value'] = $method($this->data[$field]['value']);

          } else {
            throw new Exception("The method '$method' does not exists to filter the field");
          }
        }
      }
    }

    // Data without validation neither filter
    foreach ($this->post as $key => $value) {
      if (!isset($this->data[$key])) {
        $this->data[$key]['value'] = $value;
      }
    }

    // The object
    $this->valid = (bool) $result;
    return $this->valid;
  }
}
