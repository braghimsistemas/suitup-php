#!/bin/sh
###################################################################
#Script Name	: suitup
#Description	: SuitUp Manager
#Args           : create module <name>, create controller <name>
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

# Current folder
folder=$(pwd)

modulesPath=null

# Here we will discover where is the modules path
echo "Looking for a modules path..."
for dir in $(find $folder -mindepth 1 -maxdepth 1 -type d) ; do
  if [ -d $dir/ModuleDefault ]
  then
    modulesPath=$dir/ModuleDefault
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

    name=$3

    echo "Create module $name (y/N)"
    allow=read

    if [ "$allow" = "n" ] || [ "$allow" = "N" ] || [ "$allow" = "" ]
    then
      echo "Ok, nothing was changed, bye"
      exit 0
    fi

  elif [ "$2" = "controller" ]
  then
  
    # create controller <module> <name>

    echo "oi"

  elif [ "$2" = "" ]
  then
    
    echo "We can't understand what do you wanna do... (no param 2)"
    exit 0

  fi

fi

