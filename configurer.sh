#!/usr/bin/env bash

function setupFolders(){
	if [ ! -d ./u.clients ]; then mkdir u.clients; fi
	if [ ! -d ./g.clients ]; then mkdir g.clients; fi
	if [ ! -d ./usignatures.d ]; then mkdir usignatures.d; fi
	if [ ! -d ./signatures.d ]; then mkdir signatures.d; fi
	if [ ! -d ./media ]; then mkdir media; fi
	if [ ! -d ./logs ]; then mkdir logs; touch logs/error.log logs/access.log logs/database.log logs/files.log logs/server.log; fi
	if [ ! -d ./u.images ] then mkdir u.images; fi
	# setup the permissions
	chmod 777 -R u.clients g.clients signatures.d usignatures.d media logs u.images

}

procMode=0
while :
do
    echo "Welcome to the LPGP official website server installer"
    echo "The following steps are important to the LPGP environment creation"
    echo -e "\033[9mWarning you must be a programmer or other someone with knowlogement about servers to proceed\033[0m"
    echo -e "Want to proceed? [Y/n]"; read proceed

    if [ $proceed == "n" ]; then
        echo "Ok exiting now"
        procMode=0
        break;
    else
        if [ $proceed == "Y" ]; then
            procMode=1
        else
            echo "I think you didn't understood my ways, you gave me a wrong answer."
            echo "Let's try again"
            sleep 2s
            continue
        fi
    fi

    echo "Well you are here because you're qualified to do the following steps"
    echo "Let's begin.\nWhat's the path to the SSL ceritificate file?: "; read lcrt
    echo "Great! Now the certificate key file?: "; read lkey
    sleep 1s
    echo "Where the server will run (document root)?: "; read lroot
    echo "So, who's the server manager?: "; read admin
    sleep 1s
    echo "Allright, now we may continue. Do you want me (script) to install the Apache configurations file"
    echo "Into your Apache sites folder (/etc/apache2/sites-available) and turn it to a site already?"
    echo -e "\033[9mWarning to do the following action you must be a root user\033[0m"
    echo "Do you want automaticaly add to your sites the LPGP server? [Y/n]: "; read auto

    if [ $auto == "Y" ]; then
        procMode=2
        break
    else
        if [ $auto == "n" ]; then
            procMode=1
            break
        else
            echo "Sorry i didn't understand the last command, please repate it all, your dumb"
            sleep 2s
            continue
        fi
    fi
done

if [ $procMode -eq 1 -o $procMode -eq 2 ]; then
    echo "Working..."
    echo  "
<VirtualHost *:443>
    ServerName www.lpgpoffical.com
    ServerAdmin $admin@localhost
    DocumentRoot $lroot
    ErrorLog $lroot/logs/error.log
    SSLEngine On
    SSLCertificateFile $lcrt
    SSLCertificateKeyFile $lkey

    <Directory $lroot>
        Options Indexes FollowSymLinks ExecCGI
        Options +ExecCGI
        SetHandler cgi-script
        AddHandler cgi-script .php
        Require all granted
        Order allow,deny
        Allow from all
        AllowOverride all
    </Directory>

    <Directory $lroot/core>
        Options Indexes FollowSymLinks ExecCGI
        Options +ExecCGI
        SetHandler cgi-script
        AddHandler cgi-script .php
        Require all granted
        Order allow,deny
        Allow from all
        AllowOverride all
    </Directory>

    <Directory $lroot/cgi-actions>
        Options Indexes FollowSymLinks ExecCGI
        Options +ExecCGI
        SetHandler cgi-script
        AddHandler cgi-script .php
        Require all granted
        Order allow,deny
        Allow from all
        AllowOverride all
    </Directory>
</VirtualHost>" | tee -a $lroot/config/apache.conf
    if [ $procMode -eq 2 ]; then

        echo  "
<VirtualHost *:443>
    ServerName www.lpgpoffical.com
    ServerAdmin $admin@localhost
    DocumentRoot $lroot
    ErrorLog $lroot/logs/error.log
    SSLEngine On
    SSLCertificateFile $lcrt
    SSLCertificateKeyFile $lkey

    <Directory $lroot>
        Options Indexes FollowSymLinks ExecCGI
        Options +ExecCGI
        SetHandler cgi-script
        AddHandler cgi-script .php
        Require all granted
        Order allow,deny
        Allow from all
        AllowOverride all
    </Directory>

    <Directory $lroot/core>
        Options Indexes FollowSymLinks ExecCGI
        Options +ExecCGI
        SetHandler cgi-script
        AddHandler cgi-script .php
        Require all granted
        Order allow,deny
        Allow from all
        AllowOverride all
    </Directory>

    <Directory $lroot/cgi-actions>
        Options Indexes FollowSymLinks ExecCGI
        Options +ExecCGI
        SetHandler cgi-script
        AddHandler cgi-script .php
        Require all granted
        Order allow,deny
        Allow from all
        AllowOverride all
    </Directory>
</VirtualHost>" | tee -a /etc/apache2/sites-available/lpgp.conf
        a2ensite lpgp.conf
        echo "127.0.0.1    www.lpgpofficial.com" | tee -a /etc/hosts
        service apache2 restart
    fi
else
    if [ $procMode -eq 0 ]; then
        echo "okay"
    else
        echo "Error"
        exit 124
    fi
fi

echo "Apache configuration done!"

sleep 2s

echo "Configuring logs files ... "
touch $lroot/logs/access.log $lroot/logs/database.log $lroot/logs/files.log $lroot/logs/server.log

echo "Logs configuration done ..."

echo "Do you want me to install the php dependencies? (php-mysqli; php-zip)"
echo -e "\033[41mIt will need root permissions\033[0m"
echo "Install dependencies? [Y/n]: "; read inst

if [ $inst == "Y" ]; then
    echo "Installing dependencies ... "
    sudo apt-get install -y php-mysqli php-zip
    echo "Dependencies Installed"
fi

echo "LPGP server environment installation done! Enjoy..."
