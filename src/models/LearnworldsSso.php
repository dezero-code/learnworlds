<?php
/**
 * @package dzlab\learnworlds\models
 */

namespace dzlab\learnworlds\models;

use dz\db\DbCriteria;
use dz\helpers\DateHelper;
use dz\helpers\Log;
use dz\helpers\StringHelper;
use dzlab\learnworlds\models\_base\LearnworldsSso as BaseLearnworldsSso;
use dzlab\learnworlds\models\LearnworldsUser;
use user\models\User;
use Yii;

/**
 * LearnworldsSso model class for "learnworlds_sso" database table
 *
 * Columns in table "learnworlds_sso" available as properties of the model,
 * followed by relations of table "learnworlds_sso" available as properties of the model.
 *
 * -------------------------------------------------------------------------
 * COLUMN FIELDS
 * -------------------------------------------------------------------------
 * @property integer $learnworlds_sso_id
 * @property integer $user_id
 * @property string $learnworlds_user_id
 * @property string $email
 * @property string $username
 * @property string $redirect_url
 * @property string $sso_url
 * @property integer $created_date
 * @property integer $created_uid
 * @property integer $updated_date
 * @property integer $updated_uid
 *
 * -------------------------------------------------------------------------
 * RELATIONS
 * -------------------------------------------------------------------------
 * @property mixed $createdUser
 * @property mixed $updatedUser
 * @property mixed $user
 */
class LearnworldsSso extends BaseLearnworldsSso
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
			['user_id, learnworlds_user_id, email, username, redirect_url, sso_url, created_date, created_uid, updated_date, updated_uid', 'required'],
			['user_id, created_date, created_uid, updated_date, updated_uid', 'numerical', 'integerOnly' => true],
			['learnworlds_user_id', 'length', 'max'=> 32],
			['email, username, redirect_url, sso_url', 'length', 'max'=> 255],
			['learnworlds_sso_id, user_id, learnworlds_user_id, email, username, redirect_url, sso_url, created_date, created_uid, updated_date, updated_uid', 'safe', 'on' => 'search'],
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
			'updatedUser' => [self::BELONGS_TO, User::class, ['updated_uid' => 'id']],
			'user' => [self::BELONGS_TO, User::class, ['user_id' => 'id']],

            // Custom relations
            'learnworldsUser' => [self::BELONGS_TO, LearnworldsUser::class, ['learnworlds_user_id' => 'learnworlds_user_id']],
		];
	}


    /**
     * External behaviors
     */
    /*
    public function behaviors()
    {
        return \CMap::mergeArray([
            // Date format
            'DateBehavior' => [
                'class' => '\dz\behaviors\DateBehavior',
                'columns' => [
                    'disable_date' => 'd/m/Y - H:i'
                ],
            ],

        ], parent::behaviors());
    }
    */

	
	/**
	 * Returns the attribute labels
	 */
	public function attributeLabels()
	{
		return [
			'learnworlds_sso_id' => Yii::t('app', 'Learnworlds Sso'),
			'user_id' => null,
			'learnworlds_user_id' => Yii::t('app', 'Learnworlds User'),
			'email' => Yii::t('app', 'Email'),
			'username' => Yii::t('app', 'Username'),
			'redirect_url' => Yii::t('app', 'Redirect Url'),
			'sso_url' => Yii::t('app', 'Sso Url'),
			'created_date' => Yii::t('app', 'Created Date'),
			'created_uid' => null,
			'updated_date' => Yii::t('app', 'Updated Date'),
			'updated_uid' => null,
			'createdUser' => null,
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

        $criteria->compare('t.learnworlds_sso_id', $this->learnworlds_sso_id);
        $criteria->compare('t.learnworlds_user_id', $this->learnworlds_user_id, true);
        $criteria->compare('t.email', $this->email, true);
        $criteria->compare('t.username', $this->username, true);
        $criteria->compare('t.redirect_url', $this->redirect_url, true);
        $criteria->compare('t.sso_url', $this->sso_url, true);
        $criteria->compare('t.created_date', $this->created_date);
        $criteria->compare('t.updated_date', $this->updated_date);

        return new \CActiveDataProvider($this, [
            'criteria' => $criteria,
            'pagination' => ['pageSize' => 30],
            'sort' => ['defaultOrder' => ['learnworlds_sso_id' => true]]
        ]);
    }


    /**
     * LearnworldsSso models list
     * 
     * @return array
     */
    public function learnworldssso_list($list_id = '')
    {
        $vec_output = [];

        $criteria = new DbCriteria;
        $criteria->select = ['learnworlds_sso_id', 'learnworlds_user_id'];
        // $criteria->order = 't.id ASC';
        // $criteria->condition = '';
        
        $vec_models = LearnworldsSso::model()->findAll($criteria);
        if ( !empty($vec_models) )
        {
            foreach ( $vec_models as $que_model )
            {
                $vec_output[$que_model->getAttribute('learnworlds_sso_id')] = $que_model->title();
            }
        }

        return $vec_output;
    }
}
