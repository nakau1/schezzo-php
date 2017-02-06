<?php
namespace app\controllers;

use app\components\CedynaMyPageWithCache;
use app\models\ChargeRequestHistory;
use app\models\exceptions\UnauthorizedHttpException;
use app\models\MonthlyTradingHistoryCache;
use app\models\TradingHistory;
use Exception;
use Yii;
use yii\filters\auth\QueryParamAuth;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Class AjaxController
 * @package app\controllers
 */
class AjaxController extends CommonController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        Yii::$app->user->enableSession = false;
        Yii::$app->user->loginUrl = null;

        return [
            'authenticator' => [
                'class' => QueryParamAuth::className(),
            ],
        ];
    }

    /**
     * トップ画面の各値をAjaxで取得するためのアクション
     * @return string JSON {price: チャージ残高, percentage: グラフ用のパーセンテージ}
     * @throws UnauthorizedHttpException
     */
    public function actionGetValues()
    {
        // 分母になる値はチャージ成功直後のチャージ額
        // receive-cedyna-payment-file バッチ実行時にユーザテーブルに保存される
        // https://www.pivotaltracker.com/n/projects/1857257/stories/133913607
        $denominator = $this->authorizedUser->balance_at_charge;
        $price = $this->authorizedUser->myChargedValue;

        if ($price === false) {
            // セディナページログインに失敗した場合
            return json_encode([
                'price'      => false,
                'percentage' => false,
                'loggedIn'   => false,
                'url'        => '/auth/sign-out?fail=1',
            ]);
        }

        if ($denominator <= 0) {
            $percentage = 0;
        } else if ($price >= $denominator) {
            $percentage = 1;
        } else {
            $percentage = $price / $denominator;
        }

        return json_encode([
            'price'      => $price,
            'percentage' => floatval($percentage),
            'loggedIn'   => true,
            'url'        => null,
        ]);
    }

    /**
     * 利用明細の一覧部分をHTMLで返却するアクション
     * @param string|null $month
     * @return string|Response
     * @throws BadRequestHttpException
     */
    public function actionTradingList($month = null)
    {
        $loginFail = false;
        if (is_null($month)) {
            $month = date('ym');
        }

        try {
            $tradingHistories = [];
            // アクティベート済みユーザのみ利用明細が取得できる
            if ($this->authorizedUser->isActivatedUser()) {
                // 最近完了したチャージ申請履歴も表示されるようにキャッシュをリセットする
                $this->deleteCacheIfNeed($month);

                $cedynaWithCache = CedynaMyPageWithCache::getInstance();
                $tradingHistories = $cedynaWithCache->tradingHistoriesCache($this->authorizedUser->cedyna_id, $month);

                if ($tradingHistories === null) {
                    if (!$cedynaWithCache->login(
                        $this->authorizedUser->cedyna_id,
                        $this->authorizedUser->rawPassword)
                    ) {
                        $loginFail = true;
                    }
                    $tradingHistories = $cedynaWithCache->tradingHistories($month);
                }
            }
        } catch (Exception $e) {
            throw new BadRequestHttpException('', $e->getCode(), $e);
        }

        $this->layout = false;
        return $this->render('@app/views/statement/trading-list', [
            'loginFail'        => $loginFail,
            'tradingHistories' => TradingHistory::sortByTradingDate($tradingHistories),
        ]);
    }

    /**
     * 最近完了したチャージ申請履歴が表示されるようにキャッシュをリセットする
     * @param string $month
     * @throws Exception
     */
    private function deleteCacheIfNeed(string $month)
    {
        $cache = MonthlyTradingHistoryCache::find()->where([
            'pollet_user_id' => $this->authorizedUser->id,
            'month'          => $month,
        ])->one();
        if (empty($cache)) {
            return;
        }

        $latestAppliedChargeRequest = ChargeRequestHistory::find()->mine()->atMonth($month)->applied()->orderBy([
            ChargeRequestHistory::tableName() . '.updated_at' => SORT_DESC,
        ])->one();
        // 最後に完了したチャージ申請履歴よりもキャッシュのほうが古ければ削除
        if ($latestAppliedChargeRequest && $latestAppliedChargeRequest->updated_at > $cache->updated_at) {
            $cache->delete();
        }
    }
}
