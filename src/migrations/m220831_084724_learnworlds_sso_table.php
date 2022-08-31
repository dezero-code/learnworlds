<?php
/**
 * Migration class m220831_084724_learnworlds_sso_table
 *
 * @link http://www.dezero.es/
 */

use dz\db\Migration;
use dz\helpers\DateHelper;
use dz\helpers\StringHelper;

class m220831_084724_learnworlds_sso_table extends Migration
{
	/**
	 * This method contains the logic to be executed when applying this migration.
	 */
	public function up()
	{
		// Create "learnworlds_sso" table
        // -------------------------------------------------------------------------
        $this->dropTableIfExists('learnworlds_sso', true);

        $this->createTable('learnworlds_sso', [
            'learnworlds_sso_id' => $this->primaryKey(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'learnworlds_user_id' => $this->string(32)->notNull(),
            'email' => $this->string()->notNull(),
            'username' => $this->string()->notNull(),
            'redirect_url' => $this->string()->notNull(),
            'sso_url' => $this->string()->notNull(),
            'created_date' => $this->date()->notNull(),
            'created_uid' => $this->integer()->unsigned()->notNull(),
            'updated_date' => $this->date()->notNull(),
            'updated_uid' => $this->integer()->unsigned()->notNull(),
        ]);

        // Create indexes
        $this->createIndex(null, 'learnworlds_sso', ['learnworlds_user_id'], false);
        $this->createIndex(null, 'learnworlds_sso', ['email'], false);

        // Create FOREIGN KEYS
        $this->addForeignKey(null, 'learnworlds_sso', ['user_id'], 'user_users', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, 'learnworlds_sso', ['created_uid'], 'user_users', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, 'learnworlds_sso', ['updated_uid'], 'user_users', ['id'], 'CASCADE', null);

		return true;
	}


	/**
	 * This method contains the logic to be executed when removing this migration.
	 */
	public function down()
	{
		// $this->dropTable('learnworlds_sso');
		return false;
	}
}

