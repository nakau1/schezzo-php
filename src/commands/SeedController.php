<?php
namespace app\commands;

use Faker\Factory;
use Faker\Generator;
use yii\base\ErrorException;

/**
 * データを生成する
 * Class SeedController
 * @package app\commands
 */
class SeedController extends Controller
{
    /** @var Generator */
    private $faker;

    public function init()
    {
        parent::init();

        $this->validateEnvironment();
        $this->faker = Factory::create();
    }

    /**
     * コマンドを実行できる環境を制限する
     */
    private function validateEnvironment()
    {
        $permitted = in_array(YII_ENV, ['dev', 'demo', 'test'], true);
        if (!$permitted) {
            throw new ErrorException('この環境での実行は禁止されています');
        }
    }

    /**
     * データをすべて削除する
     */
    public function actionClear()
    {

    }

    /**
     * demoに使うデータの生成
     */
    public function actionIndex()
    {
        $this->actionClear();
    }
}