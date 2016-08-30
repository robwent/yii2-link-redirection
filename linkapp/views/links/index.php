<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\editable\Editable;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\LinksSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Links';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="links-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Links', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php Pjax::begin(); 
    $gridColumns = [
    // the detail column configuration - expands the row
    [
      'class' => 'kartik\grid\ExpandRowColumn',
      'value' => function ($model, $key, $index) {
        return GridView::ROW_COLLAPSED;
      },
      'detail' => function ($model, $key, $index, $column) {
        return Yii::$app->controller->renderPartial('_link-details', ['model'=>$model]);
      },
      'attribute' => ''
    ],
    // the short_url column configuration
    [
      'class' => 'kartik\grid\EditableColumn',
      'attribute' => 'short_url',
      'pageSummary' =>true,
      'editableOptions'=> function ($model, $key, $index) {
        return [
          'header' => 'Name',
          'size' => 'sm',
        ];
      }
    ],
    // the full_url column configuration
    [
      'class' => 'kartik\grid\EditableColumn',
      'attribute' => 'full_url',
      'editableOptions'=>[
        'header' => 'Target URL',
        'inputType'=> Editable::INPUT_TEXT,
      ],
      'pageSummary'=>true
    ],
    // the status toggle configuration
    [
      'class' => 'kartik\grid\BooleanColumn',
      'attribute' => 'status',
      'vAlign'=>'middle',
    ],
    [
      'class' => 'kartik\grid\ActionColumn',
      'template' => '{update} {delete}'
    ]
  ];
  ?>
  <?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $gridColumns,
    'export' => false,
    'pjax' => true
  ]); ?>
    <?php Pjax::end(); ?>
</div>
