<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Links */

//Get the full url passed byt the controller
$full_url = $model->full_url;

//Check to see if our settings contain status code and robots headers or set defaults
$status_code = Yii::$app->params['settings']['statuscode'] ? Yii::$app->params['settings']['statuscode'] : 303;
$robots = Yii::$app->params['settings']['robots'] ? Yii::$app->params['settings']['robots'] : null;

//If we have an analytics code, send an event
if ($analytics_id = Yii::$app->params['settings']['analytics']) {
  // unique identifier from the users ip address
  //The docs say that the CID “must not itself be PII (personally identifiable information)” but should take the form of a UUID
  $identifier = crc32($_SERVER['REMOTE_ADDR']);
  $fields = array(
    "v" => "1", //Version
    "tid" => $analytics_id, //Tracking ID
    "t" => "event", //Event Hit Type
    "ec" => "redirection", //Event category
    "ea" => $full_url, //Event action
    "cid" => $identifier, // Client ID/UUID
  );
  $fieldsString = http_build_query($fields);
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "https://www.google-analytics.com/collect");
  curl_setopt($ch, CURLOPT_POST, count($fields));
  curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsString);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_exec($ch);
  curl_close($ch);
}

//Redirect the user
if($robots){
  header("X-Robots-Tag: $robots", true);
}
header('Location: ' . $full_url, true, (int)$status_code);
exit();
?>