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



## Configuration management workflow

After a `git pull` you need to ensure that your sandbox build is the same as the reference build before you start coding.

To do this, you need to make sure that you run  `drush cim` to import any configuration changes that have been committed to git before you make any changes to the code.

If you run into errors after running `drush cim` you may need to review the git commit history to identify where the error crept into the code base and revert the offending commit.

The following sequence of commands helps ensure that you have a clean reference build before you start coding:

```git pull
git pull
drush cim
docker-compose stop
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
