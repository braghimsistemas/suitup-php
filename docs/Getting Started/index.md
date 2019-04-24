# Requirements

Suitup Framework requires PHP >= 7.2 and whatever web server you prefer. For this tutorials we
will create the project with some addictional features to make it easy and improve the Suitup super powers. To engage you with that check it out if you need first study some of following features: Composer - Git - Apache - htaccess - MySql - Docker & Docker Compose

**Github** 

First of all, there are a Skeleton Project created properly to begin new projects in the link bellow, no matter how method of instalation you will choose, the structure will be the same:

[https://github.com/braghimsistemas/suitup-skeleton](https://github.com/braghimsistemas/suitup-skeleton)

  > You will be able to change directory structure of the project later if you want to, but for this tutorials we will follow the basic structure, making everything easyer.

# Instalation

There are several ways to create projects with Suitup Framework, you can even create a new project by command line with our Suitup Manager software. Bellow you will find a list with all this possibilities by difficult level:

  - [Docker](#docker) - Very easy
  - [Skeleton Project](#skeleton-project) - easy
  - [Suitup Manager (command line)](#suitup-manager) - easy
  - [From Source (Recommended)](#from-source) - medium

  > To really understand HOW Suitup works **we recommend you to install by [From Source](#from-source)** method even it taking too much more time.

## Docker

Docker is a tool created to store containers and it works almost like virtual machines. When you create a project with Docker it's not necessary even to have Apache installed in you machine, even linux is not required, Docker will manage everything for you by a predefined structure described in the `docker-compose.yml`

To begin with Docker you need to install it locally following your system requirements.
[Here you can find all documentation needed to do it](https://www.docker.com/get-started).
Remember that there are two features that you need to install: `Docker` and `Docker Compose`!

  > 1. Docker is the easyest way to init a Suitup project but maybe it's the hardest way to modify it because of the configurations made by `docker-compose.yml` file.

  > 2. We will assume that you are on linux.

Walk to the folder where you want to install the project, maybe you already have Apache and PHP installed, but you don't need to put it on the localhost, actually you can install anywhere when we talk about Docker, of course.

Clone the project

    $ git clone git@github.com:braghimsistemas/suitup-skeleton.git

Enter inside the project folder

    $ cd suitup-skeleton
    
Up the Docker services
    
    $ docker-compose up -d

Access the Docker apache container

    $ docker exec -it suitup-apache /bin/bash
    
Walk to the application docker folder
    
    $ cd /app

Download the composer packages

    $ php composer.phar update

Done! After that all you need is to access on your browser: `localhost:8080`

## Skeleton Project

## Suitup Manager

## From Source
