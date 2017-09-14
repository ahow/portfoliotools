<?php

/**
 * Example of retrieving an authentication token of the mailru service
 *
 * PHP version 5.4
 *
 * @author     David Desberg <david@daviddesberg.com>
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth2\Service\Mailru;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use OAuth\ServiceFactory;

require_once '../vendor/autoload.php';

// Session storage
$storage = new Session();

$servicesCredentials = array();
include(__DIR__.'/readconfig.php');

if (!isset($servicesCredentials['mailru']))
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
    $servicesCredentials['mailru']['key'],
    $servicesCredentials['mailru']['secret'],
    $currentUri->getAbsoluteUri()
);

$serviceFactory = new ServiceFactory();

$this->oauth = null;

try
{
    
    // Instantiate the mailru service using the credentials, http client and storage mechanism for the token
    /** @var $mailruService mailru */
    $mailruService = $serviceFactory->createService('mailru', $credentials, $storage, array());

    if (!empty($_GET['code'])) 
    {   
        // retrieve the CSRF state parameter
        $state = isset($_GET['state']) ? $_GET['state'] : null;

        // This was a callback request from mailru, get the token
        $token = $mailruService->requestAccessToken($_GET['code'], $state);

        // Send a request with it
        $body = $mailruService->requestUserInfo($servicesCredentials['mailru']['key'], 
         $servicesCredentials['mailru']['secret'], $token );
      
        $a = json_decode($body, true);
        $rvk = (object)$a[0];
        $r = new stdClass();
        $r->given_name = $rvk->first_name;
        $r->family_name = $rvk->last_name;
        $r->name = $rvk->first_name.' '.$rvk->last_name;
        $r->picture = $rvk->pic_50;
        $r->email = $rvk->email;
        $r->id = $rvk->uid;
        $this->oauth = $r;

    } else {
        $url = $mailruService->getAuthorizationUri();
        header('Location: ' . $url);
    }
    
} catch (Exception $e)
{ echo "ERROR: ".$e->getMessage();
}

