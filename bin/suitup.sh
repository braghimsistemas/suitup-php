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

folder=$(pwd)
action=$1
towhat=$2

echo $folder
echo $action
echo $towhat


