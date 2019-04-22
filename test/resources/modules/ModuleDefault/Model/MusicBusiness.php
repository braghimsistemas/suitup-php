<?php
namespace ModuleDefault\Model;

use SuitUp\Database\Business\AbstractBusiness;

class MusicBusiness extends AbstractBusiness
{
  /**
   * Reference to gateway file
   * @var Gateway\Music
   */
  protected $gateway;

  /**
   * This is a test case, WE DO NOT RECOMMEND you to do it on your own system
   * @return Gateway\Music
   */
  public function gateway() {
    return $this->gateway;
  }
}

