# standalone_php_proxy
Standalone proxy server in PHP sockets, I am using Guzzle to route the requests. Idea was if I can make HTTP Proxy Server in php to get more control over proxy and custom logic. Currently it only supports http requests you can choose port of your own choice plus if you want to make it public or private.  This is a standard HTTP Proxy so you can use in browser. By default its only working for localhost, but you can allow public ip's. 

## Todo
1.  ~~Package availble via composer~~
2. Easy way to integrate custom Logic
3. Support HTTPs
4.  ~~Parse FormData~~

### How To Install?
Define package in your composer.json file as require dependency
```json
   "require": {
        "yousafsyed/standalone_php_proxy": "^1.0"
    }
```
Now update/install composer dependencies
```
   $ composer install
   $ composer update
```
### Example
Create a file server.php
```php
   require "./vendor/autoload.php";
   use YousafSyed\ProxyServer;
   $server = new ProxyServer(); // optional parameters for port and host like this new ProxyServer('8080','localhost')
   // finally run the server
   $server->run();
```
### How to Run?
```
$ php server.php
```
### ScreenShot


![ScreenShot](http://i.imgur.com/N5wu80F.png)
