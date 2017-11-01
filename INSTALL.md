# INSTALLATION
1. Login via SSH to your virtual website host
2. Run commands in ssh console
``` bash
# Go to the home directory of the website
cd ~

# Create framework directory
mkdir ttools

#Clone git repository:
git clone https://github.com/ahow/portfoliotools.git
```
3. Open and edit path.php `mcedit path.php`
``` php
<?php
    // path to the closed part of the framework
    define('SYS_PATH','ttools/ajx-framework/');
    // path for file storing and uploading
    define('UPLOAD_PATH','ttools/uploads/');
    define('LOG_PATH','ttools/log/');
?>
```
4. Copy example of the site settings to config.php 
``` bash
 cd ttools/ajx-framework/
 cp config.php.bak config.php
```
5. Edit config `mcedit config.php`
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
6. Edit .htaccess `cd ~; mcedit .htaccess`
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
7. To install the site database just open URL: [http://yoursite.com/setup](http://yoursite.com/setup)
