# Framework Braghim Sistemas
Este é um framework para projetos web no qual podemos considerar tanto pequenos quanto grandes aplicativos. Um pequeno sistema feito de forma simples, mas efetivo e de alta produtividade.

## Entendendo o projeto
### Porquê este framework e não outro?
Você deve se perguntar porquê utilizar este framework e não um Zend, por exemplo. Nós fizemos a nós mesmos a mesma pergunta e podemos dizer o seguinte.
Ao utilizar um framework grande como o Zend 2 nós acabamos caindo em uma situação que acaba prejudicando a produtividade do proejto por que a configuração é massiva e requer muita análise e muito tempo gasto com a estruturação. Para criar um novo controlador com Zend 2 por exemplo é necessário além de criar o arquivo, configurar o módulo para entende-lo. Isso tudo, além de sua biblioteca ser grande e ocupar bastante espaço físico no servidor com uma grande quantidade de arquivos que muitas vezes nem são utilizados pelo projeto, nos fez repensar a questão de utilizar o Zend e em vez disso usarmos um framework pequeno e simples, mas que utiliza as bibliotecas do Zend 2 como suporte para ferramentas que ele provê, que são fantásticas e muito bem feitas.

*Nosso famework também trabalha com módulos* e é focado na produtividade do projeto.

### Instalação Composer
`composer require braghim-sistemas/framework`

### Estrutura de projeto
Esta é a estrutura recomendada do projeto, lembre-se que dentro da pasta onde os modulos fica, os controladores, views e models devem seguir a estrutura, os formulários não precisam. Você pode colocar a pasta de arquivos css, js, etc, onde julgar melhor.

    config
      database.config
    modules
      ModuleDefault
        Controllers
          IndexController.php
          ErrorController.php
        Form
          Index
            Contato.php
        Model
          Gateway
            User.php
          SqlFiles
            user
              getById.sql
          UserBusiness.php
        views
      ModuleAdmin
    .htaccess
    composer.json
    composer.phar
    index.php

