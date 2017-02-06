<?php
use yii\db\Migration;

class m170201_000000_create_tables extends Migration
{
    public function init()
    {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function createTable($table, $columns, $options = null)
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        parent::createTable($table, $columns, $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->create_user();
    }

    private function create_user()
    {
        $this->createTable('user', [
            'id'         => $this->primaryKey()                    ->comment('ID'),
            'account'    => $this->string(20)->notNull()->unique() ->comment('アカウント名'),
            'name'       => $this->string(256)->notNull()          ->comment('ユーザ名'),
            'email'      => $this->string(256)->null()             ->comment('メールアドレス'),
            'status'     => $this->string(32)->notNull()           ->comment('ステータス'),
            'updated_at' => $this->timestamp()->null()             ->comment('更新日時'),
            'created_at' => $this->timestamp()->null()             ->comment('登録日時'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $tables = [
            'user',
        ];
        foreach ($tables as $table) {
            $this->dropTable($table);
        }
    }
}
