#!/bin/bash

function _autocomplete() {

  instal="install"
  create="create"
  module="module"
  controller="controller"
  form="form"
  dbtable="dbtable"

  typing="${COMP_WORDS[${COMP_CWORD}]}"
  lastWord="${COMP_WORDS[${COMP_CWORD} -1]}"

  # Simple auto completion
  local suggestion=($(compgen -W "$instal $create" "$typing"))
  
  # Install have no more params
  if [ "$lastWord" = "$instal" ] || [ "$lastWord" = "$module" ] ; then
    suggestion=()

  # List of what can be created
  elif [ "$lastWord" = "$create" ]; then
    suggestion=($(compgen -W "$module $controller $form $dbtable" "$typing"))

  # It will try to list modules on this folder
  elif [ "$lastWord" = "$controller" ] || [ "$lastWord" = "$form" ] || [ "$lastWord" = "$dbtable" ]; then

    modules=""
    for dir in $(find "$(pwd)" -mindepth 1 -maxdepth 1 -type d) ; do
      if [ -d "${dir}/ModuleDefault" ]; then
        modules="$(ls ${dir})"
        break
      fi
    done
    suggestion=($(compgen -W "$modules" "$typing"))

  fi

  # Append to the result
  if [ "${#suggestion[@]}" == "1" ]; then
    # Only one, append to the terminal
    COMPREPLY=("$(echo ${suggestion[0]/%\ */})")
  else
    # Append list
    COMPREPLY=("${suggestion[@]}")
  fi

}

complete -F _autocomplete suitup

