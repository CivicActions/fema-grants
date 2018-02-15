# FEMA SF-425 Prototype

## Sandbox Instructions

Run the following:

```
git clone https://github.com/CivicActions/fema-grants.git
cd fema-grants
docker-compose up -d
```

The site should now be accessible on http://localhost:8118 - default credentials are admin/civicactions.

There can be a small delay (a couple of minutes) after `docker-compose up -d` is finished before the site is available. During this time, don't run any drush commands.

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



#### What happens when the sandbox launches

The sandbox takes a while to spin up (depending on your machine). Apache needs to spin up, drush enabled, then a configuration synchronization step is run. You may need to wait a minute or two while these startup in the background before you can work with your sandbox.

## Configuration Management

Everytime you pull code from git, you need to ensure that your local database is synched with the current configuration files. The quick way to check this is to:

1. Stop your currently running sandbox
2. Remove the docker images for the sandbox
3. Pull your code
4. Rebuild the sandbox
5. Import the configuration files

```docker-config stop
docker-config stop
docker-config rm -f
git pull
docker-compose up -d
drush cim
```

Step 5 may be redundant, but that's ok.

#### After you've done your coding work

Make sure you export any configuration changes you've made to ensure that what you've done on your machine can be replicated on other machines.

To do this, once you've done your code work, and before you submit a pull-request, make sure you do `drush cex` and commit those results before you submit your pull-request.

Not sure if all your changes work? Test your work by recreating your sandbox with your current code:

```docker-config stop
docker-config stop
docker-config rm -f
docker-compose up -d
drush cim
```

#### Troubleshooting

1) If you run Step 5 and you get this warning:

`[warning] Another request may be synchronizing configuration already.`

You may have to wait until the docker deployment script stops the initial configuration synchronization before you can re-run Step 5.

2) If you get this error after running `drush cim`:

 ```Site UUID in source storage does not match the target storage. ```

Then it's likely that your local db conficts with a configuration value and you should start from scratch by:

Deleting all of your workspace files

Reinstalling your standbox, starting from `git clone...`



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