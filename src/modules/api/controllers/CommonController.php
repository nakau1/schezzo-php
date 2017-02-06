<?php

namespace app\modules\api\controllers;

use Yii;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;

/**
 * Class CommonController
 * @package app\modules\api\v1\controllers
 */
class CommonController extends Controller
{
    const STATUS_OK = 'OK';

    /**
     * @var bool
     */
    public $enableCsrfValidation = false;
    /**
     * ステータスコード
     * @var string
     */
    protected $code = null;
    /**
     * レスポンスメッセージ
     * @var string
     */
    protected $message;
    /**
     * @var bool
     */
    protected $pureResponse = false;
    /**
     * @var string
     */
    protected $appVersion = null;
    /**
     * @var string
     */
    protected $platform = null;

    /**
     * 初期化
     */
    public function init()
    {
        parent::init();
        Yii::$app->response->format = Response::FORMAT_JSON;
    }

    /**
     * 共通後処理
     * @param \yii\base\Action $action
     * @param mixed            $result
     * @return array|mixed
     * @throws HttpException
     */
    public function afterAction($action, $result)
    {
        if ($this->pureResponse) {
            return $result;
        }

        return [
            'code'    => $this->code ?: Yii::$app->response->getStatusCode(),
            'message' => $this->message,
            'data'    => $result,
        ];
    }

    /**
     * エラーのレスポンスを作成する
     * @param      $message
     * @param      $errors
     * @param null $code
     * @return array
     */
    protected function generateErrorResponse($message, $errors, $code = null)
    {
        if (is_numeric($code)) {
            // ステータスコードをセットする
            $this->code = $code;
        }
        $this->message = $message;

        $errorValues = [];
        foreach ($errors as $k => $v) {
            $errorValues[] = [
                'field'   => $k,
                'message' => is_array($v) ? implode(',', $v) : $v,
            ];
        }

        return [
            'errors'  => $errorValues,
        ];
    }
}