<?php
namespace ModuleDefault\Model\Gateway;

use SuitUp\Database\Gateway\AbstractGateway;

class Music extends AbstractGateway {

  /**
   * Required. Table name and pk's list
   */
  protected $name = 'music';
  protected $primary = array('pk_music');

  /**
   * Optional
   * You can define here a column from your table
   * that must to be updated with current timestamp
   * every UPDATE call
   */
  // protected $onUpdate = array('edited' => 'NOW()');


}

