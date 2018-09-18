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

  echo $src

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

