#!/bin/bash

function _autocomplete() {

  
  local suggestion=($(compgen -W "install create module controller form dbtable" "${COMP_WORDS[${COMP_CWORD}]}"))
  
  if [ "${#suggestion[@]}" == "1" ]; then
    COMPREPLY=("$(echo ${suggestion[0]/%\ */})")
  else
    COMPREPLY=("${suggestion[@]}")
  fi

}

complete -F _autocomplete suitup

