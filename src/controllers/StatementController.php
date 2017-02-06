<?php
namespace app\controllers;

use app\helpers\YearMonth;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * 明細画面コントローラ
 * Class StatementController
 * @package app\controllers
 */
class StatementController extends CommonController
{
    /**
     * 19. 利用明細
     * @param string|null $month 'yymm'の形式の月(2016年9月は'1609')
     * @return string|Response
     * @throws BadRequestHttpException
     */
    public function actionTrading($month = null)
    {
        if (is_null($month)) {
            $month = date('ym');
        }

        list($y, $m) = YearMonth::divideMonthString($month);
        return $this->render('trading', [
            'currentYear'     => $y,
            'currentMonth'    => $m,
            'nextMonthString' => YearMonth::getNextMonthString($month),
            'prevMonthString' => YearMonth::getPrevMonthString($month),
            'argument'        => $month,
        ]);
    }
}
