<?php
  /* Fedotov Vitaliy (c) Ulan-Ude 2016 | kursruk@yandex.ru */
  class sales extends wPage
  {  function __construct($cfg, $path, $seg=null)
     {  $this->cfg = $cfg;
        $this->includePageLocales(__DIR__);
        $cfg->title = 'Sales';
        $this->cpage = '';
        $this->seg = $seg;
        $this->nav ='';
        if (isset($seg[1])) 
        { $cpage = __DIR__."/{$seg[1]}.php";
          if (!file_exists($cpage)) header("HTTP/1.0 404 Not Found");
          else 
          { $this->cpage = $cpage;
            $this->nav = $seg[1];
            $cfg->title = T($this->nav);
          }
        } else $this->cpage=__DIR__."/index.php";
       $this->cfg->addJs('/js', 'formvalidator.js');
       $this->cfg->addJs('/js', 'models.js');
       if ($this->nav=='upload')
       {   $this->cfg->addJs('/html.php/pages/sales','previewCSV.js');
           $this->cfg->addJs('/html.php/pages/sales','upload.js');
       }
       else if ($this->nav=='companies')
       {   $this->cfg->addJs('/html.php/pages/sales','lookup.js');
           $this->cfg->addJs('/html.php/pages/sales','companies.js');
           $this->cfg->addJs('/bootstrap-3.3.6','bootstrap3-typeahead.min.js');
       } 
       else if ($this->nav=='sic')
       {
           $this->cfg->addJs('/html.php/pages/sales','sic.js');
       }
       else if ($this->nav=='market-ranking')
       {   
           $this->cfg->addJs('/html.php/pages/sales','market-ranking.js');            
       }
       else if ($this->nav=='market-summary')
       {   $this->cfg->addJs('/bootstrap-3.3.6','bootstrap3-typeahead.min.js');
           $this->cfg->addJs('/html.php/pages/sales','lookup.js');
           $this->cfg->addJs('/html.php/pages/sales','market-summary.js');
       }
       else if ($this->nav=='portfolio-list')
       {   $this->cfg->addJs('/html.php/pages/sales','previewCSV.js');
           $this->cfg->addJs('/html.php/pages/sales','portfolio-list.js');
       }
       else if ($this->nav=='metric-list')
       {   $this->cfg->addJs('/html.php/pages/sales','previewCSV.js');
           $this->cfg->addJs('/html.php/pages/sales','metric-list.js');
       }
       else if ($this->nav=='theme-exposures')
       {   $this->cfg->addJs('/html.php/pages/sales','theme-exposures.js');
           $this->cfg->addJs('https://code.highcharts.com','highcharts.js');
           $this->cfg->addJs('https://code.highcharts.com/modules','exporting.js');
       }
       else if ($this->nav=='portfolio-metrics')
       {   $this->cfg->addJs('/html.php/pages/sales','portfolio-metrics.js');
           $this->cfg->addJs('/html.php/pages/sales','stacked.js');
           // $this->cfg->addJs('/html.php/pages/sales','sectoralloc.js');
           $this->cfg->addJs('https://code.highcharts.com','highcharts.js');
           $this->cfg->addJs('https://code.highcharts.com','highcharts-more.js');
           $this->cfg->addJs('https://code.highcharts.com/modules','exporting.js');
       }
       else if ($this->nav=='companies-analysis')
       {   $this->cfg->addJs('/html.php/pages/sales','lookup.js');
           $this->cfg->addJs('/html.php/pages/sales','companies-analysis.js');
           $this->cfg->addJs('https://code.highcharts.com','highcharts.js');
           $this->cfg->addJs('https://code.highcharts.com','highcharts-more.js');
           $this->cfg->addJs('https://code.highcharts.com/modules','exporting.js');
       }
       else if ($this->nav=='industry-analysis')
       {   $this->cfg->addJs('/html.php/pages/sales','lookup.js');
           $this->cfg->addJs('/html.php/pages/sales','industry-analysis.js');
           $this->cfg->addJs('https://code.highcharts.com','highcharts.js');
           $this->cfg->addJs('https://code.highcharts.com','highcharts-more.js');
           $this->cfg->addJs('https://code.highcharts.com/modules','exporting.js');
       } else
       {   $this->cfg->addJs('/html.php/pages/sales','inittable.js');
       }
     }
     
     function afterInit()
     {  $user = $this->cfg->user->user;
        if (empty($user)) header('Location: '.mkURL('/login'));
     }
     
     function display()
     {  $user = $this->cfg->user->user;
        if ($this->cpage!='') include($this->cpage);        
     }
  }
?>
