#!/bin/bash
###################################################################
#Script Name	: suitup
#Description	: SuitUp Manager
#Args         : create module <name>,
#               create controller <name> <module>,
#               create form <name> <module>,
#               create project <folder>,
#               dbtable <dbname> <module> <pk1> <pk2> <pk3>...
#               
#Author       : Braghim Sistemas
#Email        : braghim.sistemas@gmail.com
###################################################################

version="1.0.0"

# Current folder where USER is
folder=$(pwd)

# Colors
r='\e[31m'     # Red
g='\e[32m'     # Green
y='\e[33m'     # Yellow
b='\e[34m'     # Blue
p='\e[35m'     # Purple
d='\e[39m'     # Default color
bold='\e[1m'   # bold
dbold='\e[21m' # Default bold

declare -a List=()

# Our own echo function
function _echo() {
  clear
  echo -e "${y} \n        SuitUp Manager - Version: $version ${d}"
  echo -e "==============================================="
  echo -e ""
  if [ "$user" = "root" ]; then
    echo -e "  Current User: ${r}root${d}"
  else
    echo -e "  Current User: ${p}${user}${d}"
  fi
  echo -e "  Current Path: ${p}${folder}${d}"
  
  # Appended messages
  if ! [ ${#List[@]} -eq 0 ]; then
    for item in "${List[@]}"; do
      echo -e "  ${item}"
    done
  fi
  echo -e "-----------------------------------------------"
  echo -e ""

  cancelTip=1
  append=0

  if [ "$1" != "" ]; then
    for s in "$@" 
    do

      # If param is a command
      case "${s}" in
        -t|--no-tip)
          cancelTip=0
          ;;
        -a|--append)
          append=1
          ;;
        -*)
          ;;
        *)
          if [ $append -eq 1 ]; then
            List+=("${s}")
            append=0
          fi

          # It is just a string
          echo -e "${s}"
          ;;
      esac

    done
    echo -e ""

    if [ $cancelTip -eq 1 ]; then
      echo -e "Type CTRL+C whenever you want to ${r}cancel${d}"
    fi
  fi
}

# Avoid root user
user=$(whoami);
if [ "$user" = "root" ]; then
  
  if [ "$SUDO_USER" != "" ]; then

    _echo "You are calling this program with sudo, it's not necessary."\
    "Should we set ${b}'${SUDO_USER}'${d} instead? (Y/n)"
    read -r notSudo

    # Default is YES
    if [ "$notSudo" = "" ] || [ "${notSudo,,}" = "y" ] || [ "${notSudo,,}" = "yes" ]; then
      user=$SUDO_USER
    fi
  fi

  _echo "${b}Running program under user '$user'${d}"
fi

