<?php
namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class PolarOpenAccesslink extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @var string
     */
    const BASE_AUTHENTICATION_URL = 'https://flow.polar.com/oauth2/';

    /**
     * @var string
     */
    const BASE_TOKEN_URL = 'https://polarremote.com/v2/oauth2/token';

    /**
     * @var string
     */
    const BASE_DATA_URL = 'https://www.polaraccesslink.com/';

    /**
     * @var string
     */
    protected $apiVersion = '3';

    /**
     * @var string
     */
    protected $apikey;


    /**
     * @inheritDoc
     */
    public function getAccessToken($grant, array $options = [])
    {
        $grant = $this->verifyGrant($grant);

        $params = [
            'redirect_uri'  => $this->redirectUri,
            'grant_type'    => 'authorization_code'
        ];

        $params   = $grant->prepareRequestParameters($params, $options);
        $request  = $this->getAccessTokenRequest($params);
        $response = $this->getParsedResponse($request);
        $prepared = $this->prepareAccessTokenResponse($response);
        $token    = $this->createAccessToken($prepared, $grant);

        return $token;
    }

    /**
     * Builds request options used for requesting an access token.
     *
     * @param  array $params
     * @return array
     */
    protected function getAccessTokenOptions(array $params)
    {
        $options = parent::getAccessTokenOptions($params);

        $options['headers']['Authorization'] = 'Basic ' . base64_encode(implode(':', [
                $this->clientId,
                $this->clientSecret,
            ]));
        return $options;
    }


    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return self::BASE_AUTHENTICATION_URL . 'authorization';
    }

    /**
     * @inheritDoc
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return self::BASE_TOKEN_URL;
    }

    /**
     * @inheritDoc
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return self::BASE_DATA_URL.'/v' . $this->apiVersion . '/users/'.$this->getAccessToken->getValues()['x_user_id'];
    }

    /**
     * @link https://www.polar.com/accesslink-api/#authentication
     *
     * Get the default scopes used by this provider.
     *
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return ['accesslink.read_all'];
    }

    /**
     * @inheritDoc
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException(
                'Forbidden',
                $response->getStatusCode(),
                $response
            );
        }
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultHeaders()
    {
        return [
            'content-type' => 'application/json'
        ];
    }

    /**
     * @inheritDoc
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return null;
    }

    /**
     * @return string
     */
    public function getBaseDataUrl()
    {
        return self::BASE_DATA_URL;
    }

    /**
     * @return string
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

}
