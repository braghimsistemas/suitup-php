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

# Colors
r='\e[31m' # Red
g='\e[32m' # Green
y='\e[33m' # Yellow
b='\e[34m' # Blue
p='\e[35m' # Purple
d='\e[39m' # Default color

version="1.0.0"
echo -e $y"\nSuitUp Manager - Version: $version$d\n"$d

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
  cat "$DIR""/help.md"
  exit 0;
fi

# Avoid root user
user=$(whoami);
if [ $user = "root" ]; then
  
  if [ "$SUDO_USER" != "" ]; then

    echo -e $d"You are calling this program with sudo, it's not necessary."
    echo -e "Should we set "$b"'$SUDO_USER'"$d" instead? (Y/n)"
    read notSudo

    # Default is YES
    if [ "$notSudo" = "" ] || [ "${notSudo,,}" = "y" ] || [ "${notSudo,,}" = "yes" ]; then
      user=$SUDO_USER
    fi
  fi

  echo -e $g"Running program under user '$user'"$d
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
  if [ ! -d $1 ]
  then
    echo -e $r"There's no modules folder here. Is this the root of the project?"$d
    exit 1
  fi
}

# This function create a new module
function createNewModule() {

  local path=$2              # Path to the modules
  local name="Module""$1"    # Name of the new module

  checkModulesPath "$path"

  # Validate module creation
  if [ -d "$path""/""$name" ]; then
    echo -e "The module named '"$r"$name'"$d" already exists"
    exit 1
  fi

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
    // Here is where the legends begins...
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
cat <<EOF > "$path""/""$name""/views/index/index.phtml"
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

  chown $user:$user "$path""/""$name" -R

  echo -e $g"Done. Enjoy your new module =)"$d
}

# This function will generate files and folders to a new controller (with view)
function createController() {

  local name="$1""Controller"       # Name of the new controller
  local viewName="${1,,}"           # The view name (lowercase)
  local module=$2                   # Name of the module (Namespace)
  local path=$3                     # Path to the modules

  checkModulesPath "$path"

  # Create folders if does not exist
  mkdir -p "$path""/""$module""/Controllers/"
  mkdir -p "$path""/""$module""/views/""$viewName""/"

  # Check if controller already exists
  if [ -f "$path""/""$module""/Controllers/$name.php" ]; then
    echo -e "It's embarrassing but '"$r"$path""/""$module""/Controllers/$name.php"$d"' file already exists."
    echo -e $r"Aborting..."$d
    exit 1
  fi

  # If exists the AbstractController file to this specific module as recommended
  use="\nuse SuitUp\Mvc\MvcAbstractController;"
  extends="MvcAbstractController"
  if [ -f "$path""/""$module""/Controllers/AbstractController.php" ]; then
    use=""
    extends="AbstractController"
  fi

# The controller
cat <<EOF > "$path""/""$module""/Controllers/$name.php"
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
cat <<EOF > "$path""/""$module""/views/""$viewName""/index.phtml"
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
  chown $user:$user "$path""/""$module""/Controllers/$name.php"
  chown $user:$user "$path""/""$module""/views/""$viewName""/index.phtml"

  echo -e $g"Done. We also created the view file for you"$d

}

