<?php
namespace app\modules\admin\controllers;

use Yii;
use app\modules\admin\models\Information;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * お知らせ管理画面
 * @package app\modules\admin\controllers
 */
class InformationController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * お知らせ一覧
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Information::find()
                ->active([Information::PUBLISHING_STATUS_PUBLIC, Information::PUBLISHING_STATUS_PRIVATE])
                ->joinIsPublic()
                ->orderBy(['begin_date' => SORT_DESC]),
        ]);
        $dataProvider->pagination->setPageSize(20);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * お知らせ新規作成
     * @return string|Response
     */
    public function actionCreate()
    {
        $model = new Information();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['information/index']);
        }
        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * お知らせ更新
     * @param integer $id
     * @return string|Response
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['information/index']);
        }
        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * お知らせ削除
     * @param integer $id
     * @return Response
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * 重要なお知らせかどうかのフラグを更新するアクション(javascriptからAjax実行)
     * @return string 空文字列
     * @throws BadRequestHttpException
     */
    public function actionUpdateIsImportant()
    {
        return $this->updateFragField(function (Information $model, int $value) {
            $model->is_important = $value;
        });
    }

    /**
     * 公開するかどうかのフラグを更新するアクション(javascriptからAjax実行)
     * @return string 空文字列
     */
    public function actionUpdateIsPublic()
    {
        return $this->updateFragField(function (Information $model, int $value) {
            $model->is_public = $value;
        });
    }

    /**
     * フラグ更新の共通処理
     * @param callable $updating
     * function (Information $model, int $value)
     * @return string 空文字列
     * @throws BadRequestHttpException
     */
    private function updateFragField(callable $updating)
    {
        $id    = Yii::$app->request->post('id')    ?? 0;
        $value = Yii::$app->request->post('value') ?? 0;
        $model = $this->findModel($id);
        call_user_func($updating, $model, $value);
        if (!$model->save()) {
            throw new BadRequestHttpException();
        }
        return '';
    }

    /**
     * モデル取得
     * @param $id
     * @return null|Information
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Information::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
