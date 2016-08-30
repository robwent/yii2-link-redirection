<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
?>
<div class="link-detail">
  <h4>Date Added: <?= \Yii::$app->formatter->asDatetime($model->published, "php:d-m-Y H:i:s"); ?></h4>
  <p><?= $model->description; ?></p>
</div>
