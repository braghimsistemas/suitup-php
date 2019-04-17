<?php
return array(
  'album-detail' => array(
    'type' => \SuitUp\Mvc\Routes::TYPE_LINEAR,
    'controller' => 'album',
    'action' => 'index',
    'params' => array(
      'id' => '/\D+/',
      'name' => '/\.(html)$/'
    )
  ),
  array(
    'type' => \SuitUp\Mvc\Routes::TYPE_LITERAL,
    'controller' => 'album',
    'action' => 'add',
    'url_list' => function () {
      return array(
        'album-add.html',
        'album-add',
        'albun-add.html',
        'albun-add',
      );
    },
    'params' => array()
  ),
  'album-edit.html' => array(
    'type' => \SuitUp\Mvc\Routes::TYPE_REVERSE,
    'controller' => 'album',
    'action' => 'edit',
    'params' => array(
      'id' => ''
    )
  ),
);
