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
  'album-edit.html' => array(
    'type' => \SuitUp\Mvc\Routes::TYPE_REVERSE,
    'controller' => 'album',
    'action' => 'edit',
    'params' => array(
      'id' => 0
    )
  ),

  // Literal Route like function closure
  array(
    'type' => \SuitUp\Mvc\Routes::TYPE_LITERAL,
    'controller' => 'album',
    'action' => 'literal-add-closure',
    'url_list' => function () {
      return array(
        'the/type/album-add.html',
        '/the/type/album-add',
        'the/type/albun-add.html',
        'the/type/albun-add',
      );
    }
  ),

  // Literal route like array
  array(
    'type' => \SuitUp\Mvc\Routes::TYPE_LITERAL,
    'controller' => 'album',
    'action' => 'add',
    'url_list' => array(
      '/the-literal-route/like-array.html',
    ),
    'params' => array()
  ),

  // Literal route like force (string)
  array(
    'type' => \SuitUp\Mvc\Routes::TYPE_LITERAL,
    'controller' => 'album',
    'action' => 'add',
    'url_list' => 'the-literal-route/like-array.html',
    'params' => array()
  ),
);
