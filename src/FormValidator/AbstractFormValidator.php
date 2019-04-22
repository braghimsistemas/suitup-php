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

use DateTime;
use Exception;
use SuitUp\Exception\FormValidatorException;
use SuitUp\Exception\StructureException;

/**
 * Class AbstractFormValidator
 *
 * @package SuitUp\FormValidator
 */
abstract class AbstractFormValidator extends Validation
{

  /**
   * Check if the $_POST form field is empty.
   *
   * @param mixed $value Form field value to be compared.
   * @param mixed $options Custom message to error
   * @return FormResult
   * @throws FormValidatorException
   */
  public function notEmpty($value, $options = null): FormResult {

    $result = new FormResult();

    // Default message
    $message = 'This field is required';

    // If have message from $options
    if (isset($options['message'])) {
      $message = $options['message'];
    } else if (is_string($options) && ! empty($options)) {
      $message = $options;
    }

    if (! $value) {
      $result->setError($message);
    }
    return $result;
  }

  /**
   * E-mail validation. <b>do not check if the form field is empty</b>
   *
   * @param string $value Form field value to be compared.
   * @param mixed $options Custom message to error
   * @return FormResult
   * @throws FormValidatorException
   */
  public function isEmail($value, $options = null): FormResult {

    $result = new FormResult();

    // Default message
    $message = "Inform a valid e-mail address";

    // If have message from $options
    if (isset($options['message'])) {
      $message = $options['message'];
    } else if (is_string($options) && ! empty($options)) {
      $message = $options;
    }

    if ($value && ! preg_match("/^[a-zA-Z0-9][a-zA-Z0-9\._-]+@([a-zA-Z0-9\._-]+\.)[a-zA-Z-0-9]{2}/", $value)) {
      $result->setError($message);
    }
    return $result;
  }

  /**
   * CEP number validation. CEP is the Zip-Code for shipping system from Brazil.
   *
   * @param string $value Form field value to be compared. CEP number formated like 99999-999 or 999999999
   * @param mixed $options Custom message to error
   * @return FormResult
   * @throws FormValidatorException
   */
  public function isCep($value, $options = null): FormResult {

    $result = new FormResult();

    // Default message
    $message = "Inform a valid CEP number";

    // If have message from $options
    if (isset($options['message'])) {
      $message = $options['message'];
    } else if (is_string($options) && ! empty($options)) {
      $message = $options;
    }

    // Ignored if the field is empty
    if ($value && ! preg_match("/^\d{5}(-?)\d{3}$/", $value)) {
      $result->setError($message);
    }
    return $result;
  }

  /**
   * Verify if this field has the mininum length size indicated by the option.
   *
   * @param mixed $value Form field value to be compared.
   * @param int $options [size, message]
   *		size: The minimun length size to the field;
   *		message: The custom message to be dispatched.
   * @return FormResult
   * @throws FormValidatorException
   */
  public function minLen($value, $options = null): FormResult {

    $result = new FormResult();

    // Length size
    $size = (isset($options['size'])) ? $options['size'] : $options;

    // size?
    if ($value && null == $size) {
      throw new FormValidatorException("Required 'size' option with the number of min length accepted.");
    }

    // Ignored if empty
    if ($value && (strlen($value) < (int) $size)) {
      $result->setError(isset($options['message']) ? $options['message'] : "The minimum length for this field is $size characters");
    }
    return $result;
  }

  /**
   * Verify if this field has the maximum length size indicated by the option.
   *
   * @param mixed $value Form field value to be compared.
   * @param int $options [size, message]
   *		size: The minimum length size to the field;
   *		message: The custom message to be dispatched.
   * @return FormResult
   * @throws FormValidatorException
   */
  public function maxLen($value, $options = null): FormResult {

    $result = new FormResult();

    // Length size
    $size = (int) (isset($options['size'])) ? $options['size'] : $options;

    // size?
    if ($value && null == $size) {
      throw new FormValidatorException("Required 'size' with the number of max length accepted.");
    }

    // Ignored if empty
    if ($value && (strlen($value) > $size)) {
      $result->setError(isset($options['message']) ? $options['message'] : "The maximum length for this field is $size characters");
    }
    return $result;
  }

  /**
   * The field which contains this validation have to be
   * greater than the field indicated in the target option.
   * This validation is numeric made, not about the length.
   *
   * @param mixed $value Form field value to be compared.
   * @param mixed $options [target, message]
   * 		Target: Another $_POST form to compare to;
   * 		Message: A custom message to be dispatch in error case.
   * @return FormResult
   * @throws FormValidatorException
   */
  public function greaterThan($value, $options = null): FormResult {

    $result = new FormResult();

    if (isset($options['target'])) {
      $target = $this->post[$options['target']];
    } else if (isset($this->post[$options])) {
      $target = $this->post[$options];
    } else {
      $target = $options;
    }

    // Target?
    if ($value && null == $target) {
      throw new FormValidatorException("Required 'target' with the name of the other field to compare.");
    }

    // Ignored if empty
    if ($value && ($this->toDouble($value) < $this->toDouble($target))) {
      $result->setError(isset($options['message']) ? $options['message'] : "This field must to be greater than '$target'");
    }
    return $result;
  }