# Source = Path to the script file itself
SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  TARGET="$(readlink "$SOURCE")"
  if [[ $SOURCE == /* ]]; then
    SOURCE="$TARGET"
  else
    DIR="$( dirname "$SOURCE" )"
    SOURCE="$DIR/$TARGET" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
  fi
done
# RDIR="$( dirname "$SOURCE" )"
DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

# Show to the user the help document options
if [ "$1" = "--help" ] || [ "$1" = "-h" ]; then
  cat "${DIR}/help.md"
  exit 0;
fi

###################################################################
#                  Functions Declarations                         #
###################################################################

# Return the Capitalized version of string
function capitalize() {
  local word=${1,,}
  printf "%s" "${word^}"
}

# Remove empty spaces from a string
# link: https://stackoverflow.com/questions/369758/how-to-trim-whitespace-from-a-bash-variable
function trim() {
  local var="$*"
  # remove leading whitespace characters
  var="${var#"${var%%[![:space:]]*}"}"
  # remove trailing whitespace characters
  var="${var%"${var##*[![:space:]]}"}"   
  echo -n "$var"
}

# Validate if modules path exists, some actions require it
function checkModulesPath() {
  if [ ! -d "$1" ]; then
    _echo -t "${r}There's no modules folder here. Is this the root of the project?${d}"
    exit 1
  fi
}

# Concat a list to string
function joinBy() {
    # $1 is return variable name
    # $2 is sep
    # $3... are the elements to join
    local retname=$1 sep=$2 ret=$3
    shift 3 || shift $(($#))
    printf -v "$retname" "%s" "$ret${@/#/$sep}"
}

# This function create a new module
function createNewModule() {

  local path=$2              # Path to the modules
  local name="Module${1}"    # Name of the new module
  local force=$3

  # Force no check module?
  if [ "${force,,}" != "-f" ] && [ "${force,,}" != "--force" ]; then
    checkModulesPath "$path"
  fi

  # Validate module creation
  if [ -d "${path}/${name}" ]; then
    _echo -t "The module named ${r}'$name'${d} already exists"
    exit 1
  fi

  # Create folders
  mkdir -p "${path}/${name}/Controllers/"
  mkdir -p "${path}/${name}/Form/"
  mkdir -p "${path}/${name}/Model/Gateway"
  mkdir -p "${path}/${name}/views/index/"

  # Write documents
  
  # IndexController.php
  cat <<EOF > "${path}/${name}/Controllers/IndexController.php"
<?php
namespace $name\Controllers;

class IndexController extends AbstractController
{
  public function indexAction() {
    // Here is where the legends begins...
  }
}

EOF

  # AbstractController.php
  cat <<EOF > "${path}/${name}/Controllers/AbstractController.php"
<?php
namespace $name\Controllers;

use SuitUp\Mvc\MvcAbstractController;

class AbstractController extends MvcAbstractController
{
  public function init() {
    parent::init(); // Keep this line
  }
  
  public function posDispatch() {
    parent::posDispatch(); // Keep this line
  }
}

EOF

  # layout.phtml
  cat <<EOF > "${path}/${name}/views/layout.phtml"
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
      <h1>$name</h1>
      <h4>Created by SuitUp Manager Version: $version</h4>

      <?php echo \$content; ?>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/holder/2.9.0/holder.js"></script>
  </body>
</html>

EOF

  # index/index.phtml
  cat <<EOF > "${path}/${name}/views/index/index.phtml"
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

EOF

  chown "${user}:${user}" "${path}/${name}" -R

  _echo -t "${g}Done. Enjoy your new module =)${d}"
}

# This function will generate files and folders to a new controller (with view)
function createController() {

  local name="${1}Controller"       # Name of the new controller
  local viewName="${1,,}"           # The view name (lowercase)
  local module=$2                   # Name of the module (Namespace)
  local path=$3                     # Path to the modules

  checkModulesPath "$path"

  # Create folders if does not exist
  mkdir -p "${path}/${module}/Controllers/"
  mkdir -p "${path}/${module}/views/${viewName}/"

  # Check if controller already exists
  if [ -f "${path}/${module}/Controllers/$name.php" ]; then
    _echo -t "It's embarrassing but ${r}'${path}/${module}/Controllers/${name}.php'${d} file already exists."\
          "${r}Aborting...${d}"
    exit 1
  fi

  # If exists the AbstractController file to this specific module as recommended
  use="\nuse SuitUp\Mvc\MvcAbstractController;"
  extends="MvcAbstractController"
  if [ -f "${path}/${module}/Controllers/AbstractController.php" ]; then
    use=""
    extends="AbstractController"
  fi

  # The controller
  cat <<EOF > "${path}/${module}/Controllers/$name.php"
<?php
namespace $module\Controllers;
$(echo -e $use)
class $name extends $extends
{
  public function indexAction() {
    // Here is where the legends begins...
  }
}

EOF

  # index/index.phtml
  cat <<EOF > "${path}/${module}/views/${viewName}/index.phtml"
<!-- The content created automatically -->
<div class="row">
  <div class="col-6">
    <div class="card">
      <img class="card-img-top" src="holder.js/100px180/" alt="Card image cap">
      <div class="card-body">
        <h5 class="card-title">$name</h5>
        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
        <a href="#" class="btn btn-primary">Go somewhere</a>
      </div>
    </div>
  </div>
  <div class="col-6">
    <div class="card">
      <img class="card-img-top" src="holder.js/100px180/" alt="Card image cap">
      <div class="card-body">
        <h5 class="card-title">$name</h5>
        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
        <a href="#" class="btn btn-primary">Go somewhere</a>
      </div>
    </div>
  </div>
</div>

EOF

  # File owner
  chown "${user}:${user}" "${path}/${module}/Controllers/${name}.php"
  chown "${user}:${user}" "${path}/${module}/views/${viewName}/index.phtml"

  _echo -t "${g}Done. We also created the view file for you${d}"

}

# Create a form file for a module
function createForm() {

  local name=$1                     # Name of the new form
  local module=$2                   # Name of the module (Namespace)
  local path=$3                     # Path to the modules

  checkModulesPath "$path"

  # Split into array
  readarray -d / -t parts <<< "$name"

  folder="${path}/${module}/Form/"
  namespace="${module}\\Form"
  if [ ${#parts[@]} -eq 2 ]; then
    
    # Redefine variables
    name=$(trim "${parts[1]}")
    folder="${path}/${module}/Form/${parts[0]}"
    namespace="${module}\\Form\\${parts[0]}"
  fi

  # Validate file exists
  if [ -f "${folder}/${name}.php" ]; then
    _echo -t "The file ${r}'${folder}/${name}.php'${d} already exists, nothing changed"
    exit 1
  fi

  # Create if folder doesnt exists
  if [ ! -d "$folder" ]; then
    mkdir -p "$folder"
  fi

  # Create the file itself
  cat <<EOF > "${folder}/${name}.php"
<?php 
namespace $namespace;

use SuitUp\FormValidator\AbstractFormValidator;

class $name extends AbstractFormValidator
{

  /**
   * @var array 
   */
  protected \$data = array(
    // Your validations here
  );
}

EOF

  chown "${user}:${user}" "${folder}/${name}.php"

  _echo -t "${g}Done. Yeah, it was legen... { wait for it } ...dary!${d}"

}

