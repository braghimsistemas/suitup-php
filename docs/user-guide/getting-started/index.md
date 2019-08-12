# How to begin

## Requirements

Suitup Framework requires PHP >= 7.2 and whatever web server you prefer. For this tutorials we
will create the project with some addictional features to make it easy and improve the Suitup
super powers. To engage you with that check it out if you need first study some of following
features: `Composer` - `Git` - `Apache` - `htaccess` and _optionally_ `MySql` - `Docker & Docker Compose`

**Github** 

First of all, there is a Skeleton Project created properly to begin new projects in the link bellow,
no matter how method of instalation you will choose, the structure will be the same:

[https://github.com/braghimsistemas/suitup-skeleton](https://github.com/braghimsistemas/suitup-skeleton)

!!! tip
    You will be able to change directory structure of the project later if you want to,
    but for this tutorials we will follow the basic structure, making everything easyer.

## Install

There are several ways to create projects with Suitup Framework, you can even create a new project
by command line with our Suitup Manager software. Bellow you will find a list with all this
possibilities by difficult level:

  - [Docker](#docker) - Very easy
  - [Skeleton Project](#skeleton-project) - easy
  - [Suitup Manager (command line)](#suitup-manager) - easy
  - [From Source (Recommended for beginners)](#from-source) - medium

!!! info "Important"
    To really understand HOW Suitup works **we recommend you to install by [From Source](#from-source)**
    method even it taking a bit more time.

### Docker

Docker is a tool created to store containers and it works almost like virtual machines. When you
create a project with Docker it's not necessary even to have Apache installed in you machine,
even linux is not required, Docker will manage everything for you by a predefined structure
described in the `docker-compose.yml` file.

To begin with Docker you need to install it locally following your system requirements.
[Here you can find all documentation needed to do it](https://www.docker.com/get-started).
Remember that there are two features that you need to install: `Docker` and `Docker Compose`!

!!! warning
    1. Docker is one of the easyest way to init a Suitup project but maybe it's the hardest way to
    modify it because of the configurations made by `docker-compose.yml` file.

    2. We will assume that you are on linux (GNU system based).

Walk to the folder where you want to install the project, maybe you already have Apache and PHP
installed, but you don't need to put it on the localhost, actually you can install anywhere when
we talk about Docker, of course.

Clone the project

    #!bash
    # step 1.
    $ git clone git@github.com:braghimsistemas/suitup-skeleton.git

Enter inside the project folder

    #!bash
    # Step 2.
    $ cd suitup-skeleton
    
Up the Docker services

    #!bash
    # Step 3.
    $ docker-compose up -d

After that, you must be able to access `localhost:8080` and the project must to be running
already, but as you don't downloaded dependencies the following message will be shown.

`Project dependencies not found, run 'php composer.phar install'`

We are assuming that you don't have php installed, so let access the Docker apache container
and download the composer dependencies from there. _Remember that if you already have PHP 7.2+ installed skip to the step 6 and **avoid** step 7._

    #!bash
    # Step 4.
    $ docker exec -it suitup-skeleton /bin/bash
    
Walk into application docker folder
    
    #!bash
    # Step 5.
    $ cd /app

Download the composer dependencies

    #!bash
    # Step 6.
    $ php composer.phar install

Grant access to the vendors folder

    #!bash
    #Step 7.
    $ chmod 775 vendor -R

Done! After that all you need is to access on your browser: `localhost:8080`

### Skeleton Project

[Check here](#setup-web-server) to see how to setup a web server first.

Clone the project from it's repository on github:

    #!bash
    $ git clone git@github.com:braghimsistemas/suitup-skeleton.git

Walk into folder:

    #!bash
    $ cd suitup-skeleton/

Install composer dependencies

    #!bash
    $ php composer.phar install

Done! Open on the browser the following address: `http://localhost/suitup-skeleton`
(assuming that you installed directly on the localhost). If it was done with virtual
host don't forget to add the line `127.0.0.1  {the new domain}` to the `/etc/hosts` file.

### Suitup Manager

To automate some repetitive tasks we created a command line debian based software that can initialize a new project, create a new controller, "model" files, forms, etc...

All you gotta do to get it is to download the `suitup.deb` file from the latest release [from here](https://github.com/braghimsistemas/suitup-php/releases/latest) and install it with the command bellow.

    #!bash
    $ sudo dpkg -i ~/Downloads/suitup.deb

After that you must to be able to run the `suitup` command from your terminal with the following options:

!!! tip
    Use the commands bellow to run automated actions into your project

    ```bash
    $ suitup install            /* Install a brand new project */
    $ suitup create module      /* create a new module to an existing project */
    $ suitup create controller  /* create a new controller with its views */
    $ suitup create form        /* Starts a new form validator */
    $ suitup create dbtable     /* Create the structure to a database table (Business and Gateway files) */
    ```

So run `$ suitup install` and follow the suggested steps to entirely create a new project, it will ask you to automatically download composer dependencies too.

After that you just need to [setup the web server](#setup-web-server) and be happy with your brand new project.

Ps.: A folder with that name given to the project will be created

### From Source

For beginners, at least with **this** framework, we highly recommend to
create your project by this method so you will understand a bit more how
SuitUp works and its mechanics.

Start setting up the [web server](#Setup-web-server) as you wish. We will assume that you are creating a project named **cowboys** into default localhost. Get into your localhost folder (over linux defaults it is `/var/www/html`).

As this is the more instructive but the largest way to do, follow this [complete tutorial](from-source) that will lead you on how to create all the files one by one and its contents, letting you know what everything means step by step. That's why this is the best way to begin.

[Install From Source Tutorial](from-source)

---

## Setup Web Server

First of all you will need a web server like apache, nginx or whatever you prefer and PHP version 7.2+.

!!! question "What is a WEB SERVER?"
    A Web server is a program that uses HTTP (Hypertext Transfer Protocol) to serve the files that form Web pages to users, in response to their requests, which are forwarded by their computers' HTTP clients. Dedicated computers and appliances may be referred to as Web servers as well. Reference: [WhatIs.com](https://whatis.techtarget.com/definition/Web-server)

### Apache Web Server

Install apache web server with apt:

    #!bash
    $ sudo apt-get install apache2

The `mod_rewrite` is required to work with friendly
URL's routes.

    #!bash
    $ sudo a2enmod rewrite

After enable mod rewrite you shall need to restart the server.

    #!bash
    $ sudo service apache2 restart

It's important that you need to **allow override** on the virtual host
where you will run the application (even in localhost). It will allow
to replace in the URL names of directories with needed route names
like modules, controllers, actions and parameters too. In few words it
means that every request in the `http://localhost`, no matter what URI,
will call the same file: `/var/www/html/index.php`

The example bellow shows how the default virtual host address must looks
like with that.

    #!bash
    $ sudo vi /etc/apache2/sites-available/000-default.conf

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

That's it. Your apache server is ready to run SuitUp framework.

