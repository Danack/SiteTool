

# SiteTool

A couple of very simple tools for checking sites and site migration.


## Site crawler

Crawls a site to find all links, and then fetches them. Run with:

```
php src/cli.php site:crawl http://phpimagick.com/
```

Reults by default will be written to 'crawl_result.txt'.


## Migration checker

Once a site has been crawled, then you can check to see if the same paths are available on a different domain.

```
php -d memory_limit=1280M src/cli.php site:migratecheck phpimagick.com www.phpimagick.com 
```

This allows you to check that migrating to a new platform hasn't lost any paths.



## Visualizing events

As the whole application is tied together using events, it can be difficult to comprehend how the different parts of the app fit together.

Appending ```--graph``` to any of the commands will make the application generate a graph of how the events + processors are tied together for that command, rather than running the command.

The graph generation depends on having graphviz available. There is a docker composer file for this project to allow generating graphs inside that, which can be invoked with something like.

```
docker-compose up --build

docker exec sitetool_php_1 php cli.php site:crawl http://phpimagick.com/ --graph

```

If the project is not checked out to a directory named 'sitetool' you may need to run `docker ps` to find the exact docker image name.

