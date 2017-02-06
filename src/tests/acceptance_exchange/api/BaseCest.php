<?php
namespace tests\acceptance_exchange\api;

date_default_timezone_set('Asia/Tokyo');

use AcceptanceTester;
use Yii;

/**
 * Class BaseCest
 * @package tests\acceptance_exchange\api
 */
class BaseCest
{
    /**
     * 交換サイトコード
     */
    const SITE_CODE = 'demodemo';

    /**
     * APIキー
     */
    const API_KEY = 'demodemodemodemo';

    /**
     * 別の交換サイトコード
     */
    const ANOTHER_SITE_CODE = 'hapitas';

    /**
     * 別のAPIキー
     */
    const ANOTHER_API_KEY = 'hapitashapitas';

    /**
     * 新規ユーザのPolletユーザID
     */
    const NEW_USER_ID = 100000;

    /**
     * アクティベートされたユーザのPolletユーザID
     */
    const ACTIVATED_USER_ID = 100100;

    /**
     * アクティベートされたユーザのカード番号
     */
    const ACTIVATED_USER_CARD_NO = '2274020918206';

    /**
     * 存在しないPolletユーザID
     */
    const NO_EXISTS_USER_ID = 999999;

    /**
     * 存在しないカード番号
     */
    const NO_EXISTS_CARD_NO = '9999999999999';

    /**
     * @var AcceptanceTester
     */
    protected $tester;

    /**
     * @var String[]
     */
    protected $receptionIds = [];

    /**
     * @param AcceptanceTester $tester
     */
    public function _before(AcceptanceTester $tester)
    {
        $this->tester = $tester;
        $this->receptionIds = $this->executeReception();
    }
    
    public function _after()
    {
        // 後処理
    }

    /**
     * 新規ユーザ2件、アクティベート済みユーザ4件の受付を実行
     * @return string[] 受付IDの配列
     */
    protected function executeReception()
    {
        $I = $this->tester;

        $targets = [
            'pollet_id'   => [self::NEW_USER_ID, self::NEW_USER_ID, self::ACTIVATED_USER_ID, self::ACTIVATED_USER_ID],
            'card_number' => [self::ACTIVATED_USER_CARD_NO, self::ACTIVATED_USER_CARD_NO],
        ];

        $receptionIds = [];

        foreach ($targets as $key => $target) {
            foreach ($target as $value) {
                $params = [
                    'api_key'     => self::API_KEY,
                    'card_number' => '',
                    'pollet_id'   => '',
                    'amount'      => 1000,
                    'delay'       => 1,
                ];
                $params[$key] = $value;
                $I->sendPOST('/reception/'. self::SITE_CODE , $params);
                $receptionIds[] = $I->grabDataFromResponseByJsonPath('data.reception_id')[0];
            }
        }

        return $receptionIds;
    }

    /**
     * 指定の件数だけランダムな受付ID風のカンマ区切り文字列を生成する
     * @param $count int 件数
     * @return string 受付ID風のカンマ区切り文字列
     */
    protected function generateCommadSeparatedReceptionIds($count)
    {
        $values = [];
        for ($i = 0; $i < $count; $i++) {
            $values[] = Yii::$app->security->generateRandomString();
        }
        return implode(',', $values);
    }

    /**
     * APIのベースのパラメータ
     * @return array
     */
    protected function baseParams()
    {
        return [
            'api_key'       => self::API_KEY,
            'reception_ids' => '',
        ];
    }

    /**
     * APIのベースURL
     * @return string
     */
    protected function baseURL()
    {
        return '';
    }
}