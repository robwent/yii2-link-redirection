<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */
?>
<h1>Settings</h1>

<?php
$form = ActiveForm::begin([
  'id' => 'settings-form',
  'options' => ['class' => 'form-horizontal'],
]);

foreach ($settings as $index => $setting) {
  switch ($setting->setting_type) {
    case 'textarea':
    echo $form->field($setting, "[$index]setting_value")->label($setting->setting_name)->textarea();
    break;

    case 'checkbox':
    echo $form->field($setting, "[$index]setting_value")->label($setting->setting_name)->checkbox([0,1], false);
    break;

    default:
    echo $form->field($setting, "[$index]setting_value")->label($setting->setting_name);
    break;
  }

}
?>
<?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>

<?php ActiveForm::end() ?>

<h3>CRON Command for Link Checking</h3>
<p>The following command will check each full url to make sure that there are no errors.</p>
<code>/path/to/php <?= Yii::getAlias('@app') ?>/yii link/checklinks</code>
