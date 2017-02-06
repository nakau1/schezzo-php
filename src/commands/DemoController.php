<?php
namespace app\commands;

use app\models\cedyna_files\CedynaFile;
use app\models\PolletUser;
use Yii;
use yii\console\Controller;

/**
 * デモで動作確認するためのコマンドを提供する
 * TODO: 本番で実行できないようにする
 *
 * Class DemoController
 * @package app\commands
 */
class DemoController extends Controller
{
    /**
     * HULFTの入金ファイルからすべて成功の入金結果ファイルを生成する
     */
    public function actionCedynaAcceptPayment()
    {
        $this->processPaymentFile(true);
    }

    /**
     * HULFTの入金ファイルからすべて失敗の入金結果ファイルを生成する
     */
    public function actionCedynaRejectPayment()
    {
        $this->processPaymentFile(false);
    }

    /**
     * HULFTの入金ファイルから入金結果ファイルを生成する
     *
     * @param bool $success
     */
    private function processPaymentFile(bool $success)
    {
        $srcDir = Yii::$app->params['hulftPath'].'/send/send_payment_file/';
        $dstDir = Yii::$app->params['hulftPath'].'/recv/receive_payment_file/';

        $sourceFiles = CedynaFile::findAll($srcDir);
        foreach ($sourceFiles as $sourceFile) {
            $resultFile = new CedynaFile($dstDir.'/'.$sourceFile->getName());
            foreach ($sourceFile->readLinesAll() as $row) {
                if ($row[0] === 'S') {
                    $resultFile->setSaveContent('"'.implode('","', $row).'"'.PHP_EOL)->save(true);
                }
                if ($row[0] === 'H') {
                    $row[] = ''; // フォーマットエラーなし
                    $resultFile->setSaveContent('"'.implode('","', $row).'"'.PHP_EOL)->save(true);
                }
                if ($row[0] === 'D') {
                    $row[10] = $success ? 0  : 1;      // 処理結果
                    $row[11] = $success ? '' : '4155'; // エラーコード
                    $resultFile->setSaveContent('"'.implode('","', $row).'"'.PHP_EOL)->save(true);
                }
                if ($row[0] === 'E') {
                    $resultFile->setSaveContent('"'.implode('","', $row).'"'.PHP_EOL)->save(true);
                }
            }
            $sourceFile->remove();
        }
    }
}
