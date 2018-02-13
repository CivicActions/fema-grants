#!/bin/bash -e
set -e

# Set the apache user and group to match the host user.
# Optionally use the HOST_USER env var if provided.
if [ "$HOST_USER" ]; then
  OWNER=$(echo $HOST_USER | cut -d: -f1)
  GROUP=$(echo $HOST_USER | cut -d: -f2)
else
  OWNER=$(stat -c '%u' /var/www)
  GROUP=$(stat -c '%g' /var/www)
fi
if [ "$OWNER" != "0" ]; then
  usermod -o -u $OWNER www-data
  groupmod -o -g $GROUP www-data
fi
usermod -s /bin/bash www-data
usermod -d /var/www www-data

echo "The apache user and group has been set to the following:"
id www-data

# Wait for mysql connection.
echo "NOTE: Please ignore any warnings about 'Connection refused' while waiting for the db service to start"
i=0
while ! bash -c "cat < /dev/null > /dev/tcp/db/3306"; do
  i=$(($i+1))
  if [ "$i" -gt 7 ]; then
    echo "Cannot connect to database. Make sure it is running and the web container is linked to it."
    exit 1;
  fi
  sleep 2;
done

if [[ "$(drush core-status --field=bootstrap | sed 's/[^a-zA-Z]*//g')" == "Successful" ]]; then
  echo "Drupal already installed"
else
	echo "Installing Drupal"
	su www-data -c'/var/www/vendor/bin/drush -y site:install minimal --account-pass=civicactions --sites-subdir=default --db-url=mysql://dbuser:dbpass@db:3306/drupal --config-dir=/var/www/config/sync'
fi

exec /usr/local/bin/docker-php-entrypoint apache2-foreground
