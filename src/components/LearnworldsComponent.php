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
use dzlab\learnworlds\models\LearnworldsCourse;
use dzlab\learnworlds\models\LearnworldsCourseUser;
use dzlab\learnworlds\models\LearnworldsSso;
use dzlab\learnworlds\models\LearnworldsUser;
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
     * Get last EHttpResponse object
     */
    public function get_response()
    {
        return $this->api->get_response();
    }


    /**
     * Process and return response data into an array
     */
    public function get_response_body($is_json = true)
    {
       return $this->api->get_response_body($is_json);
    }


    /**
     * Get last used endpoint
     */
    public function get_last_endpoint()
    {
        return $this->api->last_endpoint;
    }


    /**
     * Single Sign-on process.
     * Returns an unique URL to make the SSO automatically
     *
     * - API REST Endpoint "POST /sso"
     *
     * @see LearnworldsApi::post_sso()
     */
    public function sso($user_id, $redirect_url = null)
    {
        $user_model = User::findOne($user_id);
        if ( $user_model )
        {
            // Redirect URL will be the Learnworlds school URL
            if ( $redirect_url === null )
            {
                $redirect_url = $this->api->school_url;
            }

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

                if ( $this->is_debug )
                {
                    Log::learnworlds_dev("LearnworldsComponent::sso({$user_id}, {$redirect_url}) - Last action success");
                    Log::learnworlds_dev(print_r($vec_response, true));
                }

                // Create/update LearnworldsUser model
                if ( !empty($vec_response) && isset($vec_response['user_id']) && isset($vec_response['url']) && isset($vec_response['success']) && $vec_response['success'] === true )
                {
                    // Get LearnworldsUser model
                    $learnworlds_user_model = $this->get_user($user_id, $vec_response['user_id']);
                    if ( ! $learnworlds_user_model )
                    {
                        Log::learnworlds_error("LearnworldsComponent::sso({$user_id}, {$redirect_url}) - Learnworlds User model could not be loaded. Learnworlds ID = {$vec_response['user_id']}");
                    }
                    else
                    {
                        $learnworlds_sso_model = Yii::createObject(LearnworldsSso::class);
                        $learnworlds_sso_model->setAttributes([
                            'user_id'               => $user_id,
                            'learnworlds_user_id'   => $vec_response['user_id'],
                            'email'                 => $vec_input['email'],
                            'username'              => $vec_input['username'],
                            'redirect_url'          => $redirect_url,
                            'sso_url'               => $vec_response['url'],
                        ]);

                        if ( ! $learnworlds_sso_model->save() )
                        {
                            Log::save_model_error($learnworlds_sso_model);
                        }
                        else
                        {
                            // Save entity information (useful for logs)
                            $this->api->save_entity_info('LearnworldsSso', $learnworlds_sso_model->user_id);
                        }

                        // Return LearnworldsSso model
                        return $learnworlds_sso_model;
                    }
                }

                else if ( $this->is_debug )
                {
                    Log::learnworlds_dev("LearnworldsComponent::sso({$user_id}, {$redirect_url}) - Incorrect response: ". print_r($vec_response, true));
                }
            }
            else if ( $this->is_debug )
            {
                Log::learnworlds_dev("LearnworldsComponent::sso({$user_id}, {$redirect_url}) - Last action error: ". print_r($this->api->get_last_error(), true));
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
    public function get_user($user_id, $learnworlds_user_id)
    {
        $user_model = User::findOne($user_id);
        if ( $user_model )
        {
            // Send a "GET /v2/users/{id}" request
            $response = $this->api->get_user($learnworlds_user_id);

            // Update the model with last received data
            if ( $this->api->is_last_action_success() )
            {
                $vec_response = $this->api->get_response_body(true);
                // dd($vec_response);

                if ( $this->is_debug )
                {
                    Log::learnworlds_dev("LearnworldsComponent::get_user({$user_id}, {$learnworlds_user_id}) - Last action success");
                    Log::learnworlds_dev(print_r($vec_response, true));
                }

                // Create/update LearnworldsUser model
                if ( !empty($vec_response) && isset($vec_response['id']) && isset($vec_response['email']) && isset($vec_response['username']) )
                {
                    $learnworlds_user_model = LearnworldsUser::findOne($user_id);
                    if ( ! $learnworlds_user_model )
                    {
                        $learnworlds_user_model = Yii::createObject(LearnworldsUser::class);
                        $learnworlds_user_model->user_id = $user_id;
                    }

                    // Check if ID from Learnworlds has been changed for current user
                    else if ( $learnworlds_user_model->learnworlds_user_id !== $vec_response['id'] )
                    {
                        Log::learnworlds_warning("LearnworldsComponent::get_user({$user_id}, {$learnworlds_user_id}) - Learnworlds ID for USER #{$user_id} has been changed from {$learnworlds_user_id} to {$vec_response['id']}");
                    }

                    $learnworlds_user_model->setAttributes([
                        'learnworlds_user_id'   => $vec_response['id'],
                        'email'                 => $vec_response['email'],
                        'username'              => $vec_response['username'],
                        'response_json'         => Json::encode($vec_response),
                        'last_sync_date'        => time(),
                        'last_sync_endpoint'    => 'POST___'. $this->api->last_endpoint
                    ]);

                    if ( ! $learnworlds_user_model->save() )
                    {
                        Log::save_model_error($learnworlds_user_model);
                    }
                    else
                    {
                        // Save entity information (useful for logs)
                        $this->api->save_entity_info('LearnworldsUser', $learnworlds_user_model->user_id);
                    }

                    // Return LearnworldsUser model
                    return $learnworlds_user_model;
                }

                else if ( $this->is_debug )
                {
                    Log::learnworlds_dev("LearnworldsComponent::get_user({$user_id}, {$learnworlds_user_id}) - Incorrect response: ". print_r($vec_response, true));
                }
            }
            else if ( $this->is_debug )
            {
                Log::learnworlds_dev("LearnworldsComponent::get_user({$user_id}, {$learnworlds_user_id}) - Last action error: ". print_r($this->api->get_last_error(), true));
            }
        }
        else
        {
            Log::learnworlds_error("LearnworldsComponent::get_user({$user_id}, {$learnworlds_user_id}) - USER does not exist: {$user_id}");
        }

        return null;
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
            // dd($vec_response);

            if ( $this->is_debug )
            {
                Log::learnworlds_dev("LearnworldsComponent::get_course({$learnworlds_course_id}) - Last action success");
                Log::learnworlds_dev(print_r($vec_response, true));
            }

            // Create/update LearnworldsCourse model
            if ( !empty($vec_response) && isset($vec_response['id']) && isset($vec_response['title']) && isset($vec_response['final_price']) )
            {
                // Return model
                return $this->_save_course($vec_response);
            }
            else if ( $this->is_debug )
            {
                Log::learnworlds_dev("LearnworldsComponent::get_course({$learnworlds_course_id}) - Incorrect response: ". print_r($vec_response, true));
            }
        }
        else if ( $this->is_debug )
        {
            Log::learnworlds_dev("LearnworldsComponent::get_course({$learnworlds_course_id}) - Last action error: ". print_r($this->api->get_last_error(), true));
        }

        return null;
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
        $vec_course_models = [];

        // Send a "GET /v2/courses" request
        $response = $this->api->get_courses();

        // Update the model with last received data
        if ( $this->api->is_last_action_success() )
        {
            $vec_response = $this->api->get_response_body(true);
            // dd($vec_response);

            if ( $this->is_debug )
            {
                Log::learnworlds_dev("LearnworldsComponent::get_courses() - Last action success");
                // Log::learnworlds_dev(print_r($vec_response, true));
            }

            // Create/update courses
            if ( !empty($vec_response) && isset($vec_response['data']) && isset($vec_response['meta']) )
            {
                if ( !empty($vec_response['data']) )
                {
                    foreach ( $vec_response['data'] as $que_course_response )
                    {
                        $learnworlds_course_model = $this->_save_course($que_course_response);
                        if ( $learnworlds_course_model !== null )
                        {
                            $vec_course_models[$learnworlds_course_model->learnworlds_course_id] = $learnworlds_course_model;
                        }
                    }
                }
            }
            else if ( $this->is_debug )
            {
                Log::learnworlds_dev("LearnworldsComponent::get_courses() - Incorrect response: ". print_r($vec_response, true));
            }
        }
        else if ( $this->is_debug )
        {
            Log::learnworlds_dev("LearnworldsComponent::get_courses() - Last action error: ". print_r($this->api->get_last_error(), true));
        }

        return $vec_course_models;
    }


    /**
     * Get users enrolled to an course
     *
     * - API REST Endpoint "GET /v2/courses/{id}/users"
     *
     * @see https://learnworlds.dev/docs/api/148c48f13851f-get-all-users-per-course
     */
    public function get_users_per_course($learnworlds_course_id, $vec_input = [])
    {
        $vec_output = [];
        $learnworlds_course_model = LearnworldsCourse::findOne($learnworlds_course_id);
        if ( ! $learnworlds_course_model )
        {
            $learnworlds_course_model = $this->get_course($learnworlds_course_id);
        }

        if ( $learnworlds_course_model )
        {
            // Send a "GET /v2/courses/{id}/courses" request
            $response = $this->api->get_users_per_course($learnworlds_course_model->learnworlds_course_id, $vec_input);

            // Update the model with last received data
            if ( $this->api->is_last_action_success() )
            {
                $vec_response = $this->api->get_response_body(true);

                if ( $this->is_debug )
                {
                    Log::learnworlds_dev("LearnworldsComponent::get_users_per_course({$learnworlds_course_id}) - Last action success");
                    Log::learnworlds_dev(print_r($vec_response, true));
                }

                return $vec_response;

                /*
                // Create/update enrollments (LearnworldsCourseUser models)
                if ( !empty($vec_response) && isset($vec_response['data']) )
                {
                    if ( !empty($vec_response['data']) )
                    {

                    }
                }
                else if ( $this->is_debug )
                {
                    Log::learnworlds_dev("LearnworldsComponent::get_users_per_course({$learnworlds_course_id}) - Incorrect response: ". print_r($vec_response, true));
                }
                */
            }
            else if ( $this->is_debug )
            {
                Log::learnworlds_dev("LearnworldsComponent::get_users_per_course({$learnworlds_course_id}) - Last action error: ". print_r($this->api->get_last_error(), true));
            }
        }
        else
        {
            if ( ! $learnworlds_course_model )
            {
                Log::learnworlds_error("LearnworldsComponent::get_users_per_course({$learnworlds_course_id}) - LEARNWORLDS COURSE does not exist for course {$learnworlds_course_id}");
            }
        }

        return $vec_output;
    }


    /**
     * Enroll user to a course (product)
     *
     * - API REST Endpoint "POST /v2/users/{id}/enrollment"
     *
     * @see LearnworldsApi::post_enroll_to_product()
     */
    public function enroll_to_course($user_id, $learnworlds_course_id, $comments = null)
    {
        $user_model = User::findOne($user_id);
        $learnworlds_user_model = LearnworldsUser::findOne($user_id);
        $learnworlds_course_model = LearnworldsCourse::findOne($learnworlds_course_id);
        if ( ! $learnworlds_course_model )
        {
            $learnworlds_course_model = $this->get_course($learnworlds_course_id);
        }

        if ( $user_model && $learnworlds_user_model && $learnworlds_course_model )
        {
            // Send a "POST /v2/users/{id}/enrollment" request
            $vec_input = [
                'productId'     => $learnworlds_course_id,
                'productType'   => 'course',
                'price'         => $learnworlds_course_model->price,
                'justification' => $comments !== null ? $comments : Yii::t('app', 'Added via API (SSO)')
            ];
            $response = $this->api->post_enroll_to_product($learnworlds_user_model->learnworlds_user_id, $vec_input);

            // Update the model with last received data
            if ( $this->api->is_last_action_success() )
            {
                $vec_response = $this->api->get_response_body(true);
                // dd($vec_response);

                if ( $this->is_debug )
                {
                    Log::learnworlds_dev("LearnworldsComponent::enroll_to_course({$user_id}, {$learnworlds_course_id}) - Last action success");
                    Log::learnworlds_dev(print_r($vec_response, true));
                }

                // Save LearnworldsCourseUser model
                if ( !empty($vec_response) && isset($vec_response['success']) && $vec_response['success'] === true )
                {
                    // Return LearnworldsCourseUser model
                    return $this->_save_enroll($learnworlds_course_model, $learnworlds_user_model);
                }
                else
                {
                    Log::learnworlds_dev("LearnworldsComponent::enroll_to_course({$user_id}, {$learnworlds_course_id}) - Incorrect response: ". print_r($vec_response, true));
                }
            }
            else if ( $this->is_debug )
            {
                Log::learnworlds_dev("LearnworldsComponent::enroll_to_course({$user_id}, {$learnworlds_course_id}) - Last action error: ". print_r($this->api->get_last_error(), true));
            }
        }
        else
        {
            if ( ! $user_model )
            {
                Log::learnworlds_error("LearnworldsComponent::enroll_to_course({$user_id}, {$learnworlds_course_id}) - USER does not exist: {$user_id}");
            }

            if ( ! $learnworlds_user_model )
            {
                Log::learnworlds_error("LearnworldsComponent::enroll_to_course({$user_id}, {$learnworlds_course_id}) - LEARNWORLDS USER does not exist for user #{$user_id}");
            }

            if ( ! $learnworlds_course_model )
            {
                Log::learnworlds_error("LearnworldsComponent::enroll_to_course({$user_id}, {$learnworlds_course_id}) - LEARNWORLDS COURSE does not exist with id '{$learnworlds_course_id}'");
            }
        }

        return null;
    }


    /**
     * Unenroll user from product
     *
     * - API REST Endpoint "DELETE /v2/users/{id}/enrollment"
     *
     * @see LearnworldsApi::delete_unenroll_from_product()
     */
    public function unenroll_from_course($user_id, $learnworlds_course_id)
    {
        $user_model = User::findOne($user_id);
        $learnworlds_user_model = LearnworldsUser::findOne($user_id);
        $learnworlds_course_model = LearnworldsCourse::findOne($learnworlds_course_id);
        if ( ! $learnworlds_course_model )
        {
            $learnworlds_course_model = $this->get_course($learnworlds_course_id);
        }

        if ( $user_model && $learnworlds_user_model && $learnworlds_course_model )
        {
             // Send a "DELETE /v2/users/{id}/enrollment" request
            $vec_input = [
                'productId'     => $learnworlds_course_id,
                'productType'   => 'course',
            ];
            $response = $this->api->delete_unenroll_from_product($learnworlds_user_model->learnworlds_user_id, $vec_input);

            // Update the model with last received data
            if ( $this->api->is_last_action_success() )
            {
                $vec_response = $this->api->get_response_body(true);

                if ( $this->is_debug )
                {
                    Log::learnworlds_dev("LearnworldsComponent::unenroll_from_course({$user_id}, {$learnworlds_course_id}) - Last action success");
                    Log::learnworlds_dev(print_r($vec_response, true));
                }

                // Delete LearnworldsCourseUser model
                if ( !empty($vec_response) && isset($vec_response['success']) && $vec_response['success'] === true )
                {
                    // Return LearnworldsCourseUser model
                    return _save_enroll($learnworlds_course_model, $learnworlds_user_model);
                }
                else
                {
                    Log::learnworlds_dev("LearnworldsComponent::unenroll_from_course({$user_id}, {$learnworlds_course_id}) - Incorrect response: ". print_r($vec_response, true));
                }
            }
            else if ( $this->is_debug )
            {
                Log::learnworlds_dev("LearnworldsComponent::unenroll_from_course({$user_id}, {$learnworlds_course_id}) - Last action error: ". print_r($this->api->get_last_error(), true));
            }
        }
        else
        {
            if ( ! $user_model )
            {
                Log::learnworlds_error("LearnworldsComponent::unenroll_from_course({$user_id}, {$learnworlds_course_id}) - USER does not exist: {$user_id}");
            }

            if ( ! $learnworlds_user_model )
            {
                Log::learnworlds_error("LearnworldsComponent::unenroll_from_course({$user_id}, {$learnworlds_course_id}) - LEARNWORLDS USER does not exist for user #{$user_id}");
            }

            if ( ! $learnworlds_course_model )
            {
                Log::learnworlds_error("LearnworldsComponent::unenroll_from_course({$user_id}, {$learnworlds_course_id}) - LEARNWORLDS COURSE does not exist with id '{$learnworlds_course_id}'");
            }
        }

        return null;
    }


    /**
     * Get products (couses enrollments) of user.
     *
     * - API REST Endpoint "GET /v2/users/{id}/products"
     *
     * @see https://learnworlds.dev/docs/api/7e63f13919cdd-get-courses-enrollments-of-user
     */
    public function get_user_enrollments($user_id)
    {
        $vec_output = [];
        $user_model = User::findOne($user_id);
        $learnworlds_user_model = LearnworldsUser::findOne($user_id);
        if ( $user_model && $learnworlds_user_model )
        {
            // Send a "GET /v2/users/{id}/courses" request
            $response = $this->api->get_enrollments($learnworlds_user_model->learnworlds_user_id);

            // Update the model with last received data
            if ( $this->api->is_last_action_success() )
            {
                $vec_response = $this->api->get_response_body(true);
                // dd($vec_response);

                if ( $this->is_debug )
                {
                    Log::learnworlds_dev("LearnworldsComponent::get_user_enrollments({$user_id}) - Last action success");
                    Log::learnworlds_dev(print_r($vec_response, true));
                }

                // Create/update enrollments (LearnworldsCourseUser models)
                if ( !empty($vec_response) && isset($vec_response['data']) )
                {
                    if ( !empty($vec_response['data']) )
                    {
                        foreach ( $vec_response['data'] as $que_product_response )
                        {
                            // Check if course exists
                            if ( isset($que_product_response['id']) && isset($que_product_response['type']) && $que_product_response['type'] === 'course' )
                            {
                                $learnworlds_course_model = $this->get_course($que_product_response['id']);
                                if ( $learnworlds_course_model )
                                {
                                    $learnworlds_course_user_model = $this->_save_enroll($learnworlds_course_model, $learnworlds_user_model);
                                    if ( $learnworlds_course_user_model !== null )
                                    {
                                        $vec_output[$learnworlds_course_model->learnworlds_course_id] = $learnworlds_course_user_model;
                                    }
                                }
                                else
                                {
                                    $course_id = $que_product_response['id'];
                                    Log::learnworlds_error("LearnworldsComponent::get_user_enrollments({$user_id}) - LEARNWORLDS COURSE does not exist for course {$course_id}");
                                }
                            }
                            else
                            {
                                Log::learnworlds_dev("LearnworldsComponent::get_user_enrollments({$user_id}) - Incorrect response for a course item: ". print_r($que_product_response, true));
                            }
                        }
                    }
                }
                else if ( $this->is_debug )
                {
                    Log::learnworlds_dev("LearnworldsComponent::get_user_enrollments({$user_id}) - Incorrect response: ". print_r($vec_response, true));
                }
            }
            else if ( $this->is_debug )
            {
                Log::learnworlds_dev("LearnworldsComponent::get_user_enrollments({$user_id}) - Last action error: ". print_r($this->api->get_last_error(), true));
            }
        }
        else
        {
            if ( ! $user_model )
            {
                Log::learnworlds_error("LearnworldsComponent::get_user_enrollments({$user_id}) - USER does not exist: {$user_id}");
            }

            if ( ! $learnworlds_user_model )
            {
                Log::learnworlds_error("LearnworldsComponent::get_user_enrollments({$user_id}) - LEARNWORLDS USER does not exist for user #{$user_id}");
            }
        }

        return $vec_output;
    }


    /**
     * Check if an user is enrolled to a Learnworlds course
     */
    public function is_user_enrolled($user_id, $learnworlds_course_id)
    {
        // Get courses (product enrollments)
        $vec_user_enrollments = Yii::app()->learnworlds->get_user_enrollments($user_id);

        // Clear last error, if it has not been found any course
        $this->api->clear_last_error();

        return !empty($vec_user_enrollments) && isset($vec_user_enrollments[$learnworlds_course_id]);
    }


    /**
     * Create or update a LearnworldsCourse model
     */
    private function _save_course($vec_data)
    {
        // Create/update LearnworldsCourse model
        if ( !empty($vec_data) && isset($vec_data['id']) && isset($vec_data['title']) && isset($vec_data['final_price']) )
        {
            $learnworlds_course_model = LearnworldsCourse::findOne($vec_data['id']);
            if ( ! $learnworlds_course_model )
            {
                $learnworlds_course_model = Yii::createObject(LearnworldsCourse::class);
                $learnworlds_course_model->learnworlds_course_id = $vec_data['id'];
            }
            $learnworlds_course_model->setAttributes([
                'title'                 => $vec_data['title'],
                'price'                 => $vec_data['final_price'],
                'response_json'         => Json::encode($vec_data),
                'last_sync_date'        => time(),
                'last_sync_endpoint'    => 'POST___'. $this->api->last_endpoint
            ]);

            if ( ! $learnworlds_course_model->save() )
            {
                Log::save_model_error($learnworlds_course_model);
            }
            else
            {
                // Save entity information (useful for logs)
                $this->api->save_entity_info('LearnworldsCourse', $learnworlds_course_model->learnworlds_course_id);
            }

            // Return LearnworldsUser model
            return $learnworlds_course_model;
        }
        else if ( $this->is_debug )
        {
            Log::learnworlds_dev("LearnworldsComponent::_save_course() for course with id '{$learnworlds_course_id}'' - Incorrect response: ". print_r($vec_data, true));
        }

        return null;
    }


    /**
     * Create or update an enrollment (LearnworldsCourseUser model)
     */
    private function _save_enroll($learnworlds_course_model, $learnworlds_user_model)
    {
        $learnworlds_course_user_model = LearnworldsCourseUser::get()
            ->where([
                'learnworlds_course_id' => $learnworlds_course_model->learnworlds_course_id,
                'learnworlds_user_id'   => $learnworlds_user_model->learnworlds_user_id,
            ])
            ->one();

        if ( ! $learnworlds_course_user_model )
        {
            $learnworlds_course_user_model = Yii::createObject(LearnworldsCourseUser::class);
            $learnworlds_course_user_model->setAttributes([
                'learnworlds_course_id' => $learnworlds_course_model->learnworlds_course_id,
                'learnworlds_user_id'   => $learnworlds_user_model->learnworlds_user_id,
                'user_id'               => $learnworlds_user_model->user_id
            ]);

            if ( ! $learnworlds_course_user_model->save() )
            {
                Log::save_model_error($learnworlds_course_user_model);
            }
            else
            {
                // Save entity information (useful for logs)
                $this->api->save_entity_info('LearnworldsCourseUser', $learnworlds_user_model->user_id);
            }
        }

        return $learnworlds_course_user_model;
    }
}
