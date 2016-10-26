

Crawl a site with something like:

```
php src/cli.php site:crawl phpimagick.com
```

Once that is done, then test the migration with:

```
php -d memory_limit=1280M src/cli.php site:migratecheck www.phpimagick.com phpimagick.com
```