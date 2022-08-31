<?php
/**
 * Migration class m220831_070819_learnworlds_schema
 *
 * @link http://www.dezero.es/
 */

use dz\db\Migration;
use dz\helpers\DateHelper;
use dz\helpers\StringHelper;

class m220831_070819_learnworlds_schema extends Migration
{
	/**
	 * This method contains the logic to be executed when applying this migration.
	 */
	public function up()
	{
		// Create "learnworlds_user" table
        // -------------------------------------------------------------------------
        $this->dropTableIfExists('learnworlds_user', true);

        $this->createTable('learnworlds_user', [
            'user_id' => $this->integer()->unsigned()->notNull(),
            'learnworlds_user_id' => $this->string(32)->notNull(),
            'email' => $this->string()->notNull(),
            'username' => $this->string()->notNull(),
            'response_json' => $this->text(),
            'last_sync_date' => $this->date()->notNull(),
            'last_sync_endpoint' => $this->string(128)->notNull(),
            'last_sso_url' => $this->string(),
            'last_sso_date' => $this->date(),
            'disable_date' => $this->date(),
            'disable_uid' => $this->integer()->unsigned(),
            'created_date' => $this->date()->notNull(),
            'created_uid' => $this->integer()->unsigned()->notNull(),
            'updated_date' => $this->date()->notNull(),
            'updated_uid' => $this->integer()->unsigned()->notNull(),
            'uuid' => $this->uuid(),
        ]);
    
        // Primary key (alternative method)
        $this->addPrimaryKey(null, 'learnworlds_user', 'user_id');

        // Create indexes
        $this->createIndex(null, 'learnworlds_user', ['learnworlds_user_id'], false);
        $this->createIndex(null, 'learnworlds_user', ['email'], false);

        // Create FOREIGN KEYS
        $this->addForeignKey(null, 'learnworlds_user', ['user_id'], 'user_users', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, 'learnworlds_user', ['disable_uid'], 'user_users', ['id'], 'SET NULL', null);
        $this->addForeignKey(null, 'learnworlds_user', ['created_uid'], 'user_users', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, 'learnworlds_user', ['updated_uid'], 'user_users', ['id'], 'CASCADE', null);


        // Create "learnworlds_course" table
        // -------------------------------------------------------------------------
        $this->dropTableIfExists('learnworlds_course', true);

        $this->createTable('learnworlds_course', [
            'learnworlds_course_id' => $this->string()->notNull(),
            'title' => $this->string()->notNull(),
            'price' => $this->float()->notNull()->defaultValue(0),
            'response_json' => $this->text(),
            'last_sync_date' => $this->date()->notNull(),
            'last_sync_endpoint' => $this->string(128)->notNull(),
            'disable_date' => $this->date(),
            'disable_uid' => $this->integer()->unsigned(),
            'created_date' => $this->date()->notNull(),
            'created_uid' => $this->integer()->unsigned()->notNull(),
            'updated_date' => $this->date()->notNull(),
            'updated_uid' => $this->integer()->unsigned()->notNull(),
            'uuid' => $this->uuid(),
        ]);

        // Primary key (alternative method)
        $this->addPrimaryKey(null, 'learnworlds_course', 'learnworlds_course_id');

        // Create FOREIGN KEYS
        $this->addForeignKey(null, 'learnworlds_course', ['disable_uid'], 'user_users', ['id'], 'SET NULL', null);
        $this->addForeignKey(null, 'learnworlds_course', ['created_uid'], 'user_users', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, 'learnworlds_course', ['updated_uid'], 'user_users', ['id'], 'CASCADE', null);


        // Create "learnworlds_course_user" table
        // -------------------------------------------------------------------------
        $this->dropTableIfExists('learnworlds_course_user', true);

        $this->createTable('learnworlds_course_user', [
            'learnworlds_course_id' => $this->string()->notNull(),
            'learnworlds_user_id' => $this->string(32)->notNull(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'created_date' => $this->date()->notNull(),
            'created_uid' => $this->integer()->unsigned()->notNull()
        ]);

        // Primary key (alternative method)
        $this->addPrimaryKey(null, 'learnworlds_course_user', ['learnworlds_course_id', 'learnworlds_user_id']);

        // Create FOREIGN KEYS
        $this->addForeignKey(null, 'learnworlds_course_user', ['learnworlds_course_id'], 'learnworlds_course', ['learnworlds_course_id'], 'CASCADE', null);
        $this->addForeignKey(null, 'learnworlds_course_user', ['learnworlds_user_id'], 'learnworlds_user', ['learnworlds_user_id'], 'CASCADE', null);
        $this->addForeignKey(null, 'learnworlds_course_user', ['user_id'], 'user_users', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, 'learnworlds_course_user', ['created_uid'], 'user_users', ['id'], 'CASCADE', null);

		return true;
	}


	/**
	 * This method contains the logic to be executed when removing this migration.
	 */
	public function down()
	{
		// $this->dropTable('learnworlds_user');
        // $this->dropTable('learnworlds_course');
        // $this->dropTable('learnworlds_course_user');
		return false;
	}
}

