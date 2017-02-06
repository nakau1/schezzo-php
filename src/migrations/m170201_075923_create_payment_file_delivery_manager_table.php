<?php

use yii\db\Migration;

class m170201_075923_create_payment_file_delivery_manager_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('payment_file_delivery_manager', [
            'id'               => $this->primaryKey(),
            'is_sending'       => $this->boolean()->notNull()->comment('伝送中'),
            'updated_at'       => $this->timestamp()->null() ->comment('更新日時'),
            'created_at'       => $this->timestamp()->null() ->comment('作成日時'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('payment_file_delivery_manager');
    }
}
