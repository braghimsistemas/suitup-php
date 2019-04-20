<?php


namespace SuitUp\Database\Gateway\QueryString;


class Join
{
  const INNER_JOIN = 'INNER JOIN';

  const FULL_INNER_JOIN = 'FULL INNER JOIN';

  const OUTER_JOIN = 'OUTER JOIN';

  const FULL_OUTER_JOIN = 'FULL OUTER JOIN';

  const RIGHT_JOIN = 'RIGHT JOIN';

  const LEFT_JOIN = 'LEFT JOIN';

  private $type;

  private $table;

  private $onClause;

  public function __construct(string $type, string $table, string $onClause, string $schema = null)
  {
    $this->type = $type;
    $this->table = $schema ? "$schema.$table" : $table;
    $this->onClause = $onClause;
  }

  public function __toString()
  {
    return "{$this->type} {$this->table} ON {$this->onClause}";
  }
}
