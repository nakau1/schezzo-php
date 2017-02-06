<?php

namespace app\models;

use app\models\cedyna_files\CedynaFile;
use app\models\cedyna_files\NumberedCedynaIdData;
use app\models\exceptions\FirstChargeRequest\ChargeNotFoundException;
use app\models\exceptions\ReceiveNumberedCedynaId\ChargeNotFirstException;
use app\models\exceptions\ReceiveNumberedCedynaId\UserNotFoundException;
use DomainException;
use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\db\Exception;

class ReceiveNumberedCedynaId extends Model
{
    /** @var string */
    private $completeDirectory;
    /** @var string */
    private $retryDirectory;

    /**
     * ReceiveNumberedCedynaId constructor.
     *
     * @param string $completeDirectory 処理が完了したファイルを入れるディレクトリ
     * @param string $retryDirectory 処理が失敗し、リトライ可能な場合に、ファイルを出力するディレクトリ
     * @param array $config
     */
    public function __construct(string $completeDirectory, string $retryDirectory, $config = [])
    {
        parent::__construct($config);

        $this->completeDirectory = $completeDirectory;
        $this->retryDirectory = $retryDirectory;
    }

    /**
     * 1つのセディナID発番通知ファイルに対して、以下の処理を行う。
     * - 対象のユーザー情報にセディナIDを追加
     * - 対象のユーザー情報の登録状態を「カード未認証」に更新
     * - 対象のチャージ申請データの処理状態を「処理待ち」に更新
     * 処理が失敗し、リトライ可能な場合に、リトライ用ディレクトリにファイルを出力する
     *
     * @param CedynaFile $file
     */
    public function acceptFile(CedynaFile $file)
    {
        Yii::info("begin processing file: {$file->getPath()}");

        $retryLines = [];
        foreach ($file->readDataLinesAll() as $line) {
            $data = new NumberedCedynaIdData($line);

            try {
                $this->acceptLineData($data);
            } catch (Exception $e) {
                Yii::warning("an exception occurred, retry later: polletId={$data->pollet_user_id}; {$e->getMessage()}");
                $retryLines[] = $line;
            }
        }

        $file->moveTo($this->completeDirectory);

        if ($retryLines !== []) {
            $this->makeRetryFile($file->getName(), $retryLines);
        }

        Yii::info("finish processing file: {$file->getPath()}");
    }

    /**
     * セディナID発番通知ファイルの1行に対して、以下の処理を行う。
     * - 対象のユーザー情報にセディナIDを追加
     * - 対象のユーザー情報の登録状態を「カード未認証」に更新
     * - 対象のチャージ申請データの処理状態を「処理待ち」に更新
     *
     * @param NumberedCedynaIdData $data
     * @throws Exception
     */
    private function acceptLineData(NumberedCedynaIdData $data)
    {
        Yii::info("begin processing line: polletId={$data->pollet_user_id}");
        $transaction = ActiveRecord::getDb()->beginTransaction();
        try {
            $this->acceptOneUser($data);
            $transaction->commit();
            Yii::info("finish processing line: polletId={$data->pollet_user_id}");
        } catch (UserNotFoundException $e) {
            $transaction->rollBack();
            Yii::error("user not found: polletId={$data->pollet_user_id}; {$e->getMessage()}");
        } catch (ChargeNotFirstException $e) {
            $transaction->rollBack();
            Yii::error("charge not first: polletId={$data->pollet_user_id}; {$e->getMessage()}");
        } catch (DomainException $e) {
            $transaction->rollBack();
            Yii::error("domain exception: polletId={$data->pollet_user_id}; {$e->getMessage()}");
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * 1人のユーザーに対してセディナID発番受取処理を行う。
     * - ユーザー情報にセディナIDを追加
     * - ユーザーの登録状態を「カード未認証」に更新
     * - 対象のチャージ申請データの処理状態を「処理待ち」に更新
     *
     * @param NumberedCedynaIdData $data
     *
     * @throws UserNotFoundException
     * @throws ChargeNotFirstException
     * @throws DomainException
     */
    private function acceptOneUser(NumberedCedynaIdData $data)
    {
        /** @var PolletUser $user */
        $user = PolletUser::findOne($data->pollet_user_id);
        if (!$user) {
            throw new UserNotFoundException('ユーザーが存在しません');
        }
        if (!$user->isWaitingIssue()) {
            // 「初回チャージが完了し、カード発行申し込みした」段階であることを期待する。
            // これ以外の場合、セディナIDの発番が初めてじゃない
            throw new ChargeNotFirstException('複数回カード発行申し込みしたユーザーです');
        }

        $user->cedyna_id = $data->cedyna_id;
        $user->registration_status = PolletUser::STATUS_ISSUED;
        if (!$user->save()) {
            throw new DomainException('ユーザーを保存できません; '.implode(';', $user->getFirstErrors()));
        }

        $firstChargeRequest = new FirstChargeRequest();
        $firstChargeRequest->ready($user);
    }

    /**
     * リトライするためのファイルを生成
     *
     * @param string $originalFileName
     * @param array $retryLines
     */
    private function makeRetryFile(string $originalFileName, array $retryLines)
    {
        // 処理したファイルと同じ名前だと、再度処理時に complete ディレクトリに移動できなくなる
        $suffix = '.retry.'.date("YmdHis").'.csv';
        $retryFile = new CedynaFile($this->retryDirectory.'/'.$originalFileName.$suffix);

        $header = '"S","'.date('Y/m/d H:i:s').'"'."\n";
        $retryFile->setSaveContent($header)->save(true);
        foreach ($retryLines as $line) {
            $columns = [];
            foreach ($line as $column) {
                $columns[] = '"'.$column.'"';
            }
            $data = implode(',', $columns)."\n";
            $retryFile->setSaveContent($data)->save(true);
        }
        $trailer = '"E",'.sprintf('%8s', count($retryLines))."\n";
        $retryFile->setSaveContent($trailer)->save(true);
    }
}
