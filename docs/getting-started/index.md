# Requirements

Suitup Framework requires PHP >= 7.2 and whatever web server you prefer. For this tutorials we
will create the project with some addictional features to make it easy and improve the Suitup
super powers. To engage you with that check it out if you need first study some of following
features: Composer - Git - Apache - htaccess - MySql - Docker & Docker Compose

**Github** 

First of all, there are a Skeleton Project created properly to begin new projects in the link bellow,
no matter how method of instalation you will choose, the structure will be the same:

[https://github.com/braghimsistemas/suitup-skeleton](https://github.com/braghimsistemas/suitup-skeleton)

  > You will be able to change directory structure of the project later if you want to,
  but for this tutorials we will follow the basic structure, making everything easyer.

## Install

There are several ways to create projects with Suitup Framework, you can even create a new project
by command line with our Suitup Manager software. Bellow you will find a list with all this
possibilities by difficult level:

  - [Docker](#docker) - Very easy
  - [Skeleton Project](#skeleton-project) - easy
  - [Suitup Manager (command line)](#suitup-manager) - easy
  - [From Source (Recommended)](#from-source) - medium

  > To really understand HOW Suitup works **we recommend you to install by [From Source](#from-source)**
  method even it taking a bit more time.

### Docker

Docker is a tool created to store containers and it works almost like virtual machines. When you
create a project with Docker it's not necessary even to have Apache installed in you machine,
even linux is not required, Docker will manage everything for you by a predefined structure
described in the `docker-compose.yml`

To begin with Docker you need to install it locally following your system requirements.
[Here you can find all documentation needed to do it](https://www.docker.com/get-started).
Remember that there are two features that you need to install: `Docker` and `Docker Compose`!

  > 1. Docker is one of the easyest way to init a Suitup project but maybe it's the hardest way to
  modify it because of the configurations made by `docker-compose.yml` file.

  > 2. We will assume that you are on linux.

Walk to the folder where you want to install the project, maybe you already have Apache and PHP
installed, but you don't need to put it on the localhost, actually you can install anywhere when
we talk about Docker, of course.

Clone the project

    $ git clone git@github.com:braghimsistemas/suitup-skeleton.git

Enter inside the project folder

    $ cd suitup-skeleton
    
Up the Docker services
    
    $ docker-compose up -d

After that, you must be able to access `localhost:8080` and the project must to be running
already, but as you don't downloaded dependencies the following message will be shown.

`Project dependencies not found, run 'php composer.phar install'`

We are assuming that you don't have php installed, so let access the Docker apache container
and download the composer dependencies from there. _Remember that if you have PHP 7.2+ installed
it's just run in the documnt root of project the command recommended above._

    $ docker exec -it suitup-skeleton /bin/bash
    
Walk into application docker folder
    
    $ cd /app

Download the composer dependencies

    $ php composer.phar update

Grant access to the vendors folder

    $ chmod 775 vendor -R

Done! After that all you need is to access on your browser: `localhost:8080`

### Skeleton Project

[Check here](#setup-web-server) how to setup the web server first.

Clone the project from it's repository on github:

    $ git clone git@github.com:braghimsistemas/suitup-skeleton.git

Walk into folder:

    $ cd suitup-skeleton/

Install composer dependencies

    $ php composer.phar install

Done! Open on the browser the following address: `http://localhost/suitup-skeleton`
(assuming that you installed directly on the localhost). If it was done with virtual
host don't forget of add the line `127.0.0.1  {the new domain}` to the `/etc/hosts` file.

### Suitup Manager

### From Source

---

## Setup Web Server

Before begin you need to install a web server like apache, nginx or whatever you
prefer and PHP version 7.2+. The `mod_rewrite` is required to work with friendly
URL's routes.

  - Apache: `$ sudo a2enmod rewrite`

After enable mod rewrite you shall need to restar the server.

### Apache Server

Install apache web server with apt:

`$ sudo apt-get install apache2`

It's important that you need to **allow override** on the virtual host
where you will run the application (even in localhost). It will allow
to replace in the URL names of directories with needed route names
like modules, controllers, actions and parameters too. In few words it
means that every request in the `http://localhost`, no matter what URI,
will call the same file: `/var/www/html/index.php`

The example bellow shows how the default virtual host address must looks
like with that.

`# vi /etc/apache2/sites-available/000-default.conf`

```
# Apache Example
<VirtualHost *:80>
    ServerAdmin webmaster@localhost

    ServerName localhost
    ServerAlias localhost

    DocumentRoot /var/www/html
    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride all
        Order allow,deny
        Allow from all
    </Directory>

    LogLevel error
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined

</VirtualHost>
```

### Nginx Server

### PHP Built-in Server
