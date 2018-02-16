#!/bin/bash -e
set -e
drush=/var/www/vendor/bin/drush

echo "Installing Drupal"
$drush -y site:install minimal --account-pass=civicactions --sites-subdir=default --db-url=mysql://dbuser:dbpass@db:3306/drupal --config-dir=/var/www/config/sync

echo "Importing Configuration"
$drush -y config-import

echo "Adding admin roles"
$drush user:role:add specialist Jackson.Specialist,Celeste.Aspecialist

echo "Adding grantee roles"
$drush urol grantee Marcos.Fletcher,Shannon.Blair,Amy.Fleming,Rose.Mack,Colleen.Parsons,Andrea.Wells,Cynthia.Tran,Darnell.Wright,Pamela.Clarke,Cameron.Denton,Mei.Lee,Sage.Anthony,Jillian.Doll,Roxanna.Kozlowski,Providencia.Camp,Shan.Vanover,Tessie.Oswald,Michel.Villanueva,Annice.Shackelford

echo "Unblocking users"
$drush user:unblock Jackson.Specialist,Celeste.Aspecialist,Mary.Analyst,Marcos.Fletcher,Shannon.Blair,Amy.Fleming,Rose.Mack,Colleen.Parsons,Andrea.Wells,Cynthia.Tran,Darnell.Wright,Pamela.Clarke,Cameron.Denton,Mei.Lee,Sage.Anthony,Jillian.Doll,Roxanna.Kozlowski,Providencia.Camp,Shan.Vanover,Tessie.Oswald,Michel.Villanueva,Annice.Shackelford