# Start a new fresh project (skeleton)
function createProject() {

  local name=$1      # The simple name of the new project
  local folder=$2    # The folder where the new module is about to created

  # Check if project folder exists
  if [ -d "${folder}/${name}" ]; then
    _echo "Folder ${r}'${folder}/${name}'${d} already exists, project can not be created here"
    exit 1
  fi

  # Create the project folder
  mkdir -p "${folder}/${name}"

  # Move to project folder
  cd "${folder}/${name}" || ( clear; echo -e "Error - cd command doesn't work"; exit 1; )

  # We will try to download composer
  _echo "We will try to download and install ${p}composer${d} now..."

  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
  php "composer-setup.php"
  php -r "unlink('composer-setup.php');"

  # Check if composer.phar file was downloaded
  if [ -f "${folder}/${name}/composer.phar" ]; then
    php "composer.phar" "self-update" # It may be an old version
    
    # Gerenerate composer.json with composer?
    _echo "Do you wanna to be guied with composer to create composer.json file? (y/N)"
    read -r mustInit

    # Init with composer to create composer.json file?
    if [ "${mustInit,,}" = "y" ] || [ "${mustInit,,}" = "yes" ]; then
      php "composer.phar" "init"
      php "composer.phar" "require" "braghim-sistemas/suitup-php" "dev-master"
    fi

    # If composer.json file was not generated by composer init
    if [ ! -f "${folder}/${name}/composer.json" ]; then
      cat <<EOF > "${folder}/${name}/composer.json"
{
  "name": "${folder}/${name}",
  "description": "My awesome project",
  "type": "project",
  "license": "private",
  "authors": [{
    "name": "Your Name",
    "email": "youremail@address.com"
  }],
  "require": {
    "braghim-sistemas/suitup-php": "dev-master"
  }
}

EOF
      
      # Update dependencies
      _echo "We will update your dependencies now..."
      php "composer.phar" "update"

    fi

  fi # END Check if composer.phar file was downloaded

  # Needle folders
  mkdir -p "${folder}/${name}/config"
  mkdir -p "${folder}/${name}/modules"
  mkdir -p "${folder}/${name}/var/log"
  mkdir -p "${folder}/${name}/assets"

  # .htaccess file
  cat <<EOF > "${folder}/${name}/.htaccess"
# Does not show folders content
Options -Indexes

RewriteEngine on

# check https
#RewriteCond %{HTTPS} off
#RewriteRule .* https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# check www
# NC = case insensitive
#RewriteCond %{HTTP_HOST} !^www\. [NC]
#RewriteRule .* https://www.%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

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

# Set environment development variables
# SetEnv DEVELOPMENT true
# SetEnv SHOW_ERRORS true

EOF

  # index.php file
  cat <<EOF > "${folder}/${name}/index.php"
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
  \$loader = include 'vendor/autoload.php';
  
  // You may want to implement your own libraries
  // \$loader->add('System', 'library/.');
} else {
  exit("Project dependencies not found, run 'php composer.phar install'");
}

