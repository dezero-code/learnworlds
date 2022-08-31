<?php
/**
 * @package dzlab\learnworlds\src\models 
 */

namespace dzlab\learnworlds\models;

use dz\db\DbCriteria;
use dz\helpers\DateHelper;
use dz\helpers\Log;
use dz\helpers\StringHelper;
use dz\modules\api\models\LogApi;
use dzlab\learnworlds\models\_base\LearnworldsUser as BaseLearnworldsUser;
use dzlab\learnworlds\models\LearnworldsCourseUser;
use user\models\User;
use Yii;

/**
 * LearnworldsUser model class for "learnworlds_user" database table
 *
 * Columns in table "learnworlds_user" available as properties of the model,
 * followed by relations of table "learnworlds_user" available as properties of the model.
 *
 * -------------------------------------------------------------------------
 * COLUMN FIELDS
 * -------------------------------------------------------------------------
 * @property integer $user_id
 * @property string $learnworlds_user_id
 * @property string $email
 * @property string $username
 * @property string $response_json
 * @property integer $last_sync_date
 * @property string $last_sync_endpoint
 * @property string $last_sso_url
 * @property integer $last_sso_date
 * @property integer $disable_date
 * @property integer $disable_uid
 * @property integer $created_date
 * @property integer $created_uid
 * @property integer $updated_date
 * @property integer $updated_uid
 * @property string $uuid
 *
 * -------------------------------------------------------------------------
 * RELATIONS
 * -------------------------------------------------------------------------
 * @property mixed $createdUser
 * @property mixed $disableUser
 * @property mixed $updatedUser
 * @property mixed $user
 */
class LearnworldsUser extends BaseLearnworldsUser
{
	/**
	 * Constructor
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	
	/**
	 * Returns the validation rules for attributes
	 */
	public function rules()
	{
		return [
			['user_id, learnworlds_user_id, email, username, last_sync_date, last_sync_endpoint, created_date, created_uid, updated_date, updated_uid', 'required'],
			['user_id, last_sync_date, last_sso_date, disable_date, disable_uid, created_date, created_uid, updated_date, updated_uid', 'numerical', 'integerOnly' => true],
			['learnworlds_user_id', 'length', 'max'=> 32],
			['email, username, last_sso_url', 'length', 'max'=> 255],
			['last_sync_endpoint', 'length', 'max'=> 128],
			['uuid', 'length', 'max'=> 36],
			['response_json, last_sso_url, last_sso_date, disable_date, disable_uid, uuid', 'default', 'setOnEmpty' => true, 'value' => null],
			['response_json', 'safe'],
			['user_id, learnworlds_user_id, email, username, response_json, last_sync_date, last_sync_endpoint, last_sso_url, last_sso_date, disable_date, disable_uid, created_date, created_uid, updated_date, updated_uid, uuid', 'safe', 'on' => 'search'],
		];
	}
	

	/**
	 * Define relations with other objects
	 *
	 * There are four types of relations that may exist between two active record objects:
	 *   - BELONGS_TO: e.g. a member belongs to a team;
	 *   - HAS_ONE: e.g. a member has at most one profile;
	 *   - HAS_MANY: e.g. a team has many members;
	 *   - MANY_MANY: e.g. a member has many skills and a skill belongs to a member.
	 */
	public function relations()
	{
		return [
			'createdUser' => [self::BELONGS_TO, User::class, ['created_uid' => 'id']],
			'disableUser' => [self::BELONGS_TO, User::class, ['disable_uid' => 'id']],
			'updatedUser' => [self::BELONGS_TO, User::class, ['updated_uid' => 'id']],
			'user' => [self::BELONGS_TO, User::class, ['user_id' => 'id']],

            // Custom relations
            'courses' => [self::HAS_MANY, LearnworldsCourseUser::class, ['learnworlds_user_id' => 'learnworlds_user_id']],
            'logsApi' => [self::HAS_MANY, LogApi::class, ['entity_id' => 'user_id'], 'condition' => 'logsApi.entity_type = "LearnworldsUser"', 'order' => 'logsApi.created_date DESC'],
		];
	}


