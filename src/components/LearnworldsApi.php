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

        // Auth URL to create API tokens
        $this->auth_url = $this->base_url . 'oauth2/access_token';

        parent::init();
    }


    /**
     * Custom event before sending HTTP request
     */
    public function beforeSend()
    {
        $this->client->setHeaders([
            'Lw-Client'         => $this->api_key,
            'Authorization'     => $this->get_access_token(),
            'Content-Type'      => 'application/json;charset=UTF-8',
            // 'PSU-IP-Address'    => Yii::app()->request->getUserHostAddress(),
            // 'accept'            => 'application/json;charset=UTF-8',
        ]);

        return parent::beforeSend();
    }


    /**
     * Custom event after sending HTTP request
     */
    public function afterSend()
    {
        // If some error -> SAVE LOG
        if ( ! $this->is_last_action_success() )
        {
            $this->save_log('learnworlds');
        }

        // Register success requests
        else if ( $this->is_debug )
        {
            $this->save_log('learnworlds_dev');
        }

        // Save all POST and PUT requests on database
        if ( $this->method !== 'GET' )
        {
            $this->save_db_log('learnworlds');
        }
    }


    /**
     * SSO with a User's Email or User ID
     *
     * @see https://learnworlds.dev/docs/api/58052c1c3066e-single-sign-on
     */
    public function post_sso($vec_input = [])
    {
        // Endpoint and entity information
        $this->entity_type = 'LearnworldsSso';
        $endpoint_uri = '/sso';

        // Save last used endpoint
        $this->last_endpoint = $endpoint_uri;

        // Send request
        return $this->post($endpoint_uri, $vec_input);
    }


    /**
     * Returns the user specified by the provided user id.
     *
     * - API REST Endpoint "GET /v2/users/{id}"
     *
     * @see https://learnworlds.dev/docs/api/5281620efe003-get-a-user
     */
    public function get_user($learnworlds_user_id)
    {
        // Endpoint and entity information
        $this->entity_type = 'LearnworldsUser';
        $this->entity_id = $learnworlds_user_id;
        $endpoint_uri = '/v2/users/'. $learnworlds_user_id;

        // Save last used endpoint
        $this->last_endpoint = $endpoint_uri;

        // Send request
        return $this->get($endpoint_uri);
    }


    /**
     * Returns a list of all courses of the school.
     *
     * The courses are in sorted order, with the most recently
     * created course appearing first, and the list is paginated,
     * with a limit of 50 courses per page.
     *
     * - API REST Endpoint "GET /v2/courses"
     *
     * @see https://learnworlds.dev/docs/api/14ba192f977db-get-all-courses
     */
    public function get_courses()
    {
        // Endpoint
        $this->entity_type = 'LearnworldsCourse';
        $endpoint_uri = '/v2/courses';

        // Save last used endpoint
        $this->last_endpoint = $endpoint_uri;

        // Send request
        return $this->get($endpoint_uri);
    }


    /**
     * Returns the user specified by the provided user id.
     *
     * - API REST Endpoint "GET /v2/courses/{id}"
     *
     * @see https://learnworlds.dev/docs/api/d73c769b071d6-get-a-course
     */
    public function get_course($learnworlds_course_id)
    {
        // Endpoint and entity information
        $this->entity_type = 'LearnworldsCourse';
        $this->entity_id = $learnworlds_course_id;
        $endpoint_uri = '/v2/courses/'. $learnworlds_course_id;

        // Save last used endpoint
        $this->last_endpoint = $endpoint_uri;

        // Send request
        return $this->get($endpoint_uri);
    }


    /**
     * Enroll user to product, regarding course, bundle, manual subscription
     *
     * - API REST Endpoint "POST /v2/users/{id}/enrollment"
     *
     * @see https://learnworlds.dev/docs/api/3d5e79f96b44a-enroll-user-to-product
     */
    public function post_enroll_to_product($learnworlds_user_id, $vec_input = [])
    {
        // Endpoint and entity information
        $this->entity_type = 'LearnworldsUser';
        $this->entity_id = $learnworlds_user_id;
        $endpoint_uri = '/v2/users/'. $learnworlds_user_id .'/enrollment';

        // Save last used endpoint
        $this->last_endpoint = $endpoint_uri;

        // Send request
        return $this->post($endpoint_uri, $vec_input);
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
            // 'accept'        => 'application/json;charset=UTF-8',
            // 'Content-Type'  => 'application/x-www-form-urlencoded'
        ]);
        $client->setParameterPost([
            'data' => Json::encode([
                'client_id'     => $this->api_key,
                'client_secret' => $this->auth_key,
                'grant_type'    => 'client_credentials'
            ])
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