// Let's start SuitUp Framework
\$mvc = new SuitUpStart('modules/');

// Sql monitoring
\$mvc->setSqlMonitor(DEVELOPMENT);

\$mvc->run();

EOF

  # Personal functions file
  cat <<EOF > "${folder}/${name}/functions.php"
<?php

// Put your personal functions here

EOF

  # Gitignore
  cat <<EOF > "${folder}/${name}/.gitignore"
vendor

EOF

  # Gitignore var/log
  cat <<EOF > "${folder}/${name}/var/log/.gitignore"
*

EOF

  # database.config.php
  cat <<EOF > "${folder}/${name}/config/database.config.php"
<?php
return array(
  'host' => 'localhost',
  'database' => 'mysql',
  'username' => 'root',
  'password' => '',
);

EOF

  # Create the new module
  createNewModule "Default" "${folder}/${name}/modules" "-f"

  # Folder owner
  chown "${user}:${user}" "${folder}/${name}" -R

  _echo "${g}Wow! That was great! The project was entire created successfuly!${d}"

}

# Create the database Business and Gateway files
function createDbTable() {

  local dbname="$(capitalize $1)"   # Name of table from database
  local module="$3"                 # The module name
  local path="$4"                   # Module path

  local pks="$2"
  
  checkModulesPath "${path}"

  # Create path if doesnt exists
  if ! [ -d "${path}/${module}/Model/Gateway" ]; then
    mkdir -p "${path}/${module}/Model/Gateway"
  fi

  # Business file
  cat <<EOF > "${path}/${module}/Model/${dbname}Business.php"
<?php
namespace ${module}\Model

use \SuitUp\Database\Business\AbstractBusiness;

class ${dbname}Business extends AbstractBusiness
{
  /**
   * Reference to gateway file
   * @var Gateway\\${dbname}
   */
  protected \$gateway;
}

EOF

  # Gateway file
  cat <<EOF > "${path}/${module}/Model/Gateway/${dbname}.php"
<?php
namespace ${module}\Model\Gateway

use \SuitUp\Database\Gateway\AbstractGateway;

class ${dbname} extends AbstractGateway {

  /**
   * Required. Table name and pk's list
   */
  protected \$name = '${1}';
  protected \$primary = array(${pks});

  /**
   * Optional
   * You can define here a column from your table
   * that must to be updated with current timestamp
   * every UPDATE call
   */
  // protected \$onUpdate = array('edit' => 'NOW()');


}

EOF

  _echo "${g}I bet you will mess around with all this data, baby!${d}"

}

###################################################################
#                  End Functions Declarations                     #
###################################################################

# Here we will discover where is the modules path
modulesPath=null
for dir in $(find "${folder}" -mindepth 1 -maxdepth 1 -type d) ; do
  if [ -d "${dir}/ModuleDefault" ]; then
    modulesPath=$dir
    _echo -a "Modules Path: ${p}${modulesPath}${d}"
    break
  fi
done

# What do you wanna do?
action=$1

while true; do
  if [ "${action,,}" != "install" ] && [ "${action,,}" != "create" ]; then
    _echo "         Welcome! What do you wanna do?\n"\
          "         ${b}'install'${d} - A fresh new project"\
          "         ${b}'create'${d} - Modules, controllers..."
    
    #user input
    read -r action
  else
    break
  fi
done

