ARA - a FreeRADIUS web interface

ASN RADIUS Admin Installation Guide
===================================

Requirements
------------

    * PHP >= 5.x.x
    * HTTP server with PHP support enabled
    * MySQL 4+ server with FreeRADIUS database

Get Sigma
---------

Ara uses Sigma template engine. You must have it to use ARA. You can install it
easily using well known and popular PHP Pear installator.

    pear channel-update pear.php.net
    pear install HTML_Template_Sigma

Make link in htdocs
-------------------

You probably want ARA to be a part of your administrative site, like
http://mysite.lan/ara/. To do that you need to link it in htdocs (the
document root of your http server).

    ln -s src/htdocs /var/www/htdocs/ara

Edit index.php and search for this line:

    define("ARA_PATH", "../");

This is the place to put information about real path to ara/src directory.

Note that you probably don't want strangers to look into your RADIUS database,
so protect it from being publicly accessible using HTTP authentication
mechanisms.

Configure ARA
-------------

Now is the time to prepare ARA configuration files.

    cd ara/src/config
    cp config.php.dist config.php

Edit config.php. It's quite self explanatory, but some sections are a material
for longer manual then this guide. For now just edit GENERAL and SQL sections.
If you still have personal user information from previous Dialup Admin
installation in your database, change $config["sql_user_extension"] to
TRUE.

You may want to set $config['use_auth'] to FALSE and setup your web
server to require authentication before serving any documents from Ara web
directory.

Look at AUTHORIZATION section. There is $config["access_level"] option there.
You will probably want to change it to

ARA_ACCESS_ALL so default user will have full rights to do anything he wants.
It's quite typical for small setups.

Adjust config.php file permissions. Be restrictive - there is a valuable MySQL
password there.

Try it
------

Point your browser to http://mysite.lan/ara. If something is wrong - read
error message and try to fix it.

Tip: In case of some strange errors, php error log may be hidden behind top bar.
Use "show page source" feature of your browser to discover what was it.
