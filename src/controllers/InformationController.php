<?php
namespace app\controllers;

use app\models\Information;
use app\models\PushInformationOpening;
use yii\web\NotFoundHttpException;

/**
 * Class InformationController
 * @package app\controllers
 */
class InformationController extends CommonController
{
    /**
     * 26. お知らせ一覧
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'informations' => Information::find()->joinOpening()->published()->orderBy([
                Information::tableName() . '.begin_date' => SORT_DESC,
            ])->all(),
        ]);
    }

    /**
     * 28. お知らせ詳細
     * @param integer $id お知らせID
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionDetail($id)
    {
        $information = Information::find()->joinOpening()->published()->andWhere([
            Information::tableName() . '.id' => $id,
        ])->one();

        if (!$information) {
            throw new NotFoundHttpException('お知らせが見つかりません');
        }

        if (!$information->isOpened) {
            $opening = new PushInformationOpening();
            $opening->add($this->authorizedUser->id, $information->id);
        }

        return $this->render('detail', [
            'information' => $information,
        ]);
    }
}