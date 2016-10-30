

# SiteTool

A couple of very simple tools for checking sites and site migration.


## Site crawler

Crawls a site to find all links, and then fetches them. Run with:

```
php src/cli.php site:crawl phpimagick.com
```

Reults by default will be written to 'crawl_result.txt'.


## Migration checker

Once a site has been crawled, then you can check to see if the same paths are available on a different domain.

```
php -d memory_limit=1280M src/cli.php site:migratecheck phpimagick.com www.phpimagick.com 
```

This allows you to check that migrating to a new platform hasn't lost any paths.