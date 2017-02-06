<?php
namespace tests\unit\fixtures;

use yii\db\ActiveRecord;
use yii\db\IntegrityException;
use yii\test\Fixture;

class PolletDbFixture extends Fixture
{
    public function unload()
    {
        // 子テーブルにデータがあるテーブルを消せないので、何回か繰り返す
        for ($i = 0; $i < 3; $i++) {
            // DBのデータをすべて削除する
            foreach (ActiveRecord::getDb()->getSchema()->tableSchemas as $table) {
                if ($table->fullName === 'migration') {
                    // 消してしまうとマイグレーションが失敗する
                    continue;
                }

                try {
                    ActiveRecord::getDb()->createCommand()->delete($table->fullName)->execute();
                } catch (IntegrityException $e) {
                    // 小テーブルにデータを持つ親テーブルのレコードを消そうとした。
                    // 小テーブルのデータを消してからやり直す
                    continue;
                }
                if ($table->sequenceName !== null) {
                    ActiveRecord::getDb()->createCommand()->resetSequence($table->fullName, 1)->execute();
                }
            }
        }
    }
}
