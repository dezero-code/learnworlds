<?php
/**
 * Learnworlds Component
 * 
 * Helper classes to work with Learnworlds API v2
 * 
 * @see https://learnworlds.dev/
 */

namespace dzlab\learnworlds\components;

use dz\base\ApplicationComponent;
use dz\helpers\DateHelper;
use dz\helpers\Json;
use dz\helpers\Log;
use dz\helpers\StringHelper;
use dz\helpers\Url;
use user\models\User;
use Yii;

class LearnworldsComponent extends ApplicationComponent
{
    /**
     * @var bool. Debug mode? (log all responses)
     */ 
    public $is_debug = false;


    /**
     * @var bool Sanbdox environment
     */
    public $is_sandbox = false;

    /**
     * @var array Learnworlds configuration
     */
    protected $vec_config = [];


    /**
     * @var object LearnworldsApi
     */
    protected $api;


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

        // Init the component API
        $this->api = Yii::app()->learnworldsApi;

        parent::init();
    }


    /**
     * Single Sign-on process.
     * Returns an unique URL to make the SSO automatically
     *
     * - API REST Endpoint "POST /sso"
     *
     * @see LearnworldsApi::post_sso()
     */
    public function sso($user_id, $redirect_url)
    {
        $user_model = User::findOne($user_id);
        if ( $user_model )
        {
            // Send a "POST /sso" request
            $vec_input = [
                'email'         => $user_model->email(),
                'username'      => $user_model->wp_fullname(),
                // 'username'      => urlencode($user_model->username),
                'redirectUrl'   => $redirect_url
            ];
            $response = $this->api->post_sso($vec_input);

            if ( $this->api->is_last_action_success() )
            {
                $vec_response = $this->api->get_response_body(true);
                dd($vec_response);

                if ( $this->is_debug )
                {
                    Log::learnworlds_dev("LearnworldsComponent::sso({$user_id}, {$redirect_url}) - Last action success");
                    Log::learnworlds_dev(print_r($vec_response, true));
                }
            }
            else if ( $this->is_debug )
            {
                Log::learnworlds_dev("LearnworldsComponent::sso({$user_id}, {$redirect_url}) - Last action error: ". print_r($response, true));
            }
        }
        else
        {
            Log::learnworlds_error("LearnworldsComponent::sso({$user_id}, {$redirect_url}) - USER does not exist: {$user_id}");
        }

        return null;
    }


    /**
     * Returns the user specified by the provided user id.
     *
     * - API REST Endpoint "GET /v2/users/{id}"
     *
     * @see LearnworldsApi::get_user()
     */
    public function get_user($learnworlds_user_id)
    {
        // Send a "GET /v2/users/{id}" request
        $response = $this->api->get_user($learnworlds_user_id);

        // Update the model with last received data
        if ( $this->api->is_last_action_success() )
        {
            $vec_response = $this->api->get_response_body(true);
            dd($vec_response);

            if ( $this->is_debug )
            {
                Log::learnworlds_dev("LearnworldsComponent::get_user({$learnworlds_user_id}) - Last action success");
                Log::learnworlds_dev(print_r($vec_response, true));
            }
        }
        else if ( $this->is_debug )
        {
            Log::learnworlds_dev("LearnworldsComponent::get_user({$learnworlds_user_id}) - Last action error: ". print_r($response, true));
        }
    }


    /**
     * Returns information about the course specified by the provided course id.
     *
     * - API REST Endpoint "GET /v2/courses/{id}"
     *
     * @see LearnworldsApi::get_course()
     */
    public function get_course($learnworlds_course_id)
    {
        // Send a "GET /v2/courses/{id}" request
        $response = $this->api->get_course($learnworlds_course_id);

        // Update the model with last received data
        if ( $this->api->is_last_action_success() )
        {
            $vec_response = $this->api->get_response_body(true);
            dd($vec_response);

            if ( $this->is_debug )
            {
                Log::learnworlds_dev("LearnworldsComponent::get_course({$learnworlds_course_id}) - Last action success");
                Log::learnworlds_dev(print_r($vec_response, true));
            }
        }
        else if ( $this->is_debug )
        {
            Log::learnworlds_dev("LearnworldsComponent::get_course({$learnworlds_course_id}) - Last action error: ". print_r($response, true));
        }
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
     * @see LearnworldsApi::get_courses()
     */
    public function get_courses()
    {
        // Send a "GET /v2/courses" request
        $response = $this->api->get_courses();

        // Update the model with last received data
        if ( $this->api->is_last_action_success() )
        {
            $vec_response = $this->api->get_response_body(true);
            dd($vec_response);

            if ( $this->is_debug )
            {
                Log::learnworlds_dev("LearnworldsComponent::get_courses() - Last action success");
                Log::learnworlds_dev(print_r($vec_response, true));
            }
        }
        else if ( $this->is_debug )
        {
            Log::learnworlds_dev("LearnworldsComponent::get_courses() - Last action error: ". print_r($response, true));
        }
    }


    /**
     * Enroll user to product, regarding course, bundle, manual subscription
     *
     * - API REST Endpoint "POST /v2/users/{id}/enrollment"
     *
     * @see LearnworldsApi::post_enroll_to_product()
     */
    public function enroll_to_product($user_id, $product_id, $price, $product_type = 'course', $comments = null)
    {
        // Send a "GET /v2/courses" request
        $vec_input = [
            'productId'     => $product_id,
            'productType'   => $product_type,
            'price'         => $price,
            'justification' => $comments !== null ? $comments : Yii::t('app', 'Added via API (SSO)')
        ];
        $response = $this->api->post_enroll_to_product($user_id, $vec_input);

        // Update the model with last received data
        if ( $this->api->is_last_action_success() )
        {
            $vec_response = $this->api->get_response_body(true);
            dd($vec_response);

            if ( $this->is_debug )
            {
                Log::learnworlds_dev("LearnworldsComponent::enroll_to_product({$user_id}, {$product_id}, {$price}, {$product_type}) - Last action success");
                Log::learnworlds_dev(print_r($vec_response, true));
            }
        }
        else if ( $this->is_debug )
        {
            Log::learnworlds_dev("LearnworldsComponent::enroll_to_product({$user_id}, {$product_id}, {$price}, {$product_type}) - Last action error: ". print_r($response, true));
        }
    }
}
