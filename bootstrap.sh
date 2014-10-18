#!/usr/bin/env bash

# Install mysql noninteractively
export DEBIAN_FRONTEND noninteractive

apt-get update
apt-get -f install -y apache2 mysql-server php5 php5-mysql pwgen python-dev python-pip
pip install paramiko
rm -rf /var/www
ln -fs /vagrant /var/www
chown www-data: /var/www/controller.php
chmod +x /var/www/controller.php
chmod +x /var/www/pollers/*
