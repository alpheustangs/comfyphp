# Write PHP comfortably with ComfyPHP

Provide an better development environment for developers who targeting specialized environments, such as Internet Explorer or environments where JavaScript is disabled.

## Framework Directory Structure

-   `public` directory <br/>
    Comprises files that are publicly accessible, including the index, robots.txt, sitemap.xml, scripts and styles. <br/><br/>
    -   `index.php` index <br/>
        This file serves as a trigger to activate the entire system. <br/><br/>
-   `src` directory (default) <br/>
    Where the source files for editing reside. <br/><br/>
    -   `pages` directory (default) <br/>
        Stores the routing pages for `/public/index.php` <br/><br/>
        -   `_document.php` config file <br/>
            This file configures the document's appearance across all the pages. <br/><br/>
-   `.env.local` config file <br/>
    This file holds the first priority for all environment variables. <br/><br/>
-   `.env` config file <br/>
    This file holds the second priority for all environment variables. <br/><br/>
-   `.env.development.local` config file <br/>
    This file holds the third priority for development environment variables. <br/><br/>
-   `.env.development` config file <br/>
    This file holds the fourth priority for development environment variables. <br/><br/>
-   `.env.production.local` config file <br/>
    This file holds the third priority for production environment variables. <br/><br/>
-   `.env.production` config file <br/>
    This file holds the fourth priority for production environment variables. <br/><br/>
-   `comfy.config.php` config file <br/>
    Contains all the configurations of ComfyPHP framework. <br/><br/>

## Before Using it

Required dependencies for ComfyPHP:

-   <a href="https://www.php.net/downloads.php" target="_blank" rel="noopener noreferrer">PHP</a>
-   <a href="https://getcomposer.org/download/" target="_blank" rel="noopener noreferrer">Composer</a>

## Download / Install

To use this framework, you can install it with Composer.

```bash
composer require comfyphp/core
```

## Basic `public/index.php` format

You can create `index.php` inside the public folder like the example provided below:

```php
require_once __DIR__ . "/../vendor/autoload.php";

$core = new ComfyPHP\Core();
$http = $core->http;

$http->get("/", "index");

$http->post("/post/create", "post/create");
$http->get("/post", "post/read");
$http->put("/post/update", "post/update");
$http->delete("/post/delete", "post/delete");

$http->all("/menu", "menu/index");

$core->run();
```

Or just add the `fileBasedRouter()` function into the `index.php` for File-Based Routing which will be introduced later.

```php
require_once __DIR__ . "/../vendor/autoload.php";

$core = new ComfyPHP\Core();

$core->fileBasedRouter();

$core->run();
```

## Basic `src/pages/_document.php` Format

Both `<!--%head%-->` and `<!--%body%-->` will work as a regex to be replaced by `<head>...</head>` and `<body>...</body>` from each page, please do not delete it when editing the document.

```html
<!DOCTYPE html>

<html>
    <head>
        <meta charset="UTF-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1, viewport-fit=cover"
        />
        <!--%head%-->
    </head>

    <body>
        <!--%body%-->
    </body>
</html>
```

## Basic `src/pages/page.php` Format

Inside each webpage, there are two sections: `<head>...</head>` and `<body>...</body>`. You can customize the content in both the head and body of each page by making edits. However, if both the `<head>...</head>` and `<body>...</body>` sections are missing from the file, ComfyPHP will recognize it as an API controller and provide all the results captured from it.

```html
<head>
    <!-- Place your head content here -->
</head>

<body>
    <!-- Place your body content here -->
</body>
```

## Config composer.json

You can config `composer.json` to run ComfyPHP scripts.

```json
{
    "scripts": {
        "dev": ["Composer\\Config::disableProcessTimeout", "comfyphp dev"],
        "build": ["comfyphp build"],
        "preview": [
            "Composer\\Config::disableProcessTimeout",
            "comfyphp preview"
        ]
    }
}
```

or just run the command manually:

```bash
./vendor/bin/comfyphp {dev/build/preview}
```

## Development Mode

Start the application with development env:

```bash
composer run-script dev
```

## Build for Production

To build the env for production server:

```bash
composer run-script build
```

Then you can use apache/nginx to take care of the server.

## Production Mode Preview

Start the application with production env:

```bash
composer run-script preview
```

## Server Port

Server will run in <a href="http://localhost:3000" target="_blank" rel="noreferrer noopener">http://localhost:3000</a> by default, you can add the port parameter to the script in order to set up the server port:

```bash
comfyphp dev -p 4000
comfyphp dev --port 4000
comfyphp preview -p 5000
comfyphp preview --port 5000
```

## Routing

There are two methods available for routing: function-based routing and file-based routing. You have the freedom to choose either method based on your preference.

### Function-Based Routing

When utilizing Function Based Routing, ComfyPHP will automatically search for a PHP file in `/src/pages` that matches the name mentioned in `/public/index.php`. By default, if you enter `get("/alphabet", "abc");`, it will look for `/src/pages/abc.php`. If you haven't created a file with the same name, it will return no results. Additionally, you can use methods other than `get`, such as `post`, `put`, and `delete`.

```php
$core = new ComfyPHP\Core();
$http = $core->http;

$http->get("/", "index");
$http->get("/alphabet", "abc");
```

### File-Based Routing

To implement File Based Routing, simply include `fileBasedRouter();` in `/public/index.php`. This enables ComfyPHP to search within the `/src/pages` directory when a user visits the site. For instance, when a user visits `/settings/themes?abc=123` with `POST`,`GET`,`PUT`,`DELETE` HTTP methods, ComfyPHP will look for a file named `settings/themes.php` inside the `pages` folder. If such a file doesn't exist, ComfyPHP will then search for `settings/themes/index.php` instead. It will only return no results if neither `themes.php` nor `index.php` files are created within the `themes` folder.

```php
$core = new ComfyPHP\Core();

$core->fileBasedRouter();
```

## About .env

To store different environment variables, you can create multiple .env files, with their priorities determining the order as shown below:

-   Development mode

    `.env.local` > `.env` > `.env.development.local` > `.env.development`

-   Production mode

    `.env.local` > `.env` > `.env.production.local` > `.env.production`

### Example

You can add different variables inside the env files like the example below:

```conf
COOKIE_DOMAIN="https://example.com"
```

After the process, you can use the variables within the pages.

```php
echo $_ENV["COOKIE_DOMAIN"];
```

## 404 Error Handling

For the 404 error, ComfyPHP will send a 404 status back to the client. You can add a file named `_404.php` to the `pages` folder. This file will serve as the error handling page when a client tries to access a page that cannot be found in the router.

## ComfyPHP Default Variables List

-   $\_ENV["ENV"]
-   $GLOBALS["ROOT"]
-   $GLOBALS["CONFIG_VERSION"]
-   $GLOBALS["CONFIG_MINIMIZE"]
-   $GLOBALS["CONFIG_PAGE_PATH"]
-   $GLOBALS["SYSTEM_DEBUG"]

## License

This project is MIT licensed, you can find the license file [here](./LICENSE).
