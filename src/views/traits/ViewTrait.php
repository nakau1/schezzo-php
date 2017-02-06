<?php
namespace app\views\traits;

use yii\helpers\Html;

/**
 * Class ViewTrait
 * @package app\views\traits
 */
trait ViewTrait
{
    /**
     * シンプルな画像タグ(img)を生成する
     * @param string $fileName ファイル名(拡張子なし)
     * @param array $options 属性オプション
     *
     * 'extension'を設定すると拡張子を変更できる(デフォルトはpng)
     *
     * @return string 画像タグ
     */
    public function img($fileName, $options = [])
    {
        $ext = 'png';
        if (isset($options['extension'])) {
            $ext = $options['extension'];
            unset($options['extension']);
        }

        return Html::img('/img/'. $fileName .'.' . $ext, $options);
    }

    /**
     * アンカータグでのボタンを生成する
     * @param string $text ボタンの文字列
     * @param array $options オプション
     * @return string アンカータグ(a)
     */
    public function anchorButton($text, $options = [])
    {
        return Html::a($text, self::JS_VOID, $options);
    }

    /**
     * 画像タグを囲ったアンカータグを生成する
     * @param string $fileName img()メソッドを参照
     * @param array|null|string $url Url::to()を参照
     * @param array $imgOptions imgタグのオプション
     * @param array $anchorOptions aタグのオプション
     * @return string アンカータグ(a)
     */
    public function anchorImg($fileName, $url, $imgOptions = [], $anchorOptions = [])
    {
        return Html::a($this->img($fileName, $imgOptions), $url, $anchorOptions);
    }
}
