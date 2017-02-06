<?php
namespace app\widgets;

/**
 * エラー表示対応用のカスタムなActiveFormクラス
 * @package app\widgets
 */
class ActiveForm extends \yii\widgets\ActiveForm
{
    public $fieldClass = 'app\widgets\ActiveField';

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->enableClientScript) {
            $css = <<<CSS
                .err_text {
                    display: none;
                }
                .has-error .err_text {
                    display: block;
                }
                .err_text {
                    margin-bottom: 5px;
                }
CSS;
            $view = $this->getView();
            $view->registerCss($css);
        }
        parent::run();
    }
}