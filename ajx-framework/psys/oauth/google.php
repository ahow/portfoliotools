<?php

/**
 * Example of retrieving an authentication token of the Google service
 *
 * PHP version 5.4
 *
 * @author     David Desberg <david@daviddesberg.com>
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth2\Service\Google;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use OAuth\ServiceFactory;

require_once SYS_PATH.'vendor/autoload.php';

// Session storage
$storage = new Session();

$servicesCredentials = array();
include(__DIR__.'/readconfig.php');

if (!isset($servicesCredentials['google']))
{   echo T('ERR_LOST_CONFIG_OF_OAUTH_MODULE');
    $this->oauth = null;
    return;
}

class URI
{ var $page = '';  
    
  function __construct($_page)
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
    $servicesCredentials['google']['key'],
    $servicesCredentials['google']['secret'],
    $currentUri->getAbsoluteUri()
);

$serviceFactory = new ServiceFactory();

$this->oauth = null;

try
{
    
    // Instantiate the Google service using the credentials, http client and storage mechanism for the token
    /** @var $googleService Google */
    $googleService = $serviceFactory->createService('google', $credentials, $storage, array('userinfo_email', 'userinfo_profile'));

    if (!empty($_GET['code'])) 
    {   
        // retrieve the CSRF state parameter
        $state = isset($_GET['state']) ? $_GET['state'] : null;

        // This was a callback request from google, get the token
        $googleService->requestAccessToken($_GET['code'], $state);

        // Send a request with it
        $r = (object)json_decode($googleService->request('userinfo'), true);
        $this->oauth = $r;

    } else {
        $url = $googleService->getAuthorizationUri();
        header('Location: ' . $url);
    }
    
} catch (Exception $e)
{ echo "ERROR: ".$e->getMessage();
}

