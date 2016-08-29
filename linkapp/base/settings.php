<?php
namespace app\base;

use Yii;
use yii\base\BootstrapInterface;

/*
/* The base class that you use to retrieve the settings from the database
*/

class settings implements BootstrapInterface {

  private $db;

  public function __construct() {
    $this->db = Yii::$app->db;
  }

  /**
  * Bootstrap method to be called during application bootstrap stage.
  * Loads all the settings into the Yii::$app->params array
  * @param Application $app the application currently running
  */

  public function bootstrap($app) {

    // Get settings from database
    $sql = $this->db->createCommand("SELECT setting_name,setting_value FROM settings");
    $settings = $sql->queryAll();

    // Now let's load the settings into the global params array

    foreach ($settings as $key => $val) {
      Yii::$app->params['settings'][$val['setting_name']] = $val['setting_value'];
    }

  }

}
