# ToDoList

ToDolist is a Symfony application to manage your daily tasks
daily tasks. The goal of this pedagogic project is to update, test and document this application.

![GitHub last commit (branch)](https://img.shields.io/github/last-commit/Benlasc/ToDoList/main)  

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/6daac1aed7f5445b90cec2d86046b9ee)](https://www.codacy.com/gh/Benlasc/TodoList/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Benlasc/TodoList&amp;utm_campaign=Badge_Grade)
[![Codacy Badge](https://app.codacy.com/project/badge/Coverage/6daac1aed7f5445b90cec2d86046b9ee)](https://www.codacy.com/gh/Benlasc/TodoList/dashboard?utm_source=github.com&utm_medium=referral&utm_content=Benlasc/TodoList&utm_campaign=Badge_Coverage)

## Table of Contents
1.  __[Prerequisite and technologies](#prerequisite-and-technologies)__
  * [Server](#server)
  * [Framework and libraries](#framework-and-libraries)
2.  __[Installation](#installation)__
  * [Download or clone](#download-or-clone)
  * [Configure environment variables](#configure-environment-variables)
  * [Install the project](#install-the-project)
  * [Create the database](#create-the-database)
3.  __[Tests](#tests)__
  * [Run the tests](#run-the-tests)
4. __[Contribution](#contribution)__

---
## PREREQUISITE AND TECHNOLOGIES

### __Server__
You need a web server with PHP7 (>= 8.0.0) and MySQL.  
Versions used in this project:
* PHP 8.0.6
* MySQL 8.0.25

See more information on technical requirements in the [Symfony official documentation](https://symfony.com/doc/5.3/setup.html#technical-requirements).

### __Framework and libraries__
Framework: __Symfony ^5.3__  
Dependencies manager: __Composer ^2.1.8__  

---
## INSTALLATION

### __Download or clone__
Download zip files or clone the project repository with github ([see GitHub documentation](https://docs.github.com/en/github/creating-cloning-and-archiving-repositories/cloning-a-repository)).

### __Configure environment variables__
Configure the database server url in your environment file:
```
...
###> doctrine/doctrine-bundle ###
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7"
```

### __Install the project__
1.  If needed, install __Composer__ by following [the official instructions](https://getcomposer.org/download/).
2.  In your cmd, go to the directory where you want to install the project and install dependencies with composer:
```
$ cd some\directory
$ composer install
```
Dependencies should be installed in your project (check _vendor_ directory).  

### __Create the database__
If you are in a dev environment, you can create the database and fill it with fake contents with the following commands in the project directory:

1. create the database 
```
php bin/console doctrine:database:create
```
2. Create database structure thanks to migrations:
```
php bin/console doctrine:migrations:migrate
```
3. Install fixtures to have fake contents:
```
php bin/console doctrine:fixtures:load
```
Your database should be updated with fake tasks and users.

---
## TESTS

### __Run the tests__
To run all tests, use the following command:
```
./vendor/bin/phpunit
```
See more details and options about command-line test runner in [PHP Unit documentation - EN](https://phpunit.readthedocs.io/en/latest/textui.html) / [FR](https://phpunit.readthedocs.io/fr/latest/textui.html).

---
## CONTRIBUTION

See [Contributing file](CONTRIB.md).
