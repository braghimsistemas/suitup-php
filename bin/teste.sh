#!/bin/bash
show_menu(){
  clear
  NORMAL=`echo "\033[m"`
  MENU=`echo "\033[36m"` #Blue
  NUMBER=`echo "\033[33m"` #yellow
  FGRED=`echo "\033[41m"`
  RED_TEXT=`echo "\033[31m"`
  ENTER_LINE=`echo "\033[33m"`

  echo -e ""
  echo -e "${ENTER_LINE}SuitUp Manager - Version 1.0.1"
  echo -e ""
  echo -e "${MENU}*********************************************${NORMAL}"
  echo -e "${MENU}**${NUMBER} 1 [ ]${MENU} Install a new project ${NORMAL}"
  echo -e "${MENU}**${NUMBER} 2 [ ]${MENU} Create module ${NORMAL}"
  echo -e "${MENU}**${NUMBER} 3 [ ]${MENU} Create controller ${NORMAL}"
  echo -e "${MENU}**${NUMBER} 4 [ ]${MENU} Add a Business and Gateway ${NORMAL}"
  echo -e "${MENU}**${NUMBER} 5 [ ]${MENU} exit${NORMAL}"
  echo -e "${MENU}*********************************************${NORMAL}"
  echo -e "${ENTER_LINE}Please enter a menu option and enter to select. ${NORMAL}"
  #read opt
}
function option_picked() {
  COLOR='\033[01;31m' # bold red
  RESET='\033[00;00m' # normal white
  echo -e "${COLOR}${1}${RESET}"
}

x=6
y=8

function moveDown() {
  y=$(( $y + 1 ))
  if [ $y -gt 8 ]; then
    y=8
  fi
}

function moveUp() {
  y=$(( $y - 1 ))
  if [ $y -lt 4 ]; then
    y=4
  fi
}

show_menu
tput cup "$y" "$x"
printf %b "X"

while :
do

  read -s -n 1 key

  case "$key" in
    A)
      moveUp
      show_menu
      tput cup "$y" "$x"
      printf %b "X"
      ;;
    B)
      moveDown
      show_menu
      tput cup "$y" "$x"
      printf %b "X"
      ;;
    "")
      show_menu
      tput cup "$y" "$x"
      printf %b "X"
      tput cup 11 0
      echo "Choose: $(( $y - 3 ))"

      # exit
      if [ "$(( $y - 3 ))" -eq 5 ]; then
        clear
        echo "Bye, bye"
        exit 0
      fi
      ;;
    *)
    ;;
  esac
  
done
