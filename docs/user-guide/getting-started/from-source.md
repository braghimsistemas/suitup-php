# Install From Source Tutorial

## Project Structure

Create the project folder and get into it

    $ mkdir cowboys
    $ cd cowboys

Let's create all basic needed structure of folders and files, to do that just follow the tree below.

```
cowboys/
├── assets
│   ├── main.js
│   └── styles.css
├── config
│   ├── database.config.php
│   └── default.routes.php
├── modules
│   └── ModuleDefault
│       ├── Controllers
│       │   ├── AbstractController.php
│       │   └── IndexController.php
│       ├── Form
│       ├── Model
│       │   └── Gateway
│       └── views
│           ├── index
│           │   └── index.phtml
│           └── layout.phtml
├── .htaccess
└── index.php
```

## Composer require

[Composer](https://getcomposer.org) will download all Suitup files and its dependencies as well manage future updates, so you don't have to worry about it.

Download the `composer.phar` file to manage the project dependencies from [here](https://getcomposer.org/composer.phar) or go to the [Composer Download page](https://getcomposer.org/download/) and choose your preferred type of install.

Run composer `require` to download suitup and its dependencies. It will automatically create a `composer.json` file too (a file with your project specifications).

    $ php composer.phar require braghim-sistemas/suitup-php ^2

Also you will need to set some `.htaccess` configs so Suitup can work properly.

## Files contents

### .htaccess file
file: `cowboys/.htaccess`

```
RewriteEngine on

# The following rule tells Apache that if the requested filename
# exists, simply serve it.
RewriteCond %{REQUEST_FILENAME} -s [OR]
RewriteCond %{REQUEST_FILENAME} -l [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^.*$ - [NC,L]
# The following rewrites all other queries to index.php. The
# condition ensures that if you are using Apache aliases to do
# mass virtual hosting, the base path will be prepended to
# allow proper resolution of the index.php file; it will work
# in non-aliased environments as well, providing a safe, one-size
# fits all solution.
RewriteCond %{REQUEST_URI}::$1 ^(/.+)(.+)::\2$
RewriteRule ^(.*) - [E=BASE:%1]
RewriteRule ^(.*)$ %{ENV:BASE}index.php [NC,L]

# Env variables
SetEnv DEVELOPMENT true
SetEnv SHOW_ERRORS true
```

### Index file
file: `cowboys/index.php`

```php
<?php
session_start();

// Set your timezone
// date_default_timezone_set('America/Sao_Paulo');

// Defined constants to determine test environment or production
define('DEVELOPMENT', (bool) getenv("DEVELOPMENT"));
define('SHOW_ERRORS', (bool) getenv("SHOW_ERRORS"));
if (SHOW_ERRORS) {
  error_reporting(E_ALL);
  ini_set('display_errors', true);
}

// Root path
// Now everything is related to this folder
chdir(__DIR__);

// Simple functions you should implement
// include_once './functions.php';

// Setup autoloading composer
if (file_exists('vendor/autoload.php')) {
  $loader = include 'vendor/autoload.php';

  // You may want to implement your own libraries
  // $loader->add('System', 'library/.');
} else {
  exit("Project dependencies not found, run 'php composer.phar install'");
}

// Let's start Suitup Framework
$mvc = new SuitupStart('modules/');

// Sql monitoring
$mvc->setSqlMonitor(DEVELOPMENT);

$mvc->run();

```

### Abstract Controller
file: `cowboys/modules/ModuleDefault/Controllers/AbstractController.php`

```php
<?php
namespace ModuleDefault\Controllers;

use Suitup\Mvc\MvcAbstractController;

class AbstractController extends MvcAbstractController
{
  public function init() {
    parent::init(); // Keep this line
  }

  public function posDispatch() {
    parent::posDispatch(); // Keep this line
  }
}

```

### Index Controller
file: `cowboys/modules/ModuleDefault/Controllers/IndexController.php`

```php
<?php
namespace ModuleDefault\Controllers;

class IndexController extends AbstractController
{
  public function indexAction() {
    // Here is where the legends begins...
  }
}

```

### HTML Layout
file: `cowboys/modules/ModuleDefault/views/layout.phtml`

```html
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

    <title>Hello, world!</title>
  </head>
  <body>
    <div class="container">
      <h1>Suitup PHP Framework</h1>
      <h3>ModuleDefault</h3>
      <h4>From Source Tutorial</h4>

      <?php echo $content; ?>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.9.0/holder.js"></script>
  </body>
</html>

```

### HTML View
file: `cowboys/modules/ModuleDefault/views/index/index.phtml`

```html
<!-- The content created automatically -->
<div class="row">
  <div class="col-6">
    <div class="card">
      <img class="card-img-top" src="holder.js/100px180/" alt="Card image cap">
      <div class="card-body">
        <h5 class="card-title">Card title</h5>
        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
        <a href="#" class="btn btn-primary">Go somewhere</a>
      </div>
    </div>
  </div>
  <div class="col-6">
    <div class="card">
      <img class="card-img-top" src="holder.js/100px180/" alt="Card image cap">
      <div class="card-body">
        <h5 class="card-title">Card title</h5>
        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
        <a href="#" class="btn btn-primary">Go somewhere</a>
      </div>
    </div>
  </div>
</div>

```
