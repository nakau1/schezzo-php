<?php
namespace app\modules\admin\extensions;

use Yii;
use yii\helpers\Html;
use yii\widgets\InputWidget;

/**
 * Class JQueryDateTimePicker
 * @package app\modules\admin\extensions
 * @see http://xdsoft.net/jqplugins/datetimepicker/
 * @see JQueryDateTimePickerAsset
 */
class JQueryDateTimePicker extends InputWidget
{
    const DATE_FORMAT = 'Y-m-d H:i';

    public $clientOptions = [];

    public $htmlOptions = [];

    public function run()
    {
        // fix value
        if ($this->hasModel()) {
            $value = Html::getAttributeValue($this->model, $this->attribute);
        } else {
            $value = $this->value;
        }
        if ($value !== null && $value !== '') {
            try {
                if ($value instanceof \DateTime) {
                    $value = date(self::DATE_FORMAT, $value->getTimestamp());
                }
                else if (is_string($value)) {
                    $value = date(self::DATE_FORMAT, strtotime($value));
                }
            } catch(\Exception $e) {
                // ignore
            }
        }

        // html options
        $defaultHtmlOptions = [
            'class'    => 'form-control',
            'readonly' => true,
            'value'    => $value,
        ];
        $htmlOptions = array_merge($defaultHtmlOptions, $this->htmlOptions);

        // client options
        $defaultClientOptions = [
            'lang'        => Yii::$app->language,
            'yearStart'   => date('Y'),
            'yearEnd'     => date('Y') + 1,
            'defaultTime' => '00:00',
            'format'      => self::DATE_FORMAT,
            'value'       => $value,
        ];
        $clientOptions = array_merge($defaultClientOptions, $this->clientOptions);

        if ($this->hasModel()) {
            echo Html::activeTextInput($this->model, $this->attribute, $htmlOptions);
        } else {
            echo Html::textInput($this->name, $this->value, $htmlOptions);
        }

        JQueryDateTimePickerAsset::register($this->getView());

        $js = '$("#' . $this->options['id'] . '").datetimepicker(' . json_encode($clientOptions) . ');';
        $this->getView()->registerJs($js);
    }
}
