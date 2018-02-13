# FEMA SF-425 Prototype

## Sandbox Instructions

Run the following:
```
git clone https://github.com/CivicActions/fema-grants.git
cd fema-grants
docker-compose up -d
```

The site should now be accessible on http://localhost:8118 - default credentials are admin/civicactions.

To stop the sandbox run:
```
docker-compose stop
```

To use CLI tools (composer, drush, drupal) run:
```
. bin/activate
```

If the database is not a functional Drupal site (e.g. on initial install) then Drupal will be installed automatically and the configuration (in config/sync) loaded automatically. To re-initialize the site, run:
```
docker-compose rm -f
docker-compose up -d
```

## Docker image development

Edit the Dockerfile, then rebuild using:
```
docker build -t fema-grants .
```

To test the image locally, create a docker-compose.override.yml file using:
```
echo -e 'version: "3"\nservices:\n  web:\n    image: fema-grants' > docker-compose.override.yml
docker-compose rm -f
docker-compose up -d
```

To revert to the shared image delete the override file:
```
rm docker-compose.override.yml
```
