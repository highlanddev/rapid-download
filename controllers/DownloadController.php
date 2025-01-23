<?php
namespace highlanddev\rapiddownload\controllers;

use Craft;
use craft\web\Controller;
use yii\web\Response;
use craft\mail\Message;

class DownloadController extends Controller
{
    protected array|bool|int $allowAnonymous = true;

    public function actionSend(): Response
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();

        $email = $request->getRequiredParam('email');
        $assets = json_decode($request->getRequiredParam('assets'), true);

        $message = new Message();
        $message->setTo($email);
        $message->setSubject('Your Requested Downloads');

        $body = "Here are your download links:\n\n";
        foreach ($assets as $asset) {
            $body .= Craft::$app->assets->getAssetById($asset['id'])->getUrl() . "\n";
        }

        $message->setTextBody($body);
        Craft::$app->getMailer()->send($message);

        return $this->asJson(['success' => true]);
    }
}