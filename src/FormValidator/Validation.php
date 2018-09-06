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
 * Class Validation
 * @package SuitUp\FormValidator
 */
abstract class Validation {

  /**
   * @var array Parametros que serao validados
   */
  protected $data = array();

  /**
   * @var array Mensagens de erro para os campos que nao passaram na validacao. Disponivel apenas apos o isValid.
   */
  public $messages = array();

  /**
   * @var array Post do formulario
   */
  public $post = array();

  /**
   * @var null|bool Status das validacoes
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
   * Verifica de acordo com as regras definidas para cada campo
   * se todos os campos do formulario sao validos. Soh retorna
   * true caso TODOS eles sejam validos.
   * 
   * @return boolean
   */
  public function isValid() {
    if ($this->valid === null) {
      $this->validateForm();
    }
    return $this->valid;
  }

  /**
   * Chame este metodo para capturar os dados do post "limpos", ou seja, filtrados pelas
   * regras definidas para cada campo.
   * 
   * Se o formulário ainda não estiver validado a validação é feita, ou seja, depois de chamar este método
   * ou o isValid não é mais possível adicionar campos para validação.
   * 
   * @return array
   */
  public function getData() {

    // Se o formulario ainda não foi validado vamos valida-lo.
    if ($this->valid === null) {
      $this->validateForm();
    }

    // Captura os resultados da validacao
    $data = array();
    foreach ($this->data as $key => $item) {
      $data[$key] = isset($item['value']) ? $item['value'] : '';
    }
    return $data;
  }

  /**
   * Adiciona um item ao array de retorno dos dados.
   * 
   * @param string $index
   * @param mixed $data
   */
  public function addData($index, $data) {
    $this->data[$index]['value'] = $data;
  }

  /**
   * Retorna lista de mensagens de validacao.
   * 
   * @return array
   */
  public function getMessages() {
    return $this->messages;
  }

  /**
   * Efetua as validacoes necessarias.
   * 
   * @throws \Exception
   * @return bool
   */
  private function validateForm() {
    $result = true;
    foreach ($this->data as $field => $item) {

      // Tipos de campos que nem chegaram no post
      if (!isset($this->post[$field])) {
        $this->messages[$field][] = "Este campo é obrigatório";
        $result = false;
        continue;
      }

      // Validacoes
      foreach ($item['validation'] as $methodOrClass => $method) {

        // Validacao do ZEND =)
        if (class_exists($methodOrClass)) {

          // Campo não vazio, pois é claro que estando
          // vazio toda validacao será falsa.
          // Se quer validar se esta vazio deve utilizar
          // o metodo notEmpty
          if (!$this->post[$field]) {
            continue;
          }

          // Como é uma validacao do Zend
          // então nao é um metodo que chega como valor,
          // mas as opções do validador
          $options = $method;

          /**
           * @var \Zend\Validator\AbstractValidator
           */
          $validator = new $methodOrClass($options);

          if (!$validator->isValid($this->post[$field])) {
            $result = false;
            foreach ($validator->getMessages() as $msg) {
              $this->messages[$field][] = $msg;
            }
          }
          continue;
        }

        // É um metodo de validacao dentro da classe, mas que referencia outro campo
        if (method_exists($this, $methodOrClass)) {

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

        // É um metodo comum de validacao dentro da propria classe
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

        // Se chegar aqui é pq não tem metodo para validar o campo =/
        throw new \Exception("O metodo '$methodOrClass' ou '$method' não existe para validar o campo");
      }

      // Filtros
      $this->data[$field]['value'] = $this->post[$field];
      if (isset($item['filter'])) {
        foreach ($item['filter'] as $withOptions => $method) {

          // É um metodo que tem opcoes
          if (method_exists($this, $withOptions)) {

            $filterOptions = $method;
            $this->data[$field]['value'] = $this->$withOptions($this->data[$field]['value'], $filterOptions);

            // É um metodo?
          } else if (method_exists($this, $method)) {
            $this->data[$field]['value'] = $this->$method($this->data[$field]['value']);

            // É uma função?
          } else if (function_exists($method)) {
            $this->data[$field]['value'] = $method($this->data[$field]['value']);

            // Não! É um erro =S
          } else {
            throw new \Exception("O metodo '$method' não existe para filtrar o campo");
          }
        }
      }
    }

    // Se faltou algum campo do post que não
    // levou nenhum tipo de validacao nem filtro
    foreach ($this->post as $key => $value) {
      if (!isset($this->data[$key])) {
        $this->data[$key]['value'] = $value;
      }
    }

    // Seta o objeto
    $this->valid = (bool) $result;
    return $this->valid;
  }

}
