<?php
namespace app\controllers;

use app\modules\exchange\models\Consistency;
use yii\web\NotAcceptableHttpException;

/**
 * Class ExchangeChargeController
 * @package app\controllers
 */
class ExchangeApiController extends CommonController
{
    /**
     * 提携サイト(交換APIを使用するサイト)がアプリ起動のためにアクセスするページ
     *
     * URLの形式:(routes.php参照)
     * https://(ドメイン)/exchange/(受付ID)
     *
     * @param string $reception 受付ID
     * @return \yii\web\Response
     * @throws NotAcceptableHttpException
     */
    public function actionIndex($reception)
    {
        $params = [
            'reception_id' => $reception,
        ];

        if (!$this->authorizedUser) {
            $this->throwNotAcceptableHttpException();
        }

        // ユーザ整合性チェック
        $consistency = new Consistency();
        $consistency->user = $this->authorizedUser;
        if (!$consistency->load($params) || !$consistency->check() || !$consistency->updateStatuses()) {
            $this->throwNotAcceptableHttpException();
        }

        // リダイレクト
        if ($this->authorizedUser->isFirstChargeProcessing()) {
            return $this->redirect(['issuance/']);
        } else {
            return $this->redirect(['top/']);
        }
    }

    /**
     * @throws NotAcceptableHttpException
     */
    private function throwNotAcceptableHttpException()
    {
        throw new NotAcceptableHttpException("エラー(406)が発生しました。\nこちらからお問い合わせください。");
    }
}