<?php

namespace app\modules\exchange\controllers;

use app\modules\exchange\helpers\Messages;
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
    /**
     * @var bool
     */
    public $enableCsrfValidation = false;
    /**
     * ステータスコード
     * @var string
     */
    protected $code = 200;
    /**
     * レスポンスメッセージ
     * @var string
     */
    protected $message = 'OK';

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
        Yii::$app->response->setStatusCode($this->code);
        return [
            'message' => $this->message,
            'data'    => $result,
        ];
    }

    /**
     * @param $errors
     * @return array
     */
    protected function respondError(array $errors)
    {
        $errors = $this->flatmap($errors);
        $error = array_pop($errors);

        switch ($error) {
            case Messages::REQUIRED_EMPTY:
            case Messages::INVALID_PARAM:
            case Messages::AMOUNT_RANNGE_OUT:
            case Messages::TOO_MANY_IDS:
                $this->message = Messages::ERR_INVALID_PARAM;
                $this->code    = 400;
                return [$error];
            case Messages::ERR_UNAUTHORIZED:
                $this->message = Messages::ERR_UNAUTHORIZED;
                $this->code    = 401;
                return [];
            default:
                $this->message = Messages::ERR_FAILED;
                $this->code    = 400;
                return [$error];
        }
    }

    /**
     * @param array $arr
     * @return array
     */
    private function flatmap(array $arr) : array
    {
        return iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($arr)), false);
    }
}