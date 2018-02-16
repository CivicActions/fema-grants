#!/bin/bash -e
set -e

# Set the apache user and group to match the host user.
# Optionally use the HOST_USER env var if provided.
if [ "$HOST_USER" ]; then
  OWNER=$(echo $HOST_USER | cut -d: -f1)
  GROUP=$(echo $HOST_USER | cut -d: -f2)
else
  OWNER=$(stat -c '%u' /app/src)
  GROUP=$(stat -c '%g' /app/src)
fi
if [ "$OWNER" != "0" ]; then
  usermod -o -u $OWNER www-data
  groupmod -o -g $GROUP www-data
fi
usermod -s /bin/bash www-data
usermod -d /app/src www-data

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
  chown -R www-data:www-data /app/src
  su www-data -c'bash /app/src/.docker/drupal-install.sh'
fi

exec /usr/local/bin/docker-php-entrypoint apache2-foreground
