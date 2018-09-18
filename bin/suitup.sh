#!/bin/bash
###################################################################
#Script Name	: suitup
#Description	: SuitUp Manager
#Args           : create module <name>,
#                 create controller <module> <name>
#                 create action <module> <controller> <name>
#Author       	: Braghim Sistemas
#Email         	: braghim.sistemas@gmail.com
###################################################################

version="1.0.0"
echo "SuitUp Manager - Version: $version"

# Show to the user the help document options
if [ "$1" = "--help" ] || [ "$1" = "-h" ]; then
  cat ./help.md
  exit 0;
fi

# Avoid root user
user=$(whoami);
if [ $user = "root"  ]; then
  echo "Do not run this script as root user";
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

function createNewModule() {

  local path=$2              # Path to the modules
  local name="Module""$1"    # Name of the new module
  local src=$3               # Realpath to this script (where we get the models...)

  # Create folders
  mkdir -p "$path""/""$name""/Controllers/"
  mkdir -p "$path""/""$name""/Form/"
  mkdir -p "$path""/""$name""/Model/Gateway"
  mkdir -p "$path""/""$name""/views/index/"

  # Write documents
  
# IndexController.php
cat <<EOF > "$path""/""$name""/Controllers/IndexController.php"
<?php
namespace $name\Controllers;

class IndexController extends AbstractController
{
  public function indexAction() {

  }
}

EOF

# AbstractController.php
cat <<EOF > "$path""/""$name""/Controllers/AbstractController.php"
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
cat <<EOF > "$path""/""$name""/views/layout.phtml"
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
    <h1>New Module: $name</h1>
    <h4>Created by SuitUp Manager Version: $version</h4>

    <?php echo \$content; ?>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
  </body>
</html>

EOF

# index/index.phtml
cat <<EOF > "$path""/""$name""/views/index/index.phtml"
<!-- The content created automatically -->
<div class="row">
  <div class="col-6 mx-auto">

    <div class="card">
      <img class="card-img-top" src=".../100px180/" alt="Card image cap">
      <div class="card-body">
        <h5 class="card-title">Card title</h5>
        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
        <a href="#" class="btn btn-primary">Go somewhere</a>
      </div>
    </div>

  </div>
</div>

EOF

  printf "# SuitUp Manager \n\nThat's pretty awesome\nThis module was created automatically" > "$path""/""$name""/readme.md"

  echo "Done. Enjoy your new module =)"
}

###################################################################
#                  End Functions Declarations                     #
###################################################################

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
# DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

# Current folder where USER is
folder=$(pwd)

# Here we will discover where is the modules path
echo "Looking for a modules path..."
modulesPath=null
for dir in $(find $folder -mindepth 1 -maxdepth 1 -type d) ; do
  if [ -d $dir/ModuleDefault ]
  then
    modulesPath=$dir
    echo "found!"
  fi
done

# Modules path found?
if [ $modulesPath = null ]
then
  echo "Unable to find one folder with modules, is this the root of the project?"
  echo "Can't proceed for while..."
  exit 1
fi

# What do you wanna do?
action=$1

if [ "$action" = "" ]
then
  echo "We can't understand what do you wanna do... (no param 1)"
  exit 0
fi

# Ok, let's create something...
if [ "$action" = "create" ]
then
  
  # create module <name>
  if [ "$2" = "module" ]
  then

    #####################
    # CREATE NEW MODULE #
    #####################

    # Get the third param or request it from user
    name=$3
    if [ "$name" = "" ]
    then
      echo "Which is the name of the new module?"
      read name
    fi

    # Prevent wrong type
    name=$(capitalize "$name")

    # Ask if is this really what he wanna do
    echo "Create a new module named 'Module""$name' (y/N)"
    read allow

    # User is pretty sure to append this new module...
    if [ "$allow" = "y" ] || [ "$allow" = "Y" ] || [ "$allow" = "yes" ] || [ "$allow" = "Yes" ]
    then
      
      # The function that will create the module
      createNewModule $name $modulesPath $(dirname $SOURCE)
      exit 0

    else
      echo "Answer: '$allow'. Ok, nothing was changed, bye"
      exit 0
    fi

  # create controller <module> <name>
  elif [ "$2" = "controller" ]
  then
  
    #########################
    # CREATE NEW CONTROLLER #
    #########################

    echo "oi"

  elif [ "$2" = "" ]
  then
    
    echo "We can't understand what do you wanna do... (no param 2)"
    exit 0

  fi # End what to do with action

fi # End action

