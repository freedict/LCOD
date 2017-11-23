<div id="table-of-contents">

Table of Contents
-----------------

<div id="text-table-of-contents">

-   [1. About](#orgd17d93d)
-   [2. Architecture](#org7e89901)
-   [3. Tests](#org55b54ab)
-   [4. Adding support for specific dictionaries](#org3dea49f)
-   [5. Installation](#org492f51b)
    -   [5.1. Only testing the app](#orge12ca1d)
        -   [5.1.1. Prerequisites](#org5aa37db)
        -   [5.1.2. Create database and the database user](#org0932e4c)
        -   [5.1.3. Generate the sql data files out of the
            tei](#orgffc32c7)
        -   [5.1.4. Install the postgres database
            extensions](#org1950e6c)
        -   [5.1.5. Populate database tables](#org262de5a)
        -   [5.1.6. install packages, configure server](#org7d2faef)
        -   [5.1.7. Start server](#org06046d9)
    -   [5.2. Full server setup](#org29a0216)
-   [6. TO DO List](#orgc5c10de)
-   [7. Contact](#org831882b)
-   [8. Licence](#org4a95f88)

</div>

</div>

<a id="orgd17d93d"></a>

# About

LCOD is a libre collaborative online dictionary app. It allows you to read and
collaboratively edit dictionaries from freedict.org.


<a id="org7e89901"></a>

# Architecture

The database of the app has mainly two types of tables - the tei tables and the
patches tables. The tei tables contain the tei entries from the tei files of the
free dictionary repo. The patches tables contain the patches.

A lookup looks like this: Search the patch table for a patch, that is approved
and not yet merged into the tei table. Then we lookup the tei table for an
entry, which has no effective patch.

A patch in the patches tables has the following fields:

id                 
user\_id            
group\_id           
old\_entry          
new\_entry          
comment            
flags              
approved           
merged\_into\_tei    
creation\_date      

The groupId is just the md5 sum of the associated tei entry. A group consists of
the tei entry from the tei table and all associated patches. The order of the
patches in a group is indicated by the ID which is auto incremented by the
database. The higher the id, the newer the patch. A patch in FCOD is probably
more than you think. It also includes comments, flags, etc &#x2026; . If you just
want to add a comment, you have to commit a patch. If you want to edit an
existing patch, you have to commit a new patch. No matter what you do, you have
to commit a new patch. The only exception is approving and setting the merged
into upstream flag.


<a id="org55b54ab"></a>

# Tests

The backend tests are located in tests/Feature/DictTest.php . 

You can run the backend tests via:   
$ phpunit tests/Feature/DictTest.php

Tests for javascript tei parsing and rendering are in production. Qunit is
setup. You can writes your tests into resources/assets/js/tests.js and run them
via <http://localhost:8000/tests>.


<a id="org3dea49f"></a>

# Adding support for specific dictionaries

You can find all dictionary specific stuff in resources/assets/js/dictSpecific/.
You can register your dict specific vue components in resources/assets/js/app.js
.


<a id="org492f51b"></a>

# Installation


<a id="orge12ca1d"></a>

## Only testing the app


<a id="org5aa37db"></a>

### Prerequisites

-   php-xdebug
-   composer
-   php
-   npm
-   postgresql
-   python3-psycopg2

For ubuntu: $ sudo apt-get install php postgresql python3-psycopg2 php-xdebug  

I used ubuntu 16.04. I had to install composer und npm manually. The
distro packages were too old.


<a id="org0932e4c"></a>

### Create database and the database user

$ sudo -u postgres createuser -P -d fd  
$ sudo -u postgres createdb -O fd freedict  


<a id="orgffc32c7"></a>

### Generate the sql data files out of the tei

We will use a shorter version of the eng-deu dictionary shippeded with LCOD.

$ cd $(LCOD\_ROOT)/maintainerScripts  
$ cp eng-deu-short-for-testing.tei dotTeis/eng-deu.tei  
$ make teis2sqls  


<a id="org1950e6c"></a>

### Install the postgres database extensions

$ cd $(LCOD\_ROOT)/maintainerScripts  
$ sudo -u postgres make installExtensions  


<a id="org262de5a"></a>

### Populate database tables

This may take some time. You will be spammed with 'INSERT 0 1'.  
$ cd $(LCOD\_ROOT)  
$ php artisan migrate  
$ cd maintainerScripts/  
$ PGPASSWORD=root make importSqls  


<a id="org7d2faef"></a>

### install packages, configure server

$ cd $(LCOD\_ROOT)  
$ composer install  
$ npm install  
$ cp .env.example .env  
$ php artisan key:generate  


<a id="org06046d9"></a>

### Start server

$ php artisan serve

If you want instant refresh of the browser whenever you change something in a
php or js file, You can also run this:

$ npm run watch

When debugging, consider <https://github.com/laravel/framework/issues/18515> and
install php-xdebug.

Enjoy!


<a id="org29a0216"></a>

## Full server setup

TODO: Add me!


<a id="orgc5c10de"></a>

# TO DO List

-   multilingual
-   support for more dictionaries
-   make everything pretty
-   page for entry history
-   page for searching patches
-   page for adding users to admin group
-   set/unset approval on existing patches


<a id="org831882b"></a>

# Contact

See the freedict mailinglist.


<a id="org4a95f88"></a>

# Licence

The app is licenced under the GNU GENERAL PUBLIC LICENSE Version 3. See COPYING file for more information.

