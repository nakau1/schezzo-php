<?php
namespace app\components;

/**
 * 本番以外の環境で、Hulftクラスの代替として注入される
 * @see Hulft
 * @package app\components
 */
class HulftDummy extends Hulft
{
    /**
     * @inheritdoc
     */
    protected function execOnHulftServer(string $command, &$output = null, &$status = null)
    {
        // 何もしない（正常終了）
        $output = '';
        $status = 0;

        return;
    }
}
