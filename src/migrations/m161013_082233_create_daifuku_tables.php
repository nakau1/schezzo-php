<?php

use yii\db\Migration;

class m161013_082233_create_daifuku_tables extends Migration
{
    public function init()
    {
        parent::init();
    }

    public function createTable($table, $columns, $options = null)
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        parent::createTable($table, $columns, $tableOptions);
    }

    public function up()
    {
        $this->create_pollet_user();
        $this->create_charge_source_code();
        $this->create_information();
        $this->create_register_campaign_point_percentage();
        $this->create_point_site_token();
        $this->create_point_site_api();
        $this->create_push_notification_token();
        $this->create_charge_request_history();
        $this->create_charge_error_history();
        $this->create_batch_management();
        $this->create_push_information_opening();
        $this->create_inquiry();
        $this->create_card_value_cache();
        $this->create_admin_user();
        $this->create_inquiry_reply();
        $this->create_monthly_trading_history_cache();
        $this->create_reception();
    }

    private function create_pollet_user()
    {
        $this->createTable('pollet_user', [
            'id'                   => "INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT comment 'polletユーザーID'",
            'user_code_secret'     => $this->string(64)->notNull()->unique()->defaultValue(null)->comment('ユーザーコード'),
            'cedyna_id'            => "BIGINT(16) UNSIGNED ZEROFILL NULL UNIQUE comment 'セディナカード会員番号'",
            'encrypted_password'   => $this->text()->null()                                     ->comment('暗号化されたパスワード'),
            'mail_address'         => $this->string(256)->null()                                ->comment('メールアドレス'),
            'registration_status'  => $this->string(35)->notNull()                              ->comment('登録状態'),
            'balance_at_charge'    => $this->integer()->notNull()->defaultValue(0)              ->comment('チャージ時の残高'),
            'updated_at'           => $this->timestamp()->null()                                ->comment('更新日時'),
            'created_at'           => $this->timestamp()->null()                                ->comment('登録日時'),
        ]);
    }

    private function create_charge_source_code()
    {
        $this->createTable('charge_source', [
            'id'                          => $this->primaryKey(),
            'charge_source_code'          => $this->string(10)->notNull()->unique(),
            'api_key'                     => $this->string(256)->comment('polletから提供するAPIキー'),
            'site_name'                   => $this->string(50)->notNull()->unique(),
            'min_value'                   => $this->integer()->notNull(),
            'card_issue_fee'              => $this->smallInteger()->notNull()->defaultValue(0),
            'url'                         => $this->string(256)->notNull(),
            'icon_image_url'              => $this->string(256)->null(),
            'denomination'                => $this->string(16)->notNull()->defaultValue('pt'),
            'introduce_charge_rate_point' => $this->integer()->notNull(),
            'introduce_charge_rate_price' => $this->integer()->notNull(),
            'description'                 => $this->text()->notNull(),
            'auth_url'                    => $this->string(256)->null(),
            'publishing_status'           => $this->string(35)->notNull(),
            'cooperation_type'            => $this->string(35)->notNull(),
            'updated_at'                  => $this->timestamp()->null(),
            'created_at'                  => $this->timestamp()->null(),
        ]);
    }

    private function create_information()
    {
        $this->createTable('information', [
            'id'                => $this->primaryKey(),
            'heading'           => $this->string(50)->notNull(),
            'body'              => $this->text()->notNull(),
            'begin_date'        => $this->timestamp()->null(),
            'end_date'          => $this->timestamp()->null(),
            'sends_push'        => $this->smallInteger(1)->notNull()->defaultValue(false),
            'is_important'      => $this->smallInteger(1)->notNull()->defaultValue(false),
            'publishing_status' => $this->string(35)->notNull(),
            'updated_at'        => $this->timestamp()->null(),
            'created_at'        => $this->timestamp()->null(),
        ]);
    }

    private function create_register_campaign_point_percentage()
    {
        $this->createTable('register_campaign_point_percentage', [
            'id'                => $this->primaryKey(),
            'period'            => $this->integer()->notNull(),
            'point_rate'        => $this->decimal(4, 1)->notNull(),
            'begin_date'        => $this->timestamp()->null(),
            'end_date'          => $this->timestamp()->null(),
            'publishing_status' => $this->string(35)->notNull(),
            'updated_at'        => $this->timestamp()->null(),
            'created_at'        => $this->timestamp()->null(),
        ]);
    }

    private function create_point_site_token()
    {
        $this->createTable('point_site_token', [
            'id'                 => $this->primaryKey(),
            'pollet_user_id'     => "INT(11) UNSIGNED NOT NULL comment 'polletユーザーID'",
            'charge_source_code' => $this->string(10)->notNull(),
            'token'              => $this->string(256)->notNull(),
            'updated_at'         => $this->timestamp()->null(),
            'created_at'         => $this->timestamp()->null(),
        ]);
        // creates unique index for column `pollet_id` and `charge_source_code`
        $this->createIndex(
            'idx-point_site_token-pollet_id-and-charge_source_code',
            'point_site_token',
            ['pollet_user_id', 'charge_source_code'],
            true
        );

        // creates index for column `pollet_id`
        $this->createIndex(
            'idx-point_site_token-pollet_id',
            'point_site_token',
            'pollet_user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-point_site_token-pollet_id',
            'point_site_token',
            'pollet_user_id',
            'pollet_user',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // creates index for column `charge_source_code`
        $this->createIndex(
            'idx-point_site_token-charge_source_code',
            'point_site_token',
            'charge_source_code'
        );

        // add foreign key for table `charge_source`
        $this->addForeignKey(
            'fk-point_site_token-charge_source_code',
            'point_site_token',
            'charge_source_code',
            'charge_source',
            'charge_source_code',
            'CASCADE',
            'CASCADE'
        );
    }

    private function create_point_site_api()
    {
        $this->createTable('point_site_api', [
            'id'                 => $this->primaryKey(),
            'charge_source_code' => $this->string(10)->notNull(),
            'api_name'           => $this->string(30)->notNull(),
            'url'                => $this->string(256)->notNull(),
            'publishing_status'  => $this->string(35)->notNull(),
            'updated_at'         => $this->timestamp()->null(),
            'created_at'         => $this->timestamp()->null(),
        ]);
        // creates unique index for column `charge_source_code` and `api_name`
        $this->createIndex(
            'idx-point_site_api-charge_source_code-and-api_name',
            'point_site_api',
            ['charge_source_code', 'api_name'],
            true
        );

        // creates index for column `charge_source_code`
        $this->createIndex(
            'idx-point_site_api-charge_source_code',
            'point_site_api',
            'charge_source_code'
        );

        // add foreign key for table `charge_source`
        $this->addForeignKey(
            'fk-point_site_api-charge_source_code',
            'point_site_api',
            'charge_source_code',
            'charge_source',
            'charge_source_code',
            'CASCADE',
            'CASCADE'
        );
    }

    private function create_push_notification_token()
    {
        $this->createTable('push_notification_token', [
            'id'             => $this->primaryKey(),
            'pollet_user_id' => "INT(11) UNSIGNED NOT NULL comment 'polletユーザーID'",
            'device_id'      => $this->string(256)->notNull(),
            'token'          => $this->text()->notNull(),
            'platform'       => $this->string(20)->notNull(),
            'is_active'      => $this->smallInteger(1)->notNull()->defaultValue(true),
            'updated_at'     => $this->timestamp()->null(),
            'created_at'     => $this->timestamp()->null(),
        ]);

        // creates index for column `pollet_id`
        $this->createIndex(
            'idx-push_notification_token-pollet_id',
            'push_notification_token',
            'pollet_user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-push_notification_token-pollet_id',
            'push_notification_token',
            'pollet_user_id',
            'pollet_user',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    private function create_charge_request_history()
    {
        $this->createTable('charge_request_history', [
            'id'                 => $this->primaryKey()                            ->comment('ID'),
            'pollet_user_id'     => "INT(11) UNSIGNED NOT NULL comment 'polletユーザーID'",
            'charge_source_code' => $this->string(10)  ->notNull()                 ->comment('チャージ元コード'),
            'charge_value'       => $this->integer()   ->notNull()                 ->comment('チャージ額'),
            'exchange_value'     => $this->integer()   ->notNull()->defaultValue(0)->comment('ポイントサイト交換額'),
            'cause'              => $this->string(100) ->null()                    ->comment('チャージ理由'),
            'processing_status'  => $this->string(35)  ->notNull()                 ->comment('処理状態'),
            'updated_at'         => $this->timestamp() ->null()                    ->comment('更新日時'),
            'created_at'         => $this->timestamp() ->null()                    ->comment('作成日時'),
        ]);

        // creates index for column `pollet_id`
        $this->createIndex(
            'idx-charge_request_history-pollet_id',
            'charge_request_history',
            'pollet_user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-charge_request_history-pollet_id',
            'charge_request_history',
            'pollet_user_id',
            'pollet_user',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // creates index for column `charge_source_code`
        $this->createIndex(
            'idx-charge_request_history-charge_source_code',
            'charge_request_history',
            'charge_source_code'
        );

        // add foreign key for table `charge_source`
        $this->addForeignKey(
            'fk-charge_request_history-charge_source_code',
            'charge_request_history',
            'charge_source_code',
            'charge_source',
            'charge_source_code',
            'CASCADE',
            'CASCADE'
        );
    }

    private function create_charge_error_history()
    {
        $this->createTable('charge_error_history', [
            'id'                        => $this->primaryKey(),
            'charge_request_history_id' => $this->integer()->notNull(),
            'error_code'                => $this->string(20)->notNull(),
            'raw_data'                  => $this->text()->notNull(),
            'updated_at'                => $this->timestamp()->null(),
            'created_at'                => $this->timestamp()->null(),
        ]);

        // creates index for column `charge_request_history_id`
        $this->createIndex(
            'idx-charge_error_history-charge_request_history_id',
            'charge_error_history',
            'charge_request_history_id'
        );

        // add foreign key for table `charge_request_history`
        $this->addForeignKey(
            'fk-charge_error_history-charge_request_history_id',
            'charge_error_history',
            'charge_request_history_id',
            'charge_request_history',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    private function create_batch_management()
    {
        $this->createTable('batch_management', [
            'id'     => $this->primaryKey(),
            'name'   => $this->string(256)->notNull(),
            'status' => $this->string(15)->notNull(),
        ]);
        $this->createIndex(
            'idx-batch_management-name',
            'batch_management',
            ['name'],
            true
        );
    }

    private function create_push_information_opening()
    {
        $this->createTable('push_information_opening', [
            'id'             => $this->primaryKey(),
            'pollet_user_id' => "INT(11) UNSIGNED NOT NULL comment 'polletユーザーID'",
            'information_id' => $this->integer()->notNull(),
            'created_at'     => $this->timestamp()->null(),
        ]);

        // creates index for column `pollet_id`
        $this->createIndex(
            'idx-push_information_opening-pollet_id',
            'push_information_opening',
            'pollet_user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-push_information_opening-pollet_id',
            'push_information_opening',
            'pollet_user_id',
            'pollet_user',
            'id',
            'CASCADE'
        );

        // creates index for column `information_id`
        $this->createIndex(
            'idx-push_information_opening-information_id',
            'push_information_opening',
            'information_id'
        );

        // add foreign key for table `information`
        $this->addForeignKey(
            'fk-push_information_opening-information_id',
            'push_information_opening',
            'information_id',
            'information',
            'id',
            'CASCADE'
        );
    }

    private function create_inquiry()
    {
        $this->createTable('inquiry', [
            'id'             => $this->primaryKey(),
            'pollet_user_id' => "INT(11) UNSIGNED NOT NULL comment 'polletユーザーID'",
            'mail_address'   => $this->string(256)->notNull(),
            'content'        => $this->text()->notNull(),
            'updated_at'     => $this->timestamp()->null(),
            'created_at'     => $this->timestamp()->null(),
        ]);

        // creates index for column `pollet_id`
        $this->createIndex(
            'idx-inquiry-pollet_id',
            'inquiry',
            'pollet_user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-inquiry-pollet_id',
            'inquiry',
            'pollet_user_id',
            'pollet_user',
            'id',
            'CASCADE'
        );
    }

    private function create_card_value_cache()
    {
        $this->createTable('card_value_cache', [
            'id'             => $this->primaryKey(),
            'pollet_user_id' => "INT(11) UNSIGNED NOT NULL comment 'polletユーザーID'",
            'value'          => $this->integer()->notNull(),
            'updated_at'     => $this->timestamp()->null(),
            'created_at'     => $this->timestamp()->null(),
        ]);

        // creates index for column `pollet_id`
        $this->createIndex(
            'idx-card_value_cache-pollet_id',
            'card_value_cache',
            'pollet_user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-card_value_cache-pollet_id',
            'card_value_cache',
            'pollet_user_id',
            'pollet_user',
            'id',
            'CASCADE'
        );
    }

    private function create_admin_user()
    {
        $this->createTable('admin_user', [
            'id'         => $this->primaryKey(),
            'name'       => $this->string(32)->notNull(),
            'updated_at' => $this->timestamp()->null(),
            'created_at' => $this->timestamp()->null(),
        ]);
    }

    private function create_inquiry_reply()
    {
        $this->createTable('inquiry_reply', [
            'id'            => $this->primaryKey(),
            'inquiry_id'    => $this->integer()->notNull(),
            'admin_user_id' => $this->integer()->notNull(),
            'content'       => $this->text()->notNull(),
            'updated_at'    => $this->timestamp()->null(),
            'created_at'    => $this->timestamp()->null(),
        ]);

        // creates index for column `admin_user_id`
        $this->createIndex(
            'idx-inquiry_reply-admin_user_id',
            'inquiry_reply',
            'admin_user_id'
        );

        // add foreign key for table `admin_user`
        $this->addForeignKey(
            'fk-inquiry_reply-admin_user_id',
            'inquiry_reply',
            'admin_user_id',
            'admin_user',
            'id',
            'CASCADE'
        );
    }

    private function create_monthly_trading_history_cache()
    {
        $this->createTable('monthly_trading_history_cache', [
            'id'             => $this->primaryKey(),
            'pollet_user_id' => "INT(11) UNSIGNED NOT NULL comment 'polletユーザーID'",
            'month'          => $this->string(4)->notNull(),
            'records_json'   => $this->text()->notNull(),
            'updated_at'     => $this->timestamp()->null(),
            'created_at'     => $this->timestamp()->null(),
        ]);
        // creates unique index for column `pollet_user_id` and `month`
        $this->createIndex(
            'idx-monthly_trading_history_cache-pollet_user_id-and-month',
            'monthly_trading_history_cache',
            ['pollet_user_id', 'month'],
            true
        );

        // creates index for column `pollet_user_id`
        $this->createIndex(
            'idx-monthly_trading_history_cache-pollet_user_id',
            'monthly_trading_history_cache',
            'pollet_user_id'
        );

        // add foreign key for table `pollet_user`
        $this->addForeignKey(
            'fk-monthly_trading_history_cache-pollet_user_id',
            'monthly_trading_history_cache',
            'pollet_user_id',
            'pollet_user',
            'id',
            'CASCADE'
        );
    }

    private function create_reception()
    {
        $this->createTable('reception', [
            'id'                        => $this->primaryKey()                                     ->comment('ID'),
            'reception_code'            => $this->string(64)      ->notNull()                      ->comment('受付ID'),
            'pollet_user_id'            => "INT(11) UNSIGNED NOT NULL comment 'polletユーザーID'",
            'charge_source_code'        => $this->string(10)      ->notNull()                      ->comment('チャージ元コード'),
            'charge_request_history_id' => $this->integer()       ->notNull()                      ->comment('チャージリクエスト履歴ID'),
            'charge_value'              => $this->integer()       ->notNull()                      ->comment('チャージ申請額'),
            'reception_status'          => $this->string(16)      ->notNull()                      ->comment('受付ステータス'),
            'expiry_date'               => $this->timestamp()     ->null()                         ->comment('受付の有効期限'),
            'by_card_number'            => $this->smallInteger(1) ->notNull()->defaultValue(false) ->comment('カード会員番号からユーザを紐付けたかどうか'),
            'updated_at'                => $this->timestamp()     ->null()                         ->comment('更新日時'),
            'created_at'                => $this->timestamp()     ->null()                         ->comment('作成日時'),
        ]);

        // creates unique index for column `reception_code`
        $this->createIndex(
            'idx-reception-reception_code',
            'reception',
            ['reception_code'],
            true
        );

        // add foreign key for table `pollet_user`
        $this->addForeignKey(
            'fk-reception-pollet_id',
            'reception',
            'pollet_user_id',
            'pollet_user',
            'id',
            'RESTRICT'
        );

        // add foreign key for table `charge_request_history`
        $this->addForeignKey(
            'fk-reception-charge_request_history_id',
            'reception',
            'charge_request_history_id',
            'charge_request_history',
            'id',
            'RESTRICT'
        );

        // add foreign key for table `charge_source`
        $this->addForeignKey(
            'fk-reception-charge_source_code',
            'reception',
            'charge_source_code',
            'charge_source',
            'charge_source_code',
            'RESTRICT'
        );

        // creates index for column `expiry_date`
        $this->createIndex(
            'idx-reception-expiry_date',
            'reception',
            'expiry_date'
        );
    }

    public function down()
    {
        $foreignKeys = [
            // ['外部キー名', 'テーブル']
            ['fk-reception-charge_source_code', 'reception'],
            ['fk-reception-charge_request_history_id', 'reception'],
            ['fk-reception-pollet_id', 'reception'],
            ['fk-monthly_trading_history_cache-pollet_user_id', 'monthly_trading_history_cache'],
            ['fk-inquiry_reply-admin_user_id', 'inquiry_reply'],
            ['fk-card_value_cache-pollet_id', 'card_value_cache'],
            ['fk-inquiry-pollet_id', 'inquiry'],
            ['fk-push_information_opening-information_id', 'push_information_opening'],
            ['fk-push_information_opening-pollet_id', 'push_information_opening'],
            ['fk-charge_error_history-charge_request_history_id', 'charge_error_history'],
            ['fk-charge_request_history-charge_source_code', 'charge_request_history'],
            ['fk-charge_request_history-pollet_id', 'charge_request_history'],
            ['fk-push_notification_token-pollet_id', 'push_notification_token'],
            ['fk-point_site_api-charge_source_code', 'point_site_api'],
            ['fk-point_site_token-charge_source_code', 'point_site_token'],
            ['fk-point_site_token-pollet_id', 'point_site_token'],
        ];
        foreach ($foreignKeys as list($name, $table)) {
            $this->dropForeignKey($name, $table);
        }

        $indexes = [
            // ['インデックス名', 'テーブル']
            ['idx-reception-expiry_date', 'reception'],
            ['idx-reception-reception_code', 'reception'],
            ['idx-monthly_trading_history_cache-pollet_user_id', 'monthly_trading_history_cache'],
            ['idx-monthly_trading_history_cache-pollet_user_id-and-month', 'monthly_trading_history_cache'],
            ['idx-inquiry_reply-admin_user_id', 'inquiry_reply'],
            ['idx-card_value_cache-pollet_id', 'card_value_cache'],
            ['idx-inquiry-pollet_id', 'inquiry'],
            ['idx-push_information_opening-information_id', 'push_information_opening'],
            ['idx-push_information_opening-pollet_id', 'push_information_opening'],
            ['idx-batch_management-name', 'batch_management'],
            ['idx-charge_error_history-charge_request_history_id', 'charge_error_history'],
            ['idx-charge_request_history-charge_source_code', 'charge_request_history'],
            ['idx-charge_request_history-pollet_id', 'charge_request_history'],
            ['idx-push_notification_token-pollet_id', 'push_notification_token'],
            ['idx-point_site_api-charge_source_code', 'point_site_api'],
            ['idx-point_site_api-charge_source_code-and-api_name', 'point_site_api'],
            ['idx-point_site_token-charge_source_code', 'point_site_token'],
            ['idx-point_site_token-pollet_id', 'point_site_token'],
            ['idx-point_site_token-pollet_id-and-charge_source_code', 'point_site_token'],
        ];
        foreach ($indexes as list($name, $table)) {
            $this->dropIndex($name, $table);
        }

        $tables = [
            'reception',
            'monthly_trading_history_cache',
            'inquiry_reply',
            'admin_user',
            'card_value_cache',
            'inquiry',
            'push_information_opening',
            'batch_management',
            'charge_error_history',
            'charge_request_history',
            'push_notification_token',
            'point_site_api',
            'point_site_token',
            'register_campaign_point_percentage',
            'information',
            'charge_source',
            'pollet_user',
        ];
        foreach ($tables as $table) {
            $this->dropTable($table);
        }
    }
}
