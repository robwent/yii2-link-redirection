<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Links */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="links-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'short_url')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'full_url')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'status')->checkbox() ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'published')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
