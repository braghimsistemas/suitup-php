#!/bin/bash


# If you want to enable the completion for all users, you can just
# copy the script under /etc/bash_completion.d/ and it will automatically
# be loaded by Bash.

function _autocomplete() {

  instal="install"
  create="create"
  module="module"
  controller="controller"
  form="form"
  dbtable="dbtable"

  typing="${COMP_WORDS[COMP_CWORD]}"
  lastWord="${COMP_WORDS[${COMP_CWORD} -1]}"

  # Simple auto completion
  local suggestion=()
  
  # If theres nothing but script name
  if [ "$lastWord" = "$1" ] ; then
    suggestion=($(compgen -W "$instal $create" "$typing"))
    
  # Install suggest folder
  elif [ "$lastWord" = "$instal" ] || [ "$lastWord" = "$module" ] ; then

    #compopt -o dirnames
    compopt -o plusdirs

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

    if [ "$modules" = "" ]; then
      compopt -o plusdirs
    else
      suggestion=($(compgen -W "$modules" "$typing"))
    fi

  # If last argument is a folder, the next maybe the module name
  elif [ -d "$lastWord" ]; then
    
    modules=""
    for dir in $(find "$lastWord" -mindepth 1 -maxdepth 1 -type d) ; do
      if [ -d "${dir}/ModuleDefault" ]; then
        modules="$(ls ${dir})"
        break
      fi
    done

    if [ "$modules" != "" ]; then
      suggestion=($(compgen -W "$modules" "$typing"))
    fi

  fi

  # For special options which starts with --
  if [[ ${typing} == -* ]] ; then
    COMPREPLY=($(compgen -W "--help" -- ${typing}))
    return 0
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

