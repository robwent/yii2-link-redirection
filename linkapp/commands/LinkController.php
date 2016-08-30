<?php

namespace app\commands;

use Yii;
use app\models\Links;
use yii\console\Controller;
use yii\helpers\Html;
use yii\helpers\Url;

class LinkController extends Controller
{
  /**
  * This command echoes what you have entered as the message.
  * @param string $message the message to be echoed.
  */
  public function actionChecklinks()
  {
    $links = new Links();
    $links = $links->find()->where(['status' => 1])->all();
    if($links){
      $errors = [];
      foreach ($links as $link) {

        if (isset($link->full_url) && $link->full_url !== '') {
          $curl = curl_init();
          curl_setopt($curl, CURLOPT_URL, $link->full_url);
          curl_setopt($curl, CURLOPT_HEADER, false);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
          curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //ignores ssl security if we have no ssl cert
          curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
          $response = curl_exec($curl);
          $status = curl_getinfo($curl);
          //var_dump($status);
          if ($status['http_code'] !== 200) {
            $status_code = $status['http_code'];
            $edit_link = HTML::a('Edit', Url::to(['links/update', 'id' => $link->id]), ['target' => '_blank']);
            if ($status_code == 301) {
              $redirect = $status['redirect_url'];
              $errors[] = "The link $link->full_url, directed by the shortlink '$link->short_url', has been permanantly redirected to: $redirect. $edit_link";
            } elseif($status_code == 0) {
              $errors[] = "The link $link->full_url, directed by the shortlink '$link->short_url', was unresponsive. The domain may have temporary issues or it may no longer exist. $edit_link";
            } else {
              $errors[] = "The link $link->full_url, directed by the shortlink '$link->short_url', returned the error code: $status_code. $edit_link";
            }
          }
        }
      }
      $mailto = Yii::$app->params['settings']['mailto'];
      if (count($errors) >= 1 && isset($mailto)) {
        //We have errors so send an email
        $error_string = '<p>The following issues were found with link redirection:</p>';
        $error_string .= implode("<br />", $errors);
        Yii::$app->mailer->compose()
        ->setFrom('no-reply@your-domain.com')
        ->setTo($mailto)
        ->setSubject('Link Redirection Errors')
        ->setTextBody(strip_tags($error_string) )
        ->setHtmlBody($error_string )
        ->send();
      }
    }
  }
}
