<?php

namespace OAuth\OAuth2\Service;

use OAuth\OAuth2\Token\StdOAuth2Token;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Http\Uri\UriInterface;

class Yandex  extends AbstractService
{
    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
        $scopes = array(),
        UriInterface $baseApiUri = null
    ) {
        parent::__construct($credentials, $httpClient, $storage, $scopes, $baseApiUri);

        if (is_null($this->baseApiUri) && $storage->hasAccessToken($this->service())) {
            $this->setBaseApiUri($storage->retrieveAccessToken($this->service()));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationMethod()
    {
        return static::AUTHORIZATION_METHOD_QUERY_STRING_V3;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri('https://oauth.yandex.ru/authorize');
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://oauth.yandex.ru/token');
    }

    /**
     * {@inheritdoc}
     */
    protected function parseAccessTokenResponse($responseBody)
    {
        // Parse JSON
        $data = json_decode($responseBody, true);

        // Do validation.
        if (null === $data || !is_array($data)) {
            throw new TokenResponseException('Unable to parse response.');
        } elseif (isset($data['error'])) {
            throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
        }

        // Create token object.
        $token = new StdOAuth2Token($data['access_token']);

        // Set the right API endpoint.
        $this->setBaseApiUri($token);

        // Mailchimp tokens evidently never expire...
        $token->setEndOfLife(StdOAuth2Token::EOL_NEVER_EXPIRES);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
     
    public function request($path, $method = 'GET', $body = null, array $extraHeaders = array())
    {   if (is_null($this->baseApiUri)) {
            $this->setBaseApiUri($this->storage->retrieveAccessToken($this->service()));
        }

        return parent::request($path, $method, $body, $extraHeaders);
    }
    

    /**
     * Set the right base endpoint.
     *
     * @param StdOAuth2Token $token
     */
    protected function setBaseApiUri(StdOAuth2Token $token)
    {   $this->baseApiUri = new Uri('https://login.yandex.ru/?format=json&oauth_token='.$token->getAccessToken());
        
        // Allow chaining.
        return $this;
    }
}
