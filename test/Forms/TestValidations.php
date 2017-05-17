<?php
namespace SuitUpTest\Forms;

use SuitUp\FormValidator\AbstractFormValidator;

class TestValidations extends AbstractFormValidator
{
  protected $data = array(
    // No data, tests will be realized by direct method access
    // So, why we have to create this class instead direct instanciate
    // AbstractFormValidator? It's because that class is abstract,
    // so we can not instanciate.
  );
}