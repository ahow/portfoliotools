# INSTALLATION
1. Login via SSH to your virtual website host
2. Run commands in ssh console
``` bash
# Go to the home directory of the website
cd ~

# Create framework directory
mkdir ttools
cd ttools

#Clone git repository:
git clone https://github.com/ahow/portfoliotools.git
```

3. Copy contents of www directory to the site root path, or make symlinks
``` bash
    # Run, if you wish to update files manually
    cd ~
    cp -R ttools/www/* .
    
    # Run,  if you wish to update files from git
    cd ~
    ln -s ttools/www/index.php index.php
    ln -s ttools/www/index.php html.php
    ln -s ttools/www/index.php ajax.php
    ln -s ttools/www/js js
    ln -s ttools/www/css css
    cp -R ttools/www/bootstrap-3.3.6 .
    cp -R ttools/www/images .
    cp ttools/www/path.php .
    cp ttools/www/robots.txt .
    cp ttools/www/.htaccess .
```

4. Open and edit path.php `mcedit path.php`
``` php
<?php
    // path to the closed part of the framework
    define('SYS_PATH','ttools/ajx-framework/');
    // path for file storing and uploading
    define('UPLOAD_PATH','ttools/uploads/');
    define('LOG_PATH','ttools/log/');
?>
```
5. Copy example of the site settings to config.php 
``` bash
 cd ttools/ajx-framework/
 cp config.php.bak config.php
```

6. Edit config `mcedit config.php`
``` php
<?php
  /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */
  class wConfig extends wMain
  { public $conf = null;
      // Database settings
      // Open first: http://yoursite.com/setup
      public $dbtype = 'mysql';
      public $dbhost = 'dbhost';
      public $dbname = 'dbname';  // Database name
      public $dbuser = 'username';
      public $dbpass = 'secret***password';       // Password
      public $dbcharset = 'utf8';

      // System settings
      public $title = 'Theme tools';
      public $author = 'Andrew Howard';
      public $description = 'Theme tools';
      public $root_prefix = ''; // Site subdirectory
      public $sef = true; // SEF URLs are enabled
      public $lang = 'EN';
      protected $template = 'templates/template_auth.php';
      public $authorizedURL = '/'; // Goto after authorize
      public $default_timezone = 'Asia/Irkutsk';
            
      // Custom settings
      public $md_conf = 1;
      public $pg_rows = 15;
      public $csv_delim = ',';  // CSV exporting delimeter

  }
?>
```

7. Edit .htaccess `cd ~; mcedit .htaccess`
``` html
Options +FollowSymLinks
Options -MultiViews
IndexIgnore */*
<IfModule mod_rewrite.c>
    RewriteEngine on
    SetEnv MOD_REWRITE_SEF on
    RewriteBase /
    # if a directory or a file exists, use it directly
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d

    # otherwise forward it to index.php
    RewriteRule (.*) index.php
</IfModule>
```
8. To install the site database just open URL: [http://yoursite.com/setup](http://yoursite.com/setup)

# UPDATE
To update web site from GitHUB run these commands in SSH console:
``` bash
cd ~\ttools
git pull
```