if [ "${action,,}" = "install" ]
then

  # Log action to screen
  _echo -a "Action: ${p}'${action,,}'${d} - It will create a new project"

  ######################
  # CREATE NEW PROJECT #
  ######################

  # Get the second param or request it from user
  name=""

  # Check if the second param is the folder, so name will be the third
  if [ -d "$2" ]; then
    cd "$2" || (echo -e "Error to find folder ${folder}"; exit 1;)
    folder="$(pwd)"
    name=$3
  fi

  # while no name was
  while [ "$name" = "" ]; do
    _echo "Which is the ${bold}name${dbold} for your new project?"
    read -r name
  done

  # Prevent wrong type
  name="${name,,}"

  # Log action to screen
  _echo -a "Name: ${p}'${name,,}'${d}"

  # Ask if is this really what he wanna do
  _echo "Create a new project named ${b}'${name}'${d} (y/N)"
  read -r allow

  # User is pretty sure to append this new module...
  if [ "${allow,,}" = "y" ] || [ "${allow,,}" = "yes" ]
  then
    
    # The function that will create the module
    createProject "${name}" "${folder}"
    exit 0

  else
    _echo "${r}Answer: '${allow}'. Ok, nothing was changed, bye${d}"
    exit 0
  fi
fi

# Ok, let's create something...
if [ "${action,,}" = "create" ]
then
  
  # Log action to screen
  _echo -a "Action: ${p}'${action,,}'${d}"

  # Wait till user input some expected value
  createwhat=$2
  while true; do
    if [ "${createwhat,,}" != "module" ] && [ "${createwhat,,}" != "controller" ] && [ "${createwhat,,}" != "form" ] && [ "${createwhat,,}" != "dbtable" ]; then
      _echo "         What do you wanna ${bold}create${dbold}? \n"\
      "         ${b}'module'${d}      - It will create all module needle structure"\
      "         ${b}'controller'${d}  - An empty controller"\
      "         ${b}'form'${d}        - Helps you to create form validations"\
      "         ${b}'dbtable'${d}     - It mean Business and Gateway files"
    else
      break
    fi

    # User input
    read -r createwhat
  done

  # Log action to screen
  _echo -a "Create: ${p}'${createwhat,,}'${d}"

  # create module <name>
  if [ "${createwhat,,}" = "module" ]
  then

    #####################
    # CREATE NEW MODULE #
    #####################

    name=""

    # Check if the second param is the folder, so name will be the third
    if [ -d "$3" ]; then

      # Cd to the new path and redefine folder variable
      cd "$3" || (echo -e "Error to find folder ${folder}"; exit 1;)
      folder="$(pwd)"

      # Redefine Modules path
      for dir in $(find "${folder}" -mindepth 1 -maxdepth 1 -type d) ; do
        if [ -d "${dir}/ModuleDefault" ]; then
          modulesPath="$dir"
          break
        fi
      done

      # Name must be the fourth param
      name="$4"
    fi

    # Get the third param or request it from user
    while true; do
      # Prevent wrong type
      name=$(capitalize "${name}")

      if [ "${name}" = "" ]; then
        _echo "Which is the ${bold}name${dbold} of the new module?"
        read -r name

      # Module folder already exists
      elif [ -d "${modulesPath}/Module${name}" ]; then
        _echo "The module ${r}'Module${name}'${d} already exists"\
              "Which is the ${bold}unique${dbold} name of the new module?"
        read -r name
      else
        break
      fi
    done

    # Log action to screen
    _echo -a "Name: ${p}'Module${name}'${d}"

    # Ask if is this really what he wanna do
    _echo "Create a new module named ${b}'Module${name}'${d} (y/N)"
    read -r allow

    # User is pretty sure to append this new module...
    if [ "${allow,,}" = "y" ] || [ "${allow,,}" = "yes" ]
    then
      
      # The function that will create the module
      createNewModule "${name}" "${modulesPath}"
      exit 0

    else
      _echo -t "${r}Answer: '${allow}'. Ok, nothing was changed, bye${d}"
      exit 0
    fi

  # create controller <module> <name>
  elif [ "${createwhat,,}" = "controller" ]; then
  
    #########################
    # CREATE NEW CONTROLLER #
    #########################

    module="$3"
    name="$4"

    # Check if the third param is the folder, so module will be the third
    if [ -d "$3" ]; then

      # Cd to the new path and redefine folder variable
      cd "$3" || (echo -e "Error to find folder ${folder}"; exit 1;)
      folder="$(pwd)"

      # Redefine Modules path
      for dir in $(find "${folder}" -mindepth 1 -maxdepth 1 -type d) ; do
        if [ -d "${dir}/ModuleDefault" ]; then
          modulesPath="$dir"
          break
        fi
      done

      # Name must be the fourth param
      module="$4"
      name="$5"
    fi

    # Get the fourth param or request it from user
    moduleNotFound=""

    while true; do
      if [ "${module}" = "" ]; then
        if [ "${moduleNotFound}" != "" ]; then
          _echo "It's embarrassing, but seems ${r}'${moduleNotFound}'${d} folder does not exists\n"\
                "Let's try again..."\
                "Which is the ${bold}name${dbold} of the module where you wanna do it?" "\nOptions:\n${b}$(ls "${modulesPath}")${d}"
        else
          _echo "Which is the ${bold}name${dbold} of the module where you wanna do it?" "\nOptions:\n${b}$(ls "${modulesPath}")${d}"
        fi
        read -r module
      fi

      # Prevent wrong type
      module="${module,,}"
      module="${module/#"module"}"
      module="Module""$(capitalize "${module}")"

      if [ ! -d "${modulesPath}/${module}" ]
      then
        moduleNotFound="${module}"
        module=""
      else
        break
      fi
    done

    # Log action to screen
    _echo -a "Module: ${p}'${module}'${d}"

    # Get the third param or request it from user
    while true; do
      if [ "${name}" = "" ]; then
        _echo "Which is the ${bold}name${dbold} of the new controller?"
        read -r name
      else
        break
      fi
    done

    # Prevent wrong type
    name=$(capitalize "${name}")

    # Log action to screen
    _echo -a "Name: ${p}'${name}Controller'${d}"

    # Ask if is this really what he wanna do
    _echo "Create a new controller named ${b}'${name}Controller'${d} in the module ${b}'${module}'${d} (y/N)"
    read -r allow

    # User is pretty sure to append this new module...
    if [ "${allow,,}" = "y" ] || [ "${allow,,}" = "yes" ]
    then
      
      # The function that will create the module
      createController "${name}" "${module}" "${modulesPath}"
      exit 0

    else
      _echo -t "${r}Answer: '${allow}'. Ok, nothing was changed, bye${d}"
      exit 0
    fi

  elif [ "${createwhat,,}" = "form" ]
  then
    
    ###################
    # CREATE NEW FORM #
    ###################

    module="$3"
    name="$4"

    # Check if the third param is the folder, so module will be the third
    if [ -d "$3" ]; then

      # Cd to the new path and redefine folder variable
      cd "$3" || (echo -e "Error to find folder ${folder}"; exit 1;)
      folder="$(pwd)"

      # Redefine Modules path
      for dir in $(find "${folder}" -mindepth 1 -maxdepth 1 -type d) ; do
        if [ -d "${dir}/ModuleDefault" ]; then
          modulesPath="$dir"
          break
        fi
      done

      # Name must be the fourth param
      module="$4"
      name="$5"
    fi

    # Get the fourth param or request it from user
    moduleNotFound=""

    while true
    do

      if [ "${module}" = "" ]; then

        if [ "${moduleNotFound}" != "" ]; then
          _echo "It's embarrassing, but seems ${r}'${moduleNotFound}'${d} folder does not exists\n"\
                "Let's try again..."\
                "Which is the name of the module where you wanna do it?" "\nOptions:\n${b}$(ls "${modulesPath}")${d}"
        else
          _echo "Which is the name of the module where you wanna do it?" "\nOptions:\n${b}$(ls "${modulesPath}")${d}"
        fi
        read -r module
      fi

      # Prevent wrong type
      module="${module,,}"
      module="${module/#"module"}"
      module="Module""$(capitalize "${module}")"

      if [ ! -d "${modulesPath}/${module}" ]; then
        moduleNotFound="${module}"
        module=""
      else
        break
      fi

    done

    # Log action to screen
    _echo -a "Module: ${p}'${module}'${d}"

    # Get the third param or request it from user
    while true; do
      if [ "$name" = "" ]; then
        _echo "Which is the ${bold}name${dbold} of the new form?"\
              "What about something like ${b}'folder/filename'${d}?"
        read -r name
      else
        break
      fi
    done
    name=$(capitalize "$name")

    # It's recommended to create forms with a sub folder like "Auth/Login"
    readarray -d / -t partsFormName <<< "${name}"
    if [ ${#partsFormName[@]} -eq 2 ]; then
      name=$(capitalize "${partsFormName[0]}")"/"$(capitalize "${partsFormName[1]}")
    fi

    # Log action to screen
    _echo -a "Name: ${p}'${name}'${d}"

    # Ask if is this really what he wanna do
    _echo "Create a new form named ${b}'${name}'${d} in the module ${b}'${module}'${d} (y/N)"
    read -r allow

    # User is pretty sure to append this new module...
    if [ "${allow,,}" = "y" ] || [ "${allow,,}" = "yes" ]
    then
      
      # The function that will create the module
      createForm "$name" "$module" "$modulesPath"
      exit 0

    else
      _echo -t "${r}Answer: '$allow'. Ok, nothing was changed, bye${d}"
      exit 0
    fi

  elif [ "${createwhat,,}" = "dbtable" ]; then

    ###################
    # CREATE DBTABLE  #
    ###################

    module="$3"
    dbname="$4"

    # Check if the third param is the folder, so module will be the third
    if [ -d "$3" ]; then

      # Cd to the new path and redefine folder variable
      cd "$3" || (echo -e "Error to find folder ${folder}"; exit 1;)
      folder="$(pwd)"

      # Redefine Modules path
      for dir in $(find "${folder}" -mindepth 1 -maxdepth 1 -type d) ; do
        if [ -d "${dir}/ModuleDefault" ]; then
          modulesPath="$dir"
          break
        fi
      done

      # Name must be the fourth param
      module="$4"
      dbname="$5"
    fi

    # Get the fourth param or request it from user
    moduleNotFound=""

    while true
    do

      if [ "${module}" = "" ]; then
        if [ "${moduleNotFound}" != "" ]; then
          _echo "It's embarrassing, but seems ${r}'${moduleNotFound}'${d} folder does not exists\n"\
                "Let's try again..."\
                "Type is the ${bold}name${dbold} of the module where you wanna do it?" "\nOptions:\n${b}$(ls "${modulesPath}")${d}"
        else
          _echo "Type is the ${bold}name${dbold} of the module where you wanna do it?" "\nOptions:\n${b}$(ls "${modulesPath}")${d}"
        fi
        read -r module
      fi

      # Prevent wrong type
      module="${module,,}"
      module="${module/#"module"}"
      module="Module""$(capitalize "${module}")"

      if [ ! -d "${modulesPath}/${module}" ]
      then
        moduleNotFound="${module}"
        module=""
      else
        break
      fi

    done

    # Log action to screen
    _echo -a "Module: ${p}'${module}'${d}"

    # Get the third param or request it from user
    while true; do
      if [ "${dbname}" = "" ]; then
        _echo "Which is the ${bold}database${dbold} name?"
        read -r dbname
      else
        break
      fi
    done

    # Prevent wrong type
    dbname="${dbname,,}"

    # Log action to screen
    _echo -a "Database: ${p}'${dbname}'${d}"

    _echo "Type one by one the ${bold}primary keys${dbold} on this table"\
          "${y}type '' or 'done' when finish${d}"

    # Get the fourth param or request it from user
    declare -a pks=()

    while true; do
      read -r pk
      if [ "${pk}" = "" ] || [ "${pk}" = "." ] || [ "${pk}" = "done" ] || [ "${pk}" = "ok" ]; then
        break
      else
        pks+=("${pk,,}")
      fi
    done

    # Log pks on the screen
    joinBy pksEcho "', '" "${pks[@]}"
    _echo -a "pks: ${p}'${pksEcho}'${d}"

    # Ask if is this really what he wanna do
    _echo "Confirm create ${b}'${dbname}'${d} ${bold}Business${dbold} and ${bold}Gateway${dbold} on module ${b}'${module}'${d} (y/N)"
    read -r allow

    # User is pretty sure to append this new module...
    if [ "${allow,,}" = "y" ] || [ "${allow,,}" = "yes" ]
    then
      
      # The function that will create files
      createDbTable "${dbname}" "'${pksEcho}'" "${module}" "${modulesPath}"
      exit 0

    else
      _echo -t "${r}Answer: '${allow}'. Ok, nothing was changed, bye${d}"
      exit 0
    fi

  fi # End what to do with action
fi
