<?php
namespace app\controllers;

use app\models\forms\InquiryForm;
use Yii;

/**
 * 問い合わせ画面コントローラ
 * Class InquiryController
 * @package app\controllers
 */
class InquiryController extends CommonController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return []; // 誰でもアクセスできる
    }

    /**
     * 24-4. お問い合わせフォーム
     * @return string
     */
    public function actionIndex()
    {
        $formModel = new InquiryForm();

        $userId = isset($this->authorizedUser) ? $this->authorizedUser->id : null;

        if ($formModel->load(Yii::$app->request->post()) && $formModel->contact($userId)) {
            return $this->render('complete');
        }
        return $this->render('index', [
            'formModel' => $formModel,
        ]);
    }
}
