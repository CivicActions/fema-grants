# FEMA SF-425 Prototype

## Requirements

* Git - https://git-scm.com/downloads
* Docker - https://www.docker.com/community-edition
 * Docker Compose
 * Docker Machine (for deployment of DevOps pipeline and hosting environment)
* Git LFS (for Visual Regression Testing) - https://git-lfs.github.com/

## Sandbox Instructions

Run the following:

```
git clone https://github.com/CivicActions/fema-grants.git
cd fema-grants
docker-compose up -d
```

The sandbox takes a minute or two to spin up (depending on your machine). You can follow progress by running `docker-compose logs`.

Once started the site should now be accessible on http://localhost:8118 - default credentials are admin/civicactions.

To stop the sandbox run:

```
docker-compose stop
```

To use CLI tools (composer, drush, drupal) run:

```
. bin/activate
```

If the database is not a functional Drupal site (e.g. on initial install or if a configuration import fails) then Drupal will be installed automatically and the configuration (in config/sync) loaded automatically.

To recreate your sandbox from scratch, run:
```
docker-compose stop
docker-compose rm -f
docker-compose up -d
```

## Starting work

If you start work on a new change you will need to pull code from git and also sync your local database with the current configuration files. To do this:

```
git checkout master
git pull
drush -y config:import
```

You can verify that config is in sync by running `drush config:status`. If you have problems you can recreate your sandbox (see above) which will also import the latest config into a clean install.

Always create a new branch to work on each user story or change.

### After you've done your coding work

* Make sure you export any configuration changes you've made to ensure that what you've done on your machine can be replicated on other machines. Do this by running `drush -y config:export` and commit those results before you submit your pull-request.
* If you have made changes to sample content or entity structures you should also update the sample content by running `drush dcem fema_federal_financial_report` and committing those results. It is expected that some timestamps will be updated when doing this.
* If you have added sample content look up the entity uuids for your new content:  `drush sqlq 'SELECT nid, uuid FROM node;'` , then
*  Add the UUIDs associated with the new NIDs to `web/modules/custom/fema_federal_financial_report/fema_federal_financial_report.info.yml` 
* Run `drush dcem fema_federal_financial_report` and commit those results.
* Not sure if all your changes work? Test your work by recreating your sandbox with your current code (see above).

## Testing

To run unit tests:
```
phpunit
```

To run functional behavior driven (BDD) tests:
```
behat
```

### Visual Regression Testing

To start visual regression testing, install LFS from https://git-lfs.github.com/ and make sure you have LFS enabled for this repo:
```
git lfs install --local
```

To quickly run tests - note `.` on the end which will run all tests in the tests/visual directory:
```
gemini test .
```

To to use the Gemeni GUI to visually review test results, rerun failing tests to resolve issues and (if needed) approve changes to the baseline images:
```
gemini-gui
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
