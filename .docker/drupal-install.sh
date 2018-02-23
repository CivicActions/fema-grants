#!/bin/bash -e
set -e
DRUSH=/app/src/vendor/bin/drush
DRUPAL=/app/src/vendor/bin/drupal

echo "Installing Drupal"
$DRUSH -y site:install minimal --account-pass=civicactions --sites-subdir=default --db-url=mysql://dbuser:dbpass@db:3306/drupal --config-dir=/app/src/config/sync

echo "Importing Configuration"
$DRUSH -y config-import

echo "Adding analyst roles"
$DRUSH user:role:add analyst Mary.Analyst

echo "Adding specialist roles"
$DRUSH user:role:add specialist Jackson.Specialist,Celeste.Aspecialist

echo "Adding grantee roles"
$DRUSH user:role:add grantee Marcos.Fletcher,Shannon.Blair,Amy.Fleming,Rose.Mack,Colleen.Parsons,Andrea.Wells,Cynthia.Tran,Darnell.Wright,Pamela.Clarke,Cameron.Denton,Mei.Lee,Sage.Anthony,Jillian.Doll,Roxanna.Kozlowski,Providencia.Camp,Shan.Vanover,Tessie.Oswald,Michel.Villanueva,Annice.Shackelford

echo "Unblocking and setting e-mail addresses for demo users"
$DRUSH sqlq "UPDATE users_field_data SET mail=CONCAT(name, '@example.com'), status=1 WHERE uid > 0"

echo "Setting passwords"
for NAME in $($DRUSH sqlq "SELECT name FROM users_field_data WHERE uid > 0"); do
  $DRUSH user:password "${NAME}" "civicactions"
done

echo "Rebuilding node access"
$DRUPAL --root=/app/src/docroot node:access:rebuild

echo "Rebuilding cache"
$DRUSH cache:rebuild
