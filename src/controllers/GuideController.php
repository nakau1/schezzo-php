<?php
namespace app\controllers;

/**
 * Class GuideController
 * @package app\controllers
 */
class GuideController extends CommonController
{
    /**
     * 23. 利用ガイド
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /** はじめてガイド: Pollet Visa Prepaidとは */
    public function actionFirstVisaPrepaid()
    {
        return $this->render('first-visa-prepaid');
    }

    /**
     * はじめてガイド: ご利用までの流れ
     * @param string $back 戻るボタンで戻るページのURI
     * @return string
     */
    public function actionFirstFlow($back = 'guide/')
    {
        return $this->render('first-flow', [
            'backAction' => [$back],
        ]);
    }

    /** はじめてガイド: カードの使い方 */
    public function actionFirstUsage()
    {
        return $this->render('first-usage');
    }


    /** 詳細ガイド: 会員番号 */
    public function actionDetailMemberNumber()
    {
        return $this->render('detail-member-number');
    }

    /** 詳細ガイド: ログインパスワード */
    public function actionDetailLoginPassword()
    {
        return $this->render('detail-login-password');
    }

    /** 詳細ガイド: カード暗証番号 */
    public function actionDetailCardPin()
    {
        return $this->render('detail-card-pin');
    }

    /** 詳細ガイド: チャージについて */
    public function actionDetailAboutCharge()
    {
        return $this->render('detail-about-charge');
    }

    /** 詳細ガイド: 使えるお店/使えないお店 */
    public function actionDetailAvailableShops()
    {
        return $this->render('detail-available-shops');
    }

    /** 詳細ガイド: 海外利用時の手数料 */
    public function actionDetailFeeInForeign()
    {
        return $this->render('detail-fee-in-foreign');
    }

    /** 詳細ガイド: 利用明細について */
    public function actionDetailAboutSteatment()
    {
        return $this->render('detail-about-steatment');
    }

    /** 詳細ガイド: カードの紛失 */
    public function actionDetailCardLost()
    {
        return $this->render('detail-card-lost');
    }

    /** 詳細ガイド: 登録情報の変更 */
    public function actionDetailChangeRegistration()
    {
        return $this->render('detail-change-registration');
    }

    /** 詳細ガイド: カードの停止/再開/解約 */
    public function actionDetailCardManagement()
    {
        return $this->render('detail-card-management');
    }

    /** 詳細ガイド: カードの有効期限 */
    public function actionDetailCardExpiration()
    {
        return $this->render('detail-card-expiration');
    }

    /** 詳細ガイド: 本アプリの端末推奨環境 */
    public function actionDetailRecommendedEnvironment()
    {
        return $this->render('detail-recommended-environment');
    }
}
