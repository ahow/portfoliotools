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

use OAuth\OAuth2\Service\Vkontakte;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use OAuth\ServiceFactory;

require_once SYS_PATH.'vendor/autoload.php';

// Session storage
$storage = new Session();

$servicesCredentials = array();
include(__DIR__.'/readconfig.php');

if (!isset($servicesCredentials['vkontakte']))
{   echo T('ERR_LOST_CONFIG_OF_OAUTH_MODULE');
    $this->oauth = null;
    return;
}

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
    $servicesCredentials['vkontakte']['key'],
    $servicesCredentials['vkontakte']['secret'],
    $currentUri->getAbsoluteUri()
);

$serviceFactory = new ServiceFactory();

$this->oauth = null;

try
{
    
    // Instantiate the Google service using the credentials, http client and storage mechanism for the token
    /** @var $vkService VK 'email' */     
    $vkService = $serviceFactory->createService('vkontakte', $credentials, $storage, array('email'));
    if (!empty($_GET['code'])) 
    {   
        // retrieve the CSRF state parameter
        //$state = isset($_GET['state']) ? $_GET['state'] : null;

        // This was a callback request from vk, get the token
        // $vkService->requestAccessToken($_GET['code'], $state);
        $vkService->requestAccessToken($_GET['code']);

        // Send a request with it
        $rvk = (object)json_decode($vkService->request('users.get'), 'GET'); 
        $rvk = (object)$rvk->response[0];
        $r = new stdClass();
        $r->given_name = $rvk->first_name;
        $r->family_name = $rvk->last_name;
        $r->name = $rvk->uid;
        $r->id = $rvk->uid;
        $r->picture = '';
        $r->email = '';
        if ( isset($_SESSION['lusitanian-oauth-token']) )
        {  $loa = $_SESSION['lusitanian-oauth-token'];
           if (isset($loa['Vkontakte']))
           { $token = unserialize($loa['Vkontakte']);
             $ep = $token->getExtraParams(); // вытащим email
             if (isset($ep['email']))  $r->email = $ep['email'];
           }
        }        
        $this->oauth = $r;
    } else {
        $url = $vkService->getAuthorizationUri();
        header('Location: ' . $url);
    }
    
} catch (Exception $e)
{ echo "ERROR: ".$e->getMessage();
}

