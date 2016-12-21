<?php

/**
 * Example of retrieving an authentication token of the Twitter service
 *
 * PHP version 5.4
 *
 * @author     David Desberg <david@daviddesberg.com>
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

use OAuth\OAuth1\Service\Twitter;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use OAuth\ServiceFactory;

require_once '../vendor/autoload.php';

// We need to use a persistent storage to save the token, because oauth1 requires the token secret received before'
// the redirect (request token request) in the access token request.
$storage = new Session();

$servicesCredentials = array();

include(__DIR__.'/readconfig.php');

if (!isset($servicesCredentials['twitter']))
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
  {  return $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].mkURL($this->page);
  }
  function getRelativeUri()
  { return mkURL($this->page);
  }
}

$currentUri = new URI('/'.$seg[0].'/'.$srv);

// Setup the credentials for the requests
$credentials = new Credentials(
    $servicesCredentials['twitter']['key'],
    $servicesCredentials['twitter']['secret'],
    $currentUri->getAbsoluteUri()
);

$serviceFactory = new ServiceFactory();

// Instantiate the twitter service using the credentials, http client and storage mechanism for the token
/** @var $twitterService Twitter */
$twitterService = $serviceFactory->createService('twitter', $credentials, $storage);

$this->oauth = null;

try
{
    if (!empty($_GET['oauth_token'])) {
        $token = $storage->retrieveAccessToken('Twitter');

        // This was a callback request from twitter, get the token
        $twitterService->requestAccessToken(
            $_GET['oauth_token'],
            $_GET['oauth_verifier'],
            $token->getRequestTokenSecret()
        );

        // Send a request now that we have access token        
        $rtw = (object)json_decode($twitterService->request('account/verify_credentials.json'));
        
        $r = new stdClass();
        $a = explode(' ',$rtw->name);
        $r->given_name = $a[0];        
        $r->family_name = '';
        if (isset($a[1])) $r->family_name=$a[1];
        $r->name = $rtw->screen_name;
        $r->id = $rtw->id;
        $r->picture = $rtw->profile_image_url;
        $r->email = '';
        $this->oauth = $r;        

    } else {
        // extra request needed for oauth1 to request a request token :-)
        $token = $twitterService->requestRequestToken();
        $url = $twitterService->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()));
        header('Location: ' . $url);
    } 
} catch (Exception $e)
{ echo "ERROR: ".$e->getMessage();
}
