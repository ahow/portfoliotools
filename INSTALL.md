# INSTALLATION

The instruction is designed for the case when the root directory of the site is in the home directory ~
In other cases replace ~ to the path of the site.

1. Login via SSH to your virtual website host
2. Run commands in the SSH console
``` bash
    # Go to the root directory of the website.
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
 cd ~
 cp ttools/ajx-framework/config.php.bak config.php
```

6. Edit config file.
```  bash
    mcedit config.php`
```
You must change database settings.
Also, you can change default Time zone, default template, and other settings.
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

7. Edit .htaccess if you need to disable or enable SEF URLs
``` bash
    cd ~
    mcedit .htaccess
```
To disable SEF URLs, you should change `MOD_REWRITE_SEF` to `off` and change
`$sef` parameter in the _config.php_ to `false`

Below you can see default settings.
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
8. To install the site database just open URL: [http://your_site_name.com/setup](http://your_site_name.com/setup)
If SEF URLs are disabled then open other URL: [http://your_site_name.com/index.php/setup](http://your_site_name.com/index.php/setup)


# UPDATE
1. To update the website from GitHub run these commands in SSH console:
``` bash
    cd ~\ttools
    git pull
```
2. To update the database structure open this URL: [http://your_site_name.com/setup](http://your_site_name.com/setup)  
If SEF URLs are disabled then open other URL: [http://your_site_name.com/index.php/setup](http://your_site_name.com/index.php/setup)
