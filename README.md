# Shreeji Ratnam

Diamond inventory for Shreeji Ratnam. The public list, filters, prices, and Excel columns stay as they are. Import reads the workbook in small pieces so a large catalog does not exhaust memory.

## Requirements

- PHP 8.0 or newer, with `zip`, `xml`, and `mbstring`
- MySQL
- Composer
- Apache (WAMP) or `php artisan serve`

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

Set the database name, user, and password in `.env`.

## Opening the site

Both of these addresses are supported:

- Folder URL, for WAMP: `http://localhost/shreeji_ratnam/`
- PHP server: `php artisan serve`, then `http://127.0.0.1:8000/`

Styles and scripts are requested as `/public/css` and `/public/js`. That matches the folder URL, where the site root is the project directory. The root `.htaccess` also maps `/css`, `/js`, and `/image` into `public/`. When the document root is already the `public` directory, `public/.htaccess` rewrites `/public/...` back to the real file. `php artisan serve` does not read `.htaccess`; `server.php` serves those same `/public/...` files.

## Import

Sign in and open **Import**. Choosing an Excel file reads it in the browser, shows the first rows, then saves it on the server in batches of 250 with a progress bar. The catalog is replaced only after every batch succeeds. A failed import leaves the current catalog in place.

The same price, ratio, and stock-id rules apply as before: the first column is the serial, the first `stock_id` wins, and empty stock ids are skipped.

`.htaccess` and `.user.ini` raise the upload limit to 64 MB for a direct file submit. `php artisan serve` uses the CLI `php.ini` (`upload_max_filesize`). The batch import does not send the whole workbook in one request, so it still works when that limit is 2 MB.

Run `php artisan migrate` on the server after pulling, including the diamond list indexes.

## Tests

```bash
vendor\bin\phpunit tests\Unit\DiamondSpreadsheetTest.php
```
