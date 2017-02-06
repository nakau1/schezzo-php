<?php
namespace app\controllers;

use app\models\ChargeSource;
use app\models\PointSiteToken;
use Yii;

/**
 * Class TopController
 * @package app\controllers
 */
class TopController extends CommonController
{
    /** トップ画面円グラフの分母値 */
    const CIRCLER_GRAPH_DENOMINATOR = 999999; //TODO: 仕様では5000固定と聞いている;

    /**
     * 15. トップ画面
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'chargeSources' => $this->chargeSourcesOfTopList(),
        ]);
    }

    /**
     * トップページに表示するチャージ元を取得する
     * @return \app\models\ChargeSource[]
     */
    private function chargeSourcesOfTopList()
    {
        $sourcesNotRequireAuth = ChargeSource::find()->pointSiteApi()->active()->requireNoAuthorization()->all();

        // 連携した順に表示する
        /** @var PointSiteToken[] $tokens */
        $tokens = $this->authorizedUser->getPointSiteTokens()->orderBy([
            PointSiteToken::tableName() . '.created_at' => SORT_ASC,
        ])->all();

        // 公開のみ
        $sourcesAuthorized = [];
        foreach ($tokens as $token) {
            if ($token->chargeSource->isPublic()) {
                $sourcesAuthorized[] = $token->chargeSource;
            }
        }

        return array_merge($sourcesNotRequireAuth, $sourcesAuthorized);
    }
}
