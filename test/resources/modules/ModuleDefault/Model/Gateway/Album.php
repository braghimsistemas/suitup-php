<?php
namespace ModuleDefault\Model\Gateway;

use SuitUp\Database\Gateway\AbstractGateway;

class Album extends AbstractGateway {

  /**
   * Required. Table name and pk's list
   */
  protected $name = 'album';
  protected $primary = array('pk_album');

  /**
   * Optional
   * You can define here a column from your table
   * that must to be updated with current timestamp
   * every UPDATE call
   */
  protected $onUpdate = array('updated' => 'NOW()');


}

