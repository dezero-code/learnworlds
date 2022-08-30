<?php
/**
 * Learnworlds API client
 * 
 * Helper classes to work with Learnworlds API v2
 * 
 * @see https://learnworlds.dev/
 */

namespace dzlab\learnworlds\components;

// use dz\base\ApplicationComponent;
use dz\rest\RestClient;
use dz\helpers\DateHelper;
use dz\helpers\Json;
use dz\helpers\Log;
use dz\helpers\StringHelper;
use dz\helpers\Url;
use user\models\User;
use Yii;

class LearnworldsApi extends RestClient
{
    /**
     * @var string. Auth URL to create API tokens
     */
    public $auth_url;


    /**
     * @var File
     */
    public $token_file;


    /**
     * @var bool Sanbdox environment
     */
    public $is_sandbox = false;


    /**
     * @var string Last used endpoint
     */
    public $last_endpoint;


    /**
     * @var array Learnworlds configuration
     */
    protected $vec_config = [];


    /**
     * Init function
     */
    public function init()
    {
        // Learnworlds configuration
        $this->vec_config = Yii::app()->config->get('components.learnworlds');

        // Debug mode?
        if ( isset($this->vec_config['is_debug']) )
        {
            $this->is_debug = $this->vec_config['is_debug'];
        }

        // Auth URL to create API tokens
        $this->auth_url = $this->base_url . '/admin/api/oauth2/access_token';

        // Sandbox environment?
        if ( isset($this->vec_config['is_sandbox']) )
        {
            $this->is_sandbox = $this->vec_config['is_sandbox'];
        }

        if ( $this->is_sandbox )
        {
            // API key for SANDBOX
            $this->api_key = getenv('LEARNWORLDS_SANDBOX_API_CLIENT_ID');

            // Secret key for SANDBOX
            $this->auth_key = getenv('LEARNWORLDS_SANDBOX_API_CLIENT_SECRET');

            // Base URL
            $this->base_url = getenv('LEARNWORLDS_SANDBOX_API_URL');

        }
        else
        {
            // API key for production
            $this->api_key = getenv('LEARNWORLDS_API_CLIENT_ID');

            // Secret key for production
            $this->auth_key = getenv('LEARNWORLDS_API_CLIENT_SECRET');

            // Base URL
            $this->base_url = getenv('LEARNWORLDS_API_URL');

        }

        parent::init();
    }


    /**
     * Custom event before sending HTTP request
     */
    public function beforeSend()
    {
        $this->client->setHeaders([
            'Authorization'     => $this->get_access_token(),
            // 'PSU-IP-Address'    => Yii::app()->request->getUserHostAddress(),
            'accept'            => 'application/json;charset=UTF-8',
            'Content-Type'      => 'application/json;charset=UTF-8',
        ]);

        return parent::beforeSend();
    }


    /**
     * SSO with a User's Email or User ID
     *
     * @see https://learnworlds.dev/docs/api/58052c1c3066e-single-sign-on
     */
    public function post_sso($vec_input = [])
    {
        /*
        // Endpoint and entity information
        $this->entity_type = 'LemonwayAccount';
        $endpoint_uri = '/v2/accounts/individual';
        if ( $is_legal )
        {
            $endpoint_uri = '/v2/accounts/legal';
        }

        // Save last used endpoint
        $this->last_endpoint = $endpoint_uri;

        // Send request
        return $this->post($endpoint_uri, $vec_input);
        */
    }


    /**
     * Load the file "/storage/tmp/learnworlds.json"
     */
    public function load_token_file()
    {
        $token_file_path = Yii::app()->path->get('privateTempPath') . DIRECTORY_SEPARATOR . 'learnworlds.json';

        if ( empty($this->token_file) )
        {
            $this->token_file = Yii::app()->file->set($token_file_path);
        }

        if ( $this->token_file && ( $this->token_file->getExists() || $this->token_file->create() ) )
        {
            return true;
        }

        return false;
    }


    /**
     * Return access token (OAuth)
     */
    public function get_access_token()
    {
        // Get token information saved on /storage/tmp/learnworlds.json file
        if ( $this->load_token_file() )
        {
            $now = time();
            $data_json = $this->token_file->getContents();

            $is_request_token = true;
            if ( !empty($data_json) )
            {
                $vec_data = Json::decode($data_json);
                if ( !empty($vec_data) && is_array($vec_data) && isset($vec_data['expiration_date']) && $vec_data['expiration_date'] > $now )
                {
                    $is_request_token = false;
                }
            }

            // Empty file or expired token ---> Request new token and save on the file
            if ( $is_request_token )
            {
                $vec_data = $this->request_new_token();
                if ( isset($vec_data['tokenData']) && isset($vec_data['tokenData']['expires_in']) )
                {
                    $vec_data['expiration_date'] = $vec_data['tokenData']['expires_in'] + $now;
                }

                $this->token_file->setContents(Json::encode($vec_data));
            }

            // Return access token
            if ( isset($vec_data['tokenData']) && isset($vec_data['tokenData']['access_token']) )
            {
                return $vec_data['tokenData']['token_type'] . ' ' .$vec_data['tokenData']['access_token'];
            }
        }

        return '';
    }


    /**
     * Request new access token via API
     *
     * @see https://learnworlds.dev/docs/api/b6b6c2d4906e9-authentication
     */
    public function request_new_token()
    {
        // Build URL and create HTTP Client
        $client = new \EHttpClient($this->auth_url);
        $client->setMethod('POST');
        $client->setHeaders([
            'Lw-Client'     => $this->api_key,
            'accept'        => 'application/json;charset=UTF-8',
            'Content-Type'  => 'application/x-www-form-urlencoded'
        ]);
        $client->setParameterPost([
            'client_id'     => $this->api_key,
            'client_secret' => $this->auth_key,
            'grant_type'    => 'client_credentials'
        ]);

        $response = $client->request();
        if ( $response->isSuccessful() )
        {
            if ( $this->is_debug )
            {
                Log::learnworlds_dev('Requesting new token: OK - '. trim($response->getBody()));
            }

            return Json::decode($response->getBody());
        }
        else
        {
            Log::learnworlds_error('Requesting new token: ERROR - '. trim($response->getRawBody()));

            if ( $this->is_debug )
            {
                Log::learnworlds_dev('Requesting new token: ERROR - '. trim($response->getRawBody()));
            }

            return null;
        }
    }
}
