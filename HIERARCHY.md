# FILE HIERARCHY
## Web site root path
* path.php - _contains settings of directory path (path to the ajx-framework, upload path, log path)_
* index.php - _The main point of the website framework_
* ajax.php - _used for AJAX queries_
* html.php - _used for inline parts of HTML documents_
* .htaccess - _settings for mod rewrite Apache web server module, required for SEF URLs_
* ***images*** - _images directory_
* ***js*** - _static js scripts directory_
* ***css*** - _static styles_
* ***bootstrap-3.3.6*** - _bootstrap files directory_
* ***ttools*** - _path to the site's framework_

## Web site framework hierarchy
* ***ttools*** - _root path_
  * ***ajx-framework*** - _framework_
     * ***pages*** - _dynamic pages of site_
        * ***sales*** - Pages of the Themes tools
           * install.sql - Database structure install file
           * update.1.sql - Database update 1
           * update.2.sql - Database update 2
        * ***setup*** - _DATABASE SETUP PAGE_
     * ***templates*** - _HTML templates of the site_
        * template_auth.php - _THE TEMPLATE FILE ENABLED BY DEFAULT_
     * ***modules*** - _inline modules_
        * ***pMenu*** - _SITE MENUS_
     * ***doc*** - _documentation, need to rework_
     * ***lang*** - _translation files_
       * EN.ini - _global english translation file_
       * RU.ini - _global russian translation file_
     * ***lib*** - _libraries of functions_
     * **vendor** - _external libraries_
     * default.php - _DEFAUTL SITE PAGE!_
     * composer.json - _Composer settings, requred to install external libraries_
     * composer.lock - _Composer lock file_
     * config.php - _THE CONFIGURATION FILE_
     * config.php.bak - _example of the configuration file_
     * classes.php - _global framework classes_
     * errors.php - _handling of the PHP errors_
     * ajerrors.php - _handling of the AJAX errors_
 
     
