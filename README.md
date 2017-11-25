<!-- markdown-toc start - Don't edit this section. Run M-x markdown-toc-refresh-toc -->
**Table of Contents**

- [About](#about)
- [Architecture](#architecture)
- [Tests](#tests)
- [Adding support for specific dictionaries](#adding-support-for-specific-dictionaries)
- [Installation](#installation)
    - [Prerequisites](#prerequisites)
    - [Prepare Database](#prepare-database)
        - [Create database and the database user](#create-database-and-the-database-user)
        - [Generate the sql data files out of the tei](#generate-the-sql-data-files-out-of-the-tei)
        - [Install the postgres database extensions](#install-the-postgres-database-extensions)
        - [Populate database tables](#populate-database-tables)
    - [Start testing server](#start-testing-server)
    - [Start production server](#start-production-server)
- [TO DO list](#to-do-list)
- [Contact](#contact)
- [Licence](#licence)

<!-- markdown-toc end -->

# About

LCOD is a libre collaborative online dictionary app. It allows you to read and
collaboratively edit dictionaries from freedict.org.

# Architecture

The database of the app has mainly two types of tables - the tei tables and the
patches tables. The tei tables contain the tei entries from the tei files of the
free dictionary repo. The patches tables contain the patches.

A lookup looks like this: Search the patch table for a patch, that is approved
and not yet merged into the tei table. Then we lookup the tei table for an
entry, which has no effective patch.

A patch in the patches tables has the following fields:

- id
- user\_id
- group\_id
- old\_entry
- new\_entry
- comment
- old\_flags
- new\_flags
- approved
- merged\_into\_tei
- creation\_date

The groupId is just the md5 sum of the associated tei entry. A group consists of
the tei entry from the tei table and all associated patches. The order of the
patches in a group is indicated by the ID which is auto incremented by the
database. The higher the id, the newer the patch. A patch in FCOD is probably
more than you think. It also includes comments, flags, etc &#x2026; . If you just
want to add a comment, you have to commit a patch. If you want to edit an
existing patch, you have to commit a new patch. No matter what you do, you have
to commit a new patch. The only exception is approving and setting the merged
into upstream flag.

# Tests

The backend tests are located in tests/Feature/DictTest.php . 

You can run the backend tests via:   
$ phpunit tests/Feature/DictTest.php

Tests for javascript tei parsing and rendering are in production. Qunit is
setup. You can writes your tests into resources/assets/js/tests.js and run them
via <http://localhost:8000/tests>.

# Adding support for specific dictionaries

You can find all dictionary specific stuff in resources/assets/js/dictSpecific/.
You can register your dict specific vue components in resources/assets/js/app.js
.

# Installation

## Prerequisites

- composer
- npm
- libpng-dev
- make
- autoconf
- python3-psycopg2
- python3-lxml
- php 
- php-xdebug
- php-mcrypt 
- php-gd 
- php-mbstring
- php-dom
- php-zip
- php-pgsql
- apache2
- postgresql
- libapache2-mod-php

Install via apt-get: 

$ sudo apt-get install composer libpng-dev make autoconf python3-psycopg2 python3-lxml php  php-xdebug php-mcrypt php-gd php-mbstring php-dom php-zip php-pgsql apache2 postgresql libapache2-mod-php

I used Debian 9 (Stretch). I had to install npm manually.

If you have the prerequisites, run

$ cd $(LCOD\_ROOT)  
$ composer install  
$ npm install  
$ cp .env.example .env  
$ php artisan key:generate  
$ npm run production  

## Prepare Database

### Create database and the database user

$ sudo -u postgres createuser -P -d fd  
$ sudo nano /etc/postgresql/versionNumber/main/pg_hba.conf  
// and change 'peer' to 'md5' in the row 'local all all peer'  
$ sudo -u postgres createdb -O fd freedict  

### Generate the sql data files out of the tei

We will use a shorter version of the eng-deu dictionary shippeded with LCOD.

$ cd $(LCOD\_ROOT)/maintainerScripts  
$ cp eng-deu-short-for-testing.tei dotTeis/eng-deu.tei  
$ make teis2sqls  

### Install the postgres database extensions

$ cd $(LCOD\_ROOT)/maintainerScripts  
$ sudo -u postgres make installExtensions  

### Populate database tables

This may take some time. You will be spammed with 'INSERT 0 1'.  
$ cd $(LCOD\_ROOT)  
$ php artisan migrate  
$ cd maintainerScripts/  
$ PGPASSWORD=root make importSqls  

## Start testing server

$ php artisan serve

If you want instant refresh of the browser whenever you change something in a
php or js file, You can also run this:

$ npm run watch

When debugging, consider <https://github.com/laravel/framework/issues/18515> and
install php-xdebug.

## Start production server

See https://tecadmin.net/install-laravel-framework-on-ubuntu/

You also have to run: # a2enmod rewrite && service apache2 restart

# TO DO list

- multilingual
- support for more dictionaries
- make everything pretty
- page for entry history
- page for searching patches
- page for adding users to admin group

# Contact

See the freedict mailinglist.

# Licence

The app is licenced under the GNU GENERAL PUBLIC LICENSE Version 3. See COPYING
file for more information. Copyright owner are Sebastian Humenda, Andreas J.
Heil and all other people with write access to the FreeDict repos.