  /**
   * The field which contains this validation have to be
   * less than the field indicated in the target option.
   * This validation is numeric made, not about the length.
   *
   * @param mixed $value Value from $_POST form to validate
   * @param mixed $options [target, message]
   * 		Target: Another $_POST form to compare to;
   * 		Message: A custom message to be dispatch in error case.
   * @return FormResult
   * @throws FormValidatorException
   */
  public function lessThan($value, $options = null): FormResult {

    $result = new FormResult();

    if (isset($options['target'])) {
      $target = $this->post[$options['target']];
    } else if (isset($this->post[$options])) {
      $target = $this->post[$options];
    } else {
      $target = $options;
    }

    // Target?
    if ($value && null == $target) {
      throw new FormValidatorException("Required 'target' with the name of the other field to compare.");
    }

    // Ignored if empty
    if ($value && ($this->toDouble($value) > $this->toDouble($target))) {
      $result->setError(isset($options['message']) ? $options['message'] : "This field must to be less than '$target'");
    }
    return $result;
  }

  /**
   * Compare two $_POST form fields that must to be identical.
   *
   * @param mixed $value Value from $_POST form
   * @param mixed $options [target, message]
   * 		Target: Another $_POST form to compare to;
   * 		Message: A custom message to be dispatch in error case.
   * @return FormResult
   * @throws FormValidatorException
   */
  public function identicalTo($value, $options = null): FormResult {

    $result = new FormResult();

    if (isset($options['target'])) {
      $target = $this->post[$options['target']];
    } else if (isset($this->post[$options])) {
      $target = $this->post[$options];
    } else {
      $target = $options;
    }

    // Target?
    if ($value && null == $target) {
      throw new FormValidatorException("Required 'target' with the name of the other field to compare.");
    }

    // Ignora vazio
    if ($value && ($value != $target)) {
      $result->setError(isset($options['message']) ? $options['message'] : "Fields must to be identical");
    }
    return $result;
  }

  /**
   * Check if the field $_POST value exists in the given array by options.
   *
   * @param mixed $value Form field value to be compared.
   * @param array $options You have the option of use default message and give just the array
   * 		list to search for the field $_POST value, but can use $options to give a custom message:
   * 		message: Custom message in error case;
   *		values: Array list to search for the field $_POST value;
   * @return FormResult
   * @throws FormValidatorException
   */
  public function inArray($value, array $options = array()): FormResult {

    $result = new FormResult();

    // Default message
    $message = "This value is not in the data set";

    // Custom message
    if (isset($options['message'])) {

      $message = $options['message'];

      // Have the array values to search?
      if (! isset($options['values'])) {
        throw new FormValidatorException('If $options["message"] is set, please set $options["values"] as the array values to search for.');
      }
      $compare = $options['values'];
    } else {
      $compare = $options;
    }

    if ($value && ! in_array($value, $compare)) {
      $result->setError($message);
    }
    return $result;
  }

  /**
   * Validate given date format
   *
   * @param string $value The value to be validated as date
   * @param string|array $options The message to be append to the error
   * @return FormResult
   * @throws FormValidatorException
   */
  public function isDate($value, $options = array()): FormResult {

    $result = new FormResult();

    if ($value) {
      $message = 'Invalid date';
      if ($options) {
        $message = (string) $options;
        if (isset($options['message'])) {
          $message = (string) $options['message'];
        }
      }

      try {
        // Compare date
        $dateTime = new DateTime($value);
        $errors = $dateTime->getLastErrors();

        if ($errors['error_count']) {
          $result->setError($message);
        }
      } catch (Exception $e) {
        $result->setError($message);
      }
    }
    return $result;
  }

  // ===============================================================
  //                         FILTERS
  // ===============================================================

  /**
   * Remove white spaces from begin and the end of the form field and
   * protect against tags injection.
   *
   * @param mixed $value Form field value to be filtered.
   * @return string
   */
  public function string($value): string {
    return trim(preg_replace("#<\s*script[^>]*>((.*?)<\s*/\s*script>)?#i", '', $value));
  }

  /**
   * Remove white spaces from begin and the end of the form field and
   * remove ALL tags
   *
   * @param mixed $value Form field value to be filtered.
   * @return string
   */
  public function stringNoTags($value): string {
    return trim(strip_tags($value));
  }

  /**
   * Remove white spaces from begin and the end of the form field.
   *
   * @param string $value Form field value to be filtered.
   * @return string
   */
  public function trim($value): string {
    return trim($value);
  }

  /**
   * Conversion from brazilian date format (dd/mm/yyyy) to database format (yyyy-mm-dd).
   *
   * @param string $value Form field value to be filtered.
   * @return string
   */
  public function toDbDate($value): string {
    return implode('-', array_reverse(explode('/', $value)));
  }

  /**
   * Remove everything that is not a number from the form field.
   *
   * @param string $value Form field value to be filtered.
   * @return string
   */
  public function digits($value): string {
    return preg_replace("/\D+/", '', (string) $value);
  }

  /**
   * Transform the value from form field to float (double). This method
   * is used by others methods and have to get the string format (9.999,99).
   * <b>If you use the format 9,999.99 so we recommend you to override these methods</b>.
   *
   * @TODO: i18n
   *
   * @param string $value Form field value to be filtered.
   * @param array $options Default value
   * @throws FormValidatorException
   * @return float
   */
  public function toDouble($value, $options = array('default' => 0.0)): float {

    if (! isset($options['default'])) {
      throw new FormValidatorException("The filter 'toDouble' needs an option 'default'");
    }

    // Can just be parsed?
    if (in_array(gettype($value), array('float', 'double', 'integer'))) {
      return (double) $value;
    }

    return (! $value) ? $options['default'] : (double) preg_replace(array(
      "/[^0-9,.]/",
      "/\./",
      "/\,/"
    ), array(
      '',
      '',
      '.'
    ), $value);
  }
}
