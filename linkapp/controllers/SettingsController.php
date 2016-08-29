<?php

namespace app\controllers;

use Yii;
use yii\base\Model;
use yii\web\Controller;
use app\models\Settings;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

class SettingsController extends \yii\web\Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
          'access' => [
            'class' => AccessControl::className(),
            'rules' => [
              [
                'actions' => ['login', 'error', 'view'],
                'allow' => true,
              ],
              [
                'actions' => ['logout', 'update'],
                'allow' => true,
                'roles' => ['@'],
              ],
            ],
          ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionUpdate()
    {
        $settings = Settings::find()->indexBy('id')->all();

        if (Model::loadMultiple($settings, Yii::$app->request->post()) && Model::validateMultiple($settings)) {
            foreach ($settings as $setting) {
                $setting->save(false);
            }
        }

        return $this->render('update', ['settings' => $settings]);
    }

}