    /**
     * External behaviors
     */
    public function behaviors()
    {
        return \CMap::mergeArray([
            // Date format
            'DateBehavior' => [
                'class' => '\dz\behaviors\DateBehavior',
                'columns' => [
                    'last_sync_date' => 'd/m/Y - H:i',
                    'disable_date' => 'd/m/Y - H:i'
                ],
            ],

        ], parent::behaviors());
    }

	
	/**
	 * Returns the attribute labels
	 */
	public function attributeLabels()
	{
		return [
			'user_id' => null,
			'learnworlds_user_id' => Yii::t('app', 'Learnworlds User'),
			'email' => Yii::t('app', 'Email'),
			'username' => Yii::t('app', 'Username'),
			'response_json' => Yii::t('app', 'Response Json'),
			'last_sync_date' => Yii::t('app', 'Last Sync Date'),
			'last_sync_endpoint' => Yii::t('app', 'Last Sync Endpoint'),
			'last_sso_url' => Yii::t('app', 'Last Sso Url'),
			'last_sso_date' => Yii::t('app', 'Last Sso Date'),
			'disable_date' => Yii::t('app', 'Disable Date'),
			'disable_uid' => null,
			'created_date' => Yii::t('app', 'Created Date'),
			'created_uid' => null,
			'updated_date' => Yii::t('app', 'Updated Date'),
			'updated_uid' => null,
			'uuid' => Yii::t('app', 'Uuid'),
			'createdUser' => null,
			'disableUser' => null,
			'updatedUser' => null,
			'user' => null,
		];
	}


    /**
     * Generate an ActiveDataProvider for search form of this model
     *
     * Used in CGridView
     */
    public function search()
    {
        $criteria = new DbCriteria;
        
        $criteria->with = [];
        // $criteria->together = true;

        $criteria->compare('t.learnworlds_user_id', $this->learnworlds_user_id, true);
        $criteria->compare('t.email', $this->email, true);
        $criteria->compare('t.username', $this->username, true);
        $criteria->compare('t.response_json', $this->response_json, true);
        $criteria->compare('t.last_sync_date', $this->last_sync_date);
        $criteria->compare('t.last_sync_endpoint', $this->last_sync_endpoint, true);
        $criteria->compare('t.last_sso_url', $this->last_sso_url, true);
        $criteria->compare('t.last_sso_date', $this->last_sso_date);
        $criteria->compare('t.disable_date', $this->disable_date);
        $criteria->compare('t.created_date', $this->created_date);
        $criteria->compare('t.updated_date', $this->updated_date);
        $criteria->compare('t.uuid', $this->uuid, true);

        return new \CActiveDataProvider($this, [
            'criteria' => $criteria,
            'pagination' => ['pageSize' => 30],
            'sort' => ['defaultOrder' => ['user_id' => true]]
        ]);
    }


    /**
     * LearnworldsUser models list
     * 
     * @return array
     */
    public function learnworldsuser_list($list_id = '')
    {
        $vec_output = [];

        $criteria = new DbCriteria;
        $criteria->select = ['user_id', 'learnworlds_user_id'];
        // $criteria->order = 't.id ASC';
        // $criteria->condition = '';
        
        $vec_models = LearnworldsUser::model()->findAll($criteria);
        if ( !empty($vec_models) )
        {
            foreach ( $vec_models as $que_model )
            {
                $vec_output[$que_model->getAttribute('user_id')] = $que_model->title();
            }
        }

        return $vec_output;
    }


    /**
     * Return last LogApi model. It can be filtered by request_endpoint
     */
    public function get_last_log_api_model($request_endpoint = null)
    {
        $vec_conditions = [
            'api_name'      => 'learnworlds',
            'entity_id'     => $this->user_id,
            'entity_type'   => 'LearnworldsUser'
        ];

        // Filter by request_endpoint?
        if ( $request_endpoint !== null )
        {
            $vec_conditions['request_endpoint'] = $request_endpoint;
        }

        return LogApi::get()
            ->where($vec_conditions)
            ->orderBy('created_date DESC')
            ->limit(1)
            ->one();
    }


    /**
     * Find a LearnworldsUser model by "learnworlds_user_id" attribute
     */
    public function findByLearnworldsId($learnworlds_user_id)
    {
        return self::get()->where(['learnworlds_user_id' => $learnworlds_user_id])->one();
    }


    /**
     * Title used for this model
     */
    public function title()
    {
        return $this->email;
    }
}
