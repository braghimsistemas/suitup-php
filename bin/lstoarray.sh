#!/bin/bash


function lstoarray() {

  local folder="$1"

  echo -e "${folder}"
  echo -e ""

  if [ -d "${folder}" ]; then
    local list=()
    for dir in $(ls -a "${folder}"); do
      list+=("${dir}")
    done
    echo "${list[@]}"
  fi
}

if [ "${1}" = "" ]; then
  lstoarray "$(pwd)"
else
  lstoarray "$1"
fi

