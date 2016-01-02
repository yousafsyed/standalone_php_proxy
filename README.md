# standalone_php_proxy
Standalone proxy server in PHP sockets, I am  Guzzle to route the requests. Idea was if I can make HTTP Proxy Server in php to get more control over proxy and custom logic. Currently it only supports http requests you can choose port of your own choice plus if you want to make it public or private.  This is a standard HTTP Proxy so you can use in browser. By default its only working for localhost, but you can allow public ip's. 

## Todo
1. Pacakage availble via composer
2. Easy way to integrate custom Logic
3. Support HTTPs
4. Parse FormData

### How To Install?
```
$ php composer install
$ php composer update
$ php composer dump-autoload
```
### How to Run?
```
$ php server.php
```
### ScreenShot

![ScreenShot](http://i.imgur.com/N5wu80F.png)
