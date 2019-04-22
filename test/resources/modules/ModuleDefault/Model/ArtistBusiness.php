<?php
namespace ModuleDefault\Model;

use SuitUp\Database\Business\AbstractBusiness;

class ArtistBusiness extends AbstractBusiness
{
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Reference to gateway file
   * @var Gateway\Artist
   */
  protected $gateway;
}