# Create a form file for a module
function createForm() {

  local name=$1                     # Name of the new form
  local module=$2                   # Name of the module (Namespace)
  local path=$3                     # Path to the modules

  checkModulesPath "$path"

  # Split into array
  readarray -d / -t parts <<< "$name"

  folder="$path""/""$module""/Form/"
  namespace="$module""\\Form"
  if [ ${#parts[@]} -eq 2 ]; then
    
    # Redefine variables
    name=$(trim "${parts[1]}")
    folder="$path""/""$module""/Form/""${parts[0]}"
    namespace="$module""\\Form\\""${parts[0]}"
  fi

  # Validate file exists
  if [ -f "$folder""/""$name"".php" ]; then
    echo -e "The file '"$r"$folder""/""$name"".php"$d"' already exists, nothing changed"
    exit 1
  fi

  # Create if folder doesnt exists
  if [ ! -d "$folder" ]; then
    mkdir -p "$folder"
  fi

# Create the file itself
cat <<EOF > "$folder""/""$name"".php"
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

  chown $user:$user "$folder""/""$name"".php"

  echo -e $b"Done. Yeah, it was legen... { wait for it } ...dary!"$d

}

# Start a new fresh project (skeleton)
function createProject() {

  local name=$1      # The simple name of the new project
  local folder=$2

  echo -e "Eita - $name - $folder"

}

###################################################################
#                  End Functions Declarations                     #
###################################################################

# Current folder where USER is
folder=$(pwd)

# Here we will discover where is the modules path
modulesPath=null
for dir in $(find $folder -mindepth 1 -maxdepth 1 -type d) ; do
  if [ -d $dir/ModuleDefault ]
  then
    modulesPath=$dir
  fi
done

# What do you wanna do?
action=$1

if [ "$action" = "" ]
then
  echo -e "We can't understand what do you wanna do... (no param 1)"
  exit 0
fi

# Ok, let's create something...
if [ "$action" = "create" ]
then
  
  if [ "$2" = "project" ]
  then

    ######################
    # CREATE NEW PROJECT #
    ######################

    # Get the third param or request it from user
    name=$3
    if [ "$name" = "" ]
    then
      echo -e "Which is the name of the "$g"folder"$d" to the new project?"
      read name
    fi

    # Prevent wrong type
    name=${name,,}

    # Ask if is this really what he wanna do
    echo -e "Create a new project named '"$g"$name"$d"' (y/N)"
    read allow

    # User is pretty sure to append this new module...
    if [ "$allow" = "y" ] || [ "$allow" = "Y" ] || [ "$allow" = "yes" ] || [ "$allow" = "Yes" ]
    then
      
      # The function that will create the module
      createProject $name $folder
      exit 0

    else
      echo -e $r"Answer: '$allow'. Ok, nothing was changed, bye"$d
      exit 0
    fi

  # create module <name>
  elif [ "$2" = "module" ]
  then

    #####################
    # CREATE NEW MODULE #
    #####################

    # Get the third param or request it from user
    name=$3
    if [ "$name" = "" ]
    then
      echo -e "Which is the name of the new module?"
      read name
    fi

    # Prevent wrong type
    name=$(capitalize "$name")

    # Ask if is this really what he wanna do
    echo -e "Create a new module named '"$g"Module""$name"$d"' (y/N)"
    read allow

    # User is pretty sure to append this new module...
    if [ "$allow" = "y" ] || [ "$allow" = "Y" ] || [ "$allow" = "yes" ] || [ "$allow" = "Yes" ]
    then
      
      # The function that will create the module
      createNewModule $name $modulesPath
      exit 0

    else
      echo -e $r"Answer: '$allow'. Ok, nothing was changed, bye"$d
      exit 0
    fi

  # create controller <module> <name>
  elif [ "$2" = "controller" ]
  then
  
    #########################
    # CREATE NEW CONTROLLER #
    #########################

    # Get the third param or request it from user
    name=$3
    if [ "$name" = "" ]
    then
      echo -e "Which is the name of the new controller?"
      read name
    fi

    # Prevent wrong type
    name=$(capitalize "$name")

    # Get the fourth param or request it from user
    module=$4

    while true
    do

      if [ "$module" = "" ]
      then
        echo -e "Which is the name of the module where you wanna do it?"
        read module
      fi

      # Prevent wrong type
      module="Module"$(capitalize "$module")

      if [ ! -d "$modulesPath""/""$module" ]
      then
        echo -e "It's embarrassing, but seems '"$r"$module"$d"' folder does not exists, let's try again..."
        module=""
      else
        break
      fi

    done

    # Ask if is this really what he wanna do
    echo -e "Create a new controller named '"$g"$name""Controller'"$d" in the module '"$g"$module"$d"' (y/N)"
    read allow

    # User is pretty sure to append this new module...
    if [ "$allow" = "y" ] || [ "$allow" = "Y" ] || [ "$allow" = "yes" ] || [ "$allow" = "Yes" ]
    then
      
      # The function that will create the module
      createController $name $module $modulesPath
      exit 0

    else
      echo -e $r"Answer: '$allow'. Ok, nothing was changed, bye"$d
      exit 0
    fi

  elif [ "$2" = "form" ]
  then
    
    ###################
    # CREATE NEW FORM #
    ###################

    # Get the third param or request it from user
    name=$3
    if [ "$name" = "" ]
    then
      echo -e "Which is the name of the new form? We recommend you something like 'folder/filename'"
      read name
    fi
    name=$(capitalize "$name")

    # It's recommended to create forms with a sub folder like "Auth/Login"
    readarray -d / -t partsFormName <<< "$name"
    if [ ${#partsFormName[@]} -eq 2 ]; then
      name=$(capitalize ${partsFormName[0]})"/"$(capitalize ${partsFormName[1]})
    fi

    # Get the fourth param or request it from user
    module=$4

    while true
    do

      if [ "$module" = "" ]
      then
        echo -e "Which is the name of the module where you wanna do it?"
        read module
      fi

      # Prevent wrong type
      module="Module"$(capitalize "$module")

      if [ ! -d "$modulesPath""/""$module" ]
      then
        echo -e "It's embarrassing, but seems '"$r"$module"$d"' folder does not exists, let's try again..."
        module=""
      else
        break
      fi

    done

    # Ask if is this really what he wanna do
    echo -e "Create a new form named '"$g"$name"$d"' in the module '"$g"$module"$d"' (y/N)"
    read allow

    # User is pretty sure to append this new module...
    if [ "$allow" = "y" ] || [ "$allow" = "Y" ] || [ "$allow" = "yes" ] || [ "$allow" = "Yes" ]
    then
      
      # The function that will create the module
      createForm $name $module $modulesPath
      exit 0

    else
      echo -e $r"Answer: '$allow'. Ok, nothing was changed, bye"$d
      exit 0
    fi

  fi

  elif [ "$2" = "dbtable" ]
  then

    echo -e "We will create here a new Business and a new Gateway"

  elif [ "$2" = "" ]
  then
    
    echo -e $r"We can't understand what do you wanna do... (no param 2)"$d
    exit 0

  fi # End what to do with action

fi # End action

