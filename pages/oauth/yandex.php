<?php

/**
 * Example of retrieving an authentication token of the yandex service
 *
 * PHP version 5.4
 *
 * @author     David Desberg <david@daviddesberg.com>
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth2\Service\Yandex;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use OAuth\ServiceFactory;

/**
 * Bootstrap the example
 */
require_once '../vendor/autoload.php';

// Session storage
$storage = new Session();

$servicesCredentials = array();
$servicesCredentials['yandex'] = array(
'key'=>'your_key',
'secret'=>'your_secret');

class URI
{ var $page = '';  
    
  function URI($_page)
  { $this->page = $_page;
  }
  
  function getAbsoluteUri()    
  { return $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].mkURL($this->page);
    
  }
  function getRelativeUri()
  { return mkURL($this->page);
  }
}

$currentUri = new URI('/'.$seg[0].'/'.$srv);
 
// Setup the credentials for the requests
$credentials = new Credentials(
    $servicesCredentials['yandex']['key'],
    $servicesCredentials['yandex']['secret'],
    $currentUri->getAbsoluteUri()
);

$serviceFactory = new ServiceFactory();

$this->oauth = null;

try
{
    
    // Instantiate the yandex service using the credentials, http client and storage mechanism for the token
    /** @var $yandexService yandex */
    $yandexService = $serviceFactory->createService('yandex', $credentials, $storage, array());

    if (!empty($_GET['code'])) 
    {   
        // retrieve the CSRF state parameter
        $state = isset($_GET['state']) ? $_GET['state'] : null;

        // This was a callback request from yandex, get the token
        $yandexService->requestAccessToken($_GET['code'], $state);

        // Send a request with it
        $rvk = (object)json_decode($yandexService->request('info'), true);
        $r = new stdClass();
        $r->given_name = $rvk->first_name;
        $r->family_name = $rvk->last_name;
        $r->name = $rvk->display_name;
        $r->picture = '';
        $r->email = $rvk->default_email;
        $r->id = $rvk->id;
        $this->oauth = $r;

    } else {
        $url = $yandexService->getAuthorizationUri();
        header('Location: ' . $url);
    }
    
} catch (Exception $e)
{ echo "ERROR: ".$e->getMessage();
}

