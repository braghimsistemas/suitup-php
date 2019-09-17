# Overview

Even before PHP 7 comes up Suitup already was alive and active. We build this
project as better as possible over all this years always focused on the productivity.

Here you will understand how and when Suitup do stuff
to build your website or system fast and really well
organized.

## Structure

### The index file (rewrite mod)

First of all you need to understand that Suitup works
with `mod_rewrite`. It means that all the URL routes
will load, for first, the same `index.php` file in the
document root of the project. Except resources files like
css, js, jpg, png, txt...

Remember that to make it works you will need to set up the
properly `.htaccess` file. You will find more about it [here](/user-guide/getting-started/from-source/).

### What about routes?

Suitup build automatic routes using three obvious parameters
that are: module name, controller name and the current action.

#### Default Routes

Module = ModuleDefault (folder name)

Below is the main route of the system so when you access the project with no additional
URL this is the `action` dispatched.

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

!!! Note "AbstractController"
    It's actually a _middle class_ that stands for `SuitUp\Mvc\MvcAbstractController` class. Keep reading about it in [this section](/user-guide/reference/abstract-controller), this is one of the most important things about Suitup.

#### Personal Routes

Yes! You can set your own routes, of course. To do that you
just need to create one file by module like bellow. Read more about the contents of this files [here](/user-guide/reference/routes).

`config/default.routes.php` to the module default.

`config/admin.routes.php` to the module admin.

### Database connection

All you got to do to setup the database connection is to
configure the `config/database.config.php` file as shown
detailed [here](/user-guide/reference/models-database-workflow).

After have the database connection you will want to create two files
for each database table, `Business` and `Gateway`. 

The `Business` files control the flow of actions over the table
rows and the `Gateway` must to efectivelly apply the SQL queries.

!!! Abstract "Help wanted"
    We really need help to improve Suitup compatibility
    with other kinds of database than MySql. Come on and
    [contribute](/contributors/) with us.

## Workflow

When the user access some URL over the project this request will be thrown over the `index.php` file
and this file will for first setup the project itself and start the Suitup. After that a method named
`run` is called and it will load all requirements needed as follow: Check and setup database connection,
try to load the current route, if fail try to run the user defined `ErrorController`, if fail try to run
the Suitup internal `ErrorController`. When everything is ok and Suitup load the route successfuly (even
when it is an `ErrorController`),
so first is loaded the `Controller` class instance and it is followed by its methods right in that order:

  - $controller->preDispatch();
  - $controller->init();
  - $controller->{$frontController->getActionName()}();
  - $controller->posDispatch();
  - $controller->render();

The `render` method will render the view (phtml) file and show the result back.
