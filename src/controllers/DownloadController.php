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
        $assetsData = json_decode($request->getRequiredParam('assets'), true);
        $assetId = $assetsData['where']['elements.id'][0] ?? null;

        if ($assetId && $asset = Craft::$app->elements->getElementById($assetId)) {
            // Record download
            Craft::$app->db->createCommand()->insert('{{%rapiddownload_downloads}}', [
                'email' => $email,
                'pageUrl' => $request->getReferrer(),
                'filenames' => $asset->title,
                'dateCreated' => (new \DateTime())->format('Y-m-d H:i:s'),
                'dateUpdated' => (new \DateTime())->format('Y-m-d H:i:s'),
                'uid' => \craft\helpers\StringHelper::UUID(),
            ])->execute();

            // Send email
            $message = new Message();
            $message->setTo($email);
            $message->setSubject('Your Requested Downloads');

            $body = "Your digital assets are ready for download:\n\n<ul>";
            $body .= "<li><a href='" . $asset->getUrl() . "'>" . $asset->title . "</a></li>";
            $body .= "</ul>";

            $message->setHtmlBody($body);
            Craft::$app->getMailer()->send($message);
        }

        return $this->redirect($request->getReferrer());
    }
}