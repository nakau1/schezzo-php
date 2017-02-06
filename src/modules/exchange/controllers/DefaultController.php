<?php

namespace app\modules\exchange\controllers;

use app\modules\exchange\models\ApplyInquire;
use app\modules\exchange\models\Reception;
use app\modules\exchange\models\SiteAuthorize;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * Class DefaultController
 * @package app\modules\exchange\controllers
 */
class DefaultController extends CommonController
{
    /**
     * 交換受付通知API
     * @return array
     */
    public function actionReception()
    {
        $params = [
            'site_code'     => Yii::$app->request->get('site_code'),
            'api_key'       => Yii::$app->request->post('api_key'),
            'card_number'   => Yii::$app->request->post('card_number'),
            'pollet_user_id'=> Yii::$app->request->post('pollet_id'),
            'amount'        => Yii::$app->request->post('amount'),
            'delay'         => Yii::$app->request->post('delay'),
        ];

        // 認証
        $siteAuth = new SiteAuthorize();
        if (!$siteAuth->load($params) || !$siteAuth->authorize()) {
            return $this->respondError($siteAuth->getFirstErrors());
        }
        $params['charge_source_code'] = $siteAuth->getChargeSource()->charge_source_code;

        // 受理
        $reception = new Reception();
        $reception->setScenario(Reception::SCENARIO_API_REQUEST);
        $reception->siteAuthorize = $siteAuth;
        if (!$reception->load($params) || !$reception->accept()) {
            return $this->respondError($reception->getFirstErrors());
        }
        
        return [
            'reception_id'    => $reception->reception_code,
            'expiry_datetime' => $reception->convertDateTime($reception->expiry_date)
        ];
    }

    /**
     * チャージ申請API
     * @return array
     */
    public function actionApply()
    {
        $params = [
            'site_code'     => Yii::$app->request->get('site_code'),
            'api_key'       => Yii::$app->request->post('api_key'),
            'reception_ids' => Yii::$app->request->post('reception_ids'),
        ];

        // 認証
        $siteAuth = new SiteAuthorize();
        if (!$siteAuth->load($params) || !$siteAuth->authorize()) {
            return $this->respondError($siteAuth->getFirstErrors());
        }

        // 問い合わせ
        $inquire = new ApplyInquire();
        $inquire->siteAuthorize = $siteAuth;
        if (!$inquire->load($params) || !$inquire->apply()) {
            return $this->respondError($inquire->getFirstErrors());
        }

        return $inquire->getResults();
    }

    /**
     * チャージ状態確認API
     * @return array
     */
    public function actionInquire()
    {
        $params = [
            'site_code'     => Yii::$app->request->get('site_code'),
            'api_key'       => Yii::$app->request->post('api_key'),
            'reception_ids' => Yii::$app->request->post('reception_ids'),
        ];

        // 認証
        $siteAuth = new SiteAuthorize();
        if (!$siteAuth->load($params) || !$siteAuth->authorize()) {
            return $this->respondError($siteAuth->getFirstErrors());
        }

        // 問い合わせ
        $inquire = new ApplyInquire();
        $inquire->siteAuthorize = $siteAuth;
        if (!$inquire->load($params) || !$inquire->inquire()) {
            return $this->respondError($inquire->getFirstErrors());
        }

        return $inquire->getResults();
    }

    /**
     * 共通エラーメソッド
     */
    public function actionIndex()
    {
        throw new NotFoundHttpException();
    }

    /**
     * 共通エラーメソッド
     */
    public function actionError()
    {
        // 実際のレスポンスは config/api.php に設定
    }
}
