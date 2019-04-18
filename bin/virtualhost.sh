#!/bin/bash
###################################################################
#Script Name  : VirtualHost
#Description  : SuitUp VirtualHost
#Args         : create domain.lab /var/www/domain
#               remove domain.lab
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
echo -e "${y} \nSuitUp VirtualHost - Version: $version\n ${d}"

email='webmaster@host.com'

# Root user required
user=$(whoami);
if [ "$user" != "root" ]; then
  echo -e "This script requires sudo"
  exit 1
fi

# Action
action=$1
while true; do
  if [ "${action,,}" = "create" ] || [ "${action,,}" = "install" ] || [ "${action,,}" = "delete" ] || [ "${action,,}" = "remove" ]; then
    break
  else
    echo -e "You need to prompt for action (create or delete)"
    read action
  fi
done

# Which is the domain?
domain=${2,,}
if [ "${domain}" = "" ]; then
  echo -e "Fill the domain you wish to create ex.: mysite.lab"
  read domain
  domain="${domain,,}"
fi

owner=$(who am i | awk '{print $1}')
sitesEnable='/etc/apache2/sites-enabled/'
userDir='/var/www/'
sitesAvailabledomain="/etc/apache2/sites-available/${domain}.conf"

if [ "${action,,}" = "create" ] || [ "${action,,}" = "install" ]; then

  rootDir=$3
  folder=$(pwd)

  # Request for document root
  if [ "${rootDir}" = "" ]; then
    echo -e "We are on the '${folder}' directory, is this the DOCUMENT_ROOT to your new site? (Y/n)"
    read isDocRoot

    if [ "${isDocRoot,,}" = "n" ] || [ "${isDocRoot,,}" = "no" ]; then
      while true; do
        echo -e "Where is your new site?"
        read rootDir

        if [ ! -d "${rootDir}" ]; then
          echo -e "Error: '${rootDir}' doesnt exists, trying again.."
        else
          break
        fi
      done
    else
      rootDir="${folder}"
    fi
  fi # End get document root

  ### check if domain already exists
  if [ -e $sitesAvailabledomain ]; then
    echo -e $"This domain already exists.\nPlease Try Another one"
    exit;
  fi

  ### create virtual host rules file
  cat <<EOF > "${sitesAvailabledomain}"
<VirtualHost *:80>
  ServerAdmin $email

  ServerName $domain
  ServerAlias www.$domain

  DocumentRoot $rootDir
  <Directory $rootDir>
    Options Indexes FollowSymLinks
    AllowOverride all
    Order allow,deny
    Allow from all
  </Directory>

  LogLevel error  
  ErrorLog $rootDir/var/log/error.log
  CustomLog $rootDir/var/log/access.log combined
</VirtualHost>
#<VirtualHost *:443>
#  ServerAdmin $email
#
#  ServerName $domain
#  ServerAlias www.$domain
#
#  Redirect 301 / http://$domain
# 
#  DocumentRoot $rootDir
#  <Directory $rootDir>
#    Options Indexes FollowSymLinks
#    AllowOverride all
#    Order allow,deny
#    Allow from all
#  </Directory>
#  
#  LogLevel error
#  ErrorLog $rootDir/var/log/error.log
#  CustomLog $rootDir/var/log/access.log combined
#</VirtualHost>

EOF
  
  # Check if was created the file...
  if [ ! -f "${sitesAvailabledomain}" ]; then
    echo -e "Error: We could not create '${sitesAvailabledomain}'"
    exit 1
  fi

  ### Add domain in /etc/hosts
  if ! echo "127.0.0.1 $domain" >> /etc/hosts
  then
    echo -e "ERROR: Not able to write in /etc/hosts"
    exit 1
  else
    echo -e $"Host added to /etc/hosts file \n"
  fi

  ### Cria pasta de log
  mkdir -p "${rootDir}/var/log";
  chmod 777 "${rootDir}/var" -R;

  ### enable website
  a2ensite $domain

  ### restart Apache
  /etc/init.d/apache2 restart

  ### show the finished message
  echo -e $"Complete! \nYou now have a new Virtual Host \nYour new host is: http://$domain \nAnd its located at $rootDir"
  exit 0

elif [ "${action,,}" = "delete" ] || [ "${action,,}" = "remove" ]; then

  ### check whether domain already exists
  if ! [ -e $sitesAvailabledomain ]; then
    echo -e $"This domain does not exist.\nPlease try another one"
    exit;
  else
    ### Delete domain in /etc/hosts
    newhost=${domain//./\\.}
    sed -i "/$newhost/d" /etc/hosts

    ### disable website
    a2dissite $domain

    ### restart Apache
    /etc/init.d/apache2 restart

    ### Delete virtual host rules files
    rm $sitesAvailabledomain
  fi

  ### show the finished message
  echo -e $"Complete!\nYou just removed Virtual Host $domain"
  exit 0
fi
