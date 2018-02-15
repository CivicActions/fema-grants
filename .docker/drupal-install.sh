#!/bin/bash -e
set -e
drush=/var/www/vendor/bin/drush

echo "Installing Drupal"
$drush -y site:install minimal --account-pass=civicactions --sites-subdir=default --db-url=mysql://dbuser:dbpass@db:3306/drupal --config-dir=/var/www/config/sync

echo "Importing Configuration"
$drush -y config-import

echo "Adding admin users/roles"
$drush user-create Mary.Analyst --mail=analyst@example.com --password=civicactions
$drush urol analyst Mary.Analyst
$drush user-create Jackson.Specialist --mail=jackson@example.com --password=civicactions
$drush user-create Celeste.Aspecialist --mail=celeste@example.com --password=civicactions
$drush urol specialist Jackson.Specialist,Celeste.Aspecialist

echo "Adding grantee users"
$drush user-create Marcos.Fletcher --mail Marcos.Fletcher@example.com --password=civicactions
$drush user-create Shannon.Blair --mail Shannon.Blair@example.com --password=civicactions
$drush user-create Amy.Fleming --mail Amy.Fleming@example.com --password=civicactions
$drush user-create Rose.Mack --mail Rose.Mack@example.com --password=civicactions
$drush user-create Colleen.Parsons --mail Colleen.Parsons@example.com --password=civicactions
$drush user-create Andrea.Wells --mail Andrea.Wells@example.com --password=civicactions
$drush user-create Cynthia.Tran --mail Cynthia.Tran@example.com --password=civicactions
$drush user-create Darnell.Wright --mail Darnell.Wright@example.com --password=civicactions
$drush user-create Pamela.Clarke --mail Pamela.Clarke@example.com --password=civicactions
$drush user-create Cameron.Denton --mail Cameron.Denton@example.com --password=civicactions
$drush user-create Mei.Lee --mail Mei.Lee@example.com --password=civicactions
$drush user-create Sage.Anthony --mail Sage.Anthony@example.com --password=civicactions
$drush user-create Jillian.Doll --mail Jillian.Doll@example.com --password=civicactions
$drush user-create Roxanna.Kozlowski --mail Roxanna.Kozlowski@example.com --password=civicactions
$drush user-create Providencia.Camp --mail Providencia.Camp@example.com --password=civicactions
$drush user-create Shan.Vanover --mail Shan.Vanover@example.com --password=civicactions
$drush user-create Tessie.Oswald --mail Tessie.Oswald@example.com --password=civicactions
$drush user-create Michel.Villanueva --mail Michel.Villanueva@example.com --password=civicactions
$drush user-create Annice.Shackelford --mail Annice.Shackelford@example.com --password=civicactions

echo "Adding grantee roles"
$drush urol grantee Marcos.Fletcher,Shannon.Blair,Amy.Fleming,Rose.Mack,Colleen.Parsons,Andrea.Wells,Cynthia.Tran,Darnell.Wright,Pamela.Clarke,Cameron.Denton,Mei.Lee,Sage.Anthony,Jillian.Doll,Roxanna.Kozlowski,Providencia.Camp,Shan.Vanover,Tessie.Oswald,Michel.Villanueva,Annice.Shackelford
