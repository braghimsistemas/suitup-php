# Install From Source Tutorial

Start setting up the [web server](/user-guide/getting-started/#setup-web-server) as you wish.
We will assume that you are creating a project named **cowboys**
into default localhost. Get into your localhost folder (over linux
defaults it is `/var/www/html`).

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

Run composer `require` to download Suitup and its dependencies. It will automatically create a `composer.json` file too (a file with your project specifications).

    $ php composer.phar require braghim-sistemas/suitup-php ^2

## Files contents

Let's put some content into these files. 

### .htaccess file
file: `cowboys/.htaccess`

Set some `.htaccess` configs so Suitup can work properly.

```properties
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

!!! note "Important"
    Note that there's two  environments variables `DEVELOPMENT` and `SHOW_ERRORS`.
    
    You can set all the system to show in the screen the errors occurring in real
    time to debug. It will be done in the errors pages.
    
    Of course if you define `DEVELOPMENT` another kind of actions will be launched
    as you wish in the system such as show a list of SQL queries done to load the
    page. Actually this one is set so you can use your creativity to use as wish.
    
    You can simply don't use it by removing these lines or commenting as shown bellow. 
    
    ```properties
    # Env variables
    #SetEnv DEVELOPMENT true
    #SetEnv SHOW_ERRORS true
    ```  

### Index file
file: `cowboys/index.php`

This is the most important file of whole system. It's by this file that every
call to the system is done and every URL should run by here. It's from here that
the system will load everything, even the Suitup itself.

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

### MVC

A stop to coffee...

!!! Tip 
    Now we will begin to setup some MVC code. If you aren't familiar to this concept yet,
    please go ahead and make some researches so you can back to this tutorial with
    necessary knowledge to continue. 

Basically...

> "MVC is a software architecture - the structure of the system - that separates domain/application/business (whatever you prefer) logic from the rest of the user interface. It does this by separating the application into three parts: the model, the view, and the controller.

> The model manages fundamental behaviors and data of the application. It can respond to requests for information, respond to instructions to change the state of its information, and even to notify observers in event-driven systems when information changes. This could be a database, or any number of data structures or storage systems. In short, it is the data and data-management of the application.
 
> The view effectively provides the user interface element of the application. It'll render data from the model into a form that is suitable for the user interface.
 
> The controller receives user input and makes calls to model objects and the view to perform appropriate actions.
 
> All in all, these three components work together to create the three basic components of MVC."

[Link to this reference](https://softwareengineering.stackexchange.com/a/127632)

### Abstract Controller
file: `cowboys/modules/ModuleDefault/Controllers/AbstractController.php`

This file will be a regards to the all controllers from the module, it means that
all the others controllers will extends the `AbstractController` so its contents
is shared over. So if you need to create a method that must to be accessible over
all controllers, make it in this file.

This file is not actually required, but helps a lot and we highly recommend

!!! Tip
    Over this class there's some methods that is automatically called by Suitup
    in a certain order:
    
    * __construct() // Avoid override this method
    * preDispatch()
    * init()
    * {current}Action()
    * posDispatch()
    
!!! Danger "IMPORTANT"
    **Every time you override one of these methods, please call the parent method
    inside to ensure that it will work properly**
    
    
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
