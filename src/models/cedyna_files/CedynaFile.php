<?php

namespace app\models\cedyna_files;

use app\models\exceptions\CedynaFile\DirectoryNotWritableException;
use app\models\exceptions\CedynaFile\FileAlreadyExistsException;
use app\models\exceptions\CedynaFile\FileCannotRenameException;
use app\models\exceptions\CedynaFile\FileNotExistsException;
use app\models\exceptions\CedynaFile\FileWritingFailedException;
use app\models\exceptions\CedynaFile\SaveContentIsEmptyException;
use Generator;
use SplFileObject;
use Yii;
use yii\helpers\FileHelper;

class CedynaFile
{
    private $filePath;
    private $saveContent;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        $this->saveContent = null;
    }

    /**
     * @param string $directory
     *
     * @return CedynaFile[]
     */
    public static function findAll(string $directory): array
    {
        $filePaths = FileHelper::findFiles($directory, ['only' => ['*.csv', '*.txt']]);
        $files = [];
        foreach ($filePaths as $filePath) {
            $files[] = new static($filePath);
        }

        return $files;
    }

    /**
     * ユニークなファイル名に変更
     * yyyymmddhhiiss-{suffix}.csv の形式のファイルを生成する
     *
     * @param CedynaFile[] $files
     * @return CedynaFile[]
     */
    public static function renameToUnique($files): array
    {
        $date = date('YmdHis');
        foreach ($files as $file) {
            //同時刻に処理するファイルを識別するための接尾辞
            $suffix = mt_rand();
            $newFileName = $date.'-'.$suffix.'.csv';
            $file->renameTo($newFileName);
        }

        return $files;
    }

    /**
     * @return Generator
     *
     * @throws FileNotExistsException
     */
    public function readLinesAll(): Generator
    {
        if (!file_exists($this->filePath)) {
            throw new FileNotExistsException('ファイルが存在しません');
        }

        $csv = new SplFileObject($this->filePath);
        $csv->setFlags(SplFileObject::READ_CSV);
        foreach ($csv as $line) {
            yield $line;
        }
    }

    /**
     * @return array
     *
     * @throws FileNotExistsException
     */
    public function readHeaderLine(): array
    {
        foreach ($this->readLinesAll() as $line) {
            if ($line[0] === 'H') {
                return $line;
            }
        }

        return [];
    }

    /**
     * @return Generator
     *
     * @throws FileNotExistsException
     */
    public function readDataLinesAll(): Generator
    {
        foreach ($this->readLinesAll() as $line) {
            if ($line[0] === 'D') {
                yield $line;
            }
        }
    }

    /**
     * @param string $dstDirectory
     *
     * @throws DirectoryNotWritableException
     * @throws FileAlreadyExistsException
     * @throws FileWritingFailedException
     */
    public function moveTo(string $dstDirectory)
    {
        if (!is_writable($dstDirectory)) {
            throw new DirectoryNotWritableException('移動先のディレクトリに書き込みできません');
        }
        $dstFilePath = $dstDirectory.'/'.$this->getName();
        if (file_exists($dstFilePath)) {
            throw new FileAlreadyExistsException('移動先にファイルが存在します');
        }

        $isSuccess = rename($this->filePath, $dstFilePath);
        if (!$isSuccess) {
            throw new FileWritingFailedException('ファイルの書き込みに失敗しました');
        }

        $this->filePath = $dstFilePath;
    }

    /**
     * @param string $dstDirectory
     * @return CedynaFile
     *
     * @throws DirectoryNotWritableException
     * @throws FileAlreadyExistsException
     * @throws FileWritingFailedException
     */
    public function copyTo(string $dstDirectory)
    {
        if (!is_writable($dstDirectory)) {
            throw new DirectoryNotWritableException('コピー先のディレクトリに書き込みできません');
        }
        $dstFilePath = $dstDirectory.'/'.$this->getName();
        if (file_exists($dstFilePath)) {
            throw new FileAlreadyExistsException('コピー先にファイルが存在します');
        }

        $isSuccess = copy($this->filePath, $dstFilePath);
        if (!$isSuccess) {
            throw new FileWritingFailedException('ファイルの書き込みに失敗しました');
        }

        return new static($dstFilePath);
    }

    /**
     * @param string $name
     *
     * @throws DirectoryNotWritableException
     * @throws FileAlreadyExistsException
     * @throws FileCannotRenameException
     */
    public function renameTo(string $name)
    {
        if (!is_writable($this->getDirectory())) {
            throw new DirectoryNotWritableException('ディレクトリに書き込みできません');
        }
        $dstFilePath = $this->getDirectory().'/'.$name;
        if (file_exists($dstFilePath)) {
            throw new FileAlreadyExistsException('移動先にファイルが存在します');
        }

        $isSuccess = rename($this->filePath, $dstFilePath);
        if (!$isSuccess) {
            throw new FileCannotRenameException('ファイルの書き込みに失敗しました');
        }

        $this->filePath = $dstFilePath;
    }

    /**
     * @return string
     */
    public function getDirectory(): string
    {
        return dirname($this->filePath);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return basename($this->filePath);
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->filePath;
    }

    /**
     * @param string $content
     * @return CedynaFile
     */
    public function setSaveContent(string $content)
    {
        $this->saveContent = $content;

        return $this;
    }

    /**
     * @param bool $fileAppend 追記する場合true,上書きする場合false
     *
     * @throws SaveContentIsEmptyException
     * @throws FileWritingFailedException
     */
    public function save(bool $fileAppend = false)
    {
        if ($this->saveContent === null) {
            throw new SaveContentIsEmptyException('保存する内容がありません');
        }

        if (!$this->isSjisSaveContent()) {
            $this->saveContent = mb_convert_encoding($this->saveContent, 'SJIS');
        }

        if ($fileAppend) {
            $result = file_put_contents($this->filePath, $this->saveContent, FILE_APPEND);
        } else {
            $result = file_put_contents($this->filePath, $this->saveContent);
        }

        if ($result === false) {
            throw new FileWritingFailedException('ファイルの書き込みに失敗しました');
        }
    }

    /**
     * ファイルを削除する
     */
    public function remove()
    {
        if (file_exists($this->filePath)) {
            unlink($this->filePath);
        } else {
            Yii::warning('削除対象のファイルが存在しません');
        }
    }

    /**
     * 保存対象がSJISかどうか
     * @fixme ASCII文字のみの場合変数がSJISでもfalseになる
     *
     * @return bool
     */
    public function isSjisSaveContent(): bool
    {
        return mb_detect_encoding($this->saveContent, 'ASCII,JIS,UTF-8,EUC-JP,SJIS', true) === 'SJIS';
    }
}