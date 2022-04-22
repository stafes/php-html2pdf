# php-html2pdf

Html to PDF Generate Batch for PHP.

Requirements
------------

- PHP >=5.6.32
- Composer
- php-gd

Usage
------------

```{.bash}
$ ./bin/html2pdf.sh --url="{url}" --save-path="{local or s3 upload path}" [--option="{mpdf options}"] [--dry-run]

# --url: Contents source URL (http:// or s3://)
# --save-path: Save path (local or s3://)
# --option: mpdf Library Options
# --dry-run: will not real save
```

Installation
------------

```{.bash}
$ cp .env.example .env
$ composer install
```
