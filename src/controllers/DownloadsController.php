<?php
namespace highlanddev\rapiddownload\controllers;

use Craft;
use craft\web\Controller;
use yii\web\Response;

class DownloadsController extends Controller
{
    // DownloadsController.php
    public function actionIndex(): Response
    {
        $pageSize = 50;
        $currentPage = Craft::$app->request->getQueryParam('page', 1);

        $query = (new \craft\db\Query())
            ->select(['*'])
            ->from('{{%rapiddownload_downloads}}')
            ->orderBy(['dateCreated' => SORT_DESC]);

        if ($pageUrl = Craft::$app->request->getQueryParam('pageUrl')) {
            $query->andWhere(['like', 'pageUrl', $pageUrl]);
        }

        if ($search = Craft::$app->request->getQueryParam('search')) {
            $query->andWhere(['or',
                ['like', 'email', $search],
                ['like', 'filenames', $search]
            ]);
        }

        $totalDownloads = $query->count();
        $downloads = $query
            ->offset(($currentPage - 1) * $pageSize)
            ->limit($pageSize)
            ->all();

        if (Craft::$app->request->getQueryParam('download') === 'csv') {
            return $this->downloadCsv($query->all());
        }

        return $this->renderTemplate('rapid-download/downloads/index', [
            'downloads' => $downloads,
            'pageUrl' => $pageUrl,
            'search' => $search,
            'totalPages' => ceil($totalDownloads / $pageSize),
            'currentPage' => $currentPage
        ]);
    }

    private function downloadCsv($downloads): Response
    {
        $rows = [];
        $rows[] = ['Date', 'Email', 'Page URL', 'Files']; // Headers

        foreach ($downloads as $download) {
            $rows[] = [
                $download['dateCreated'],
                $download['email'],
                $download['pageUrl'],
                $download['filenames']
            ];
        }

        $tempFile = tmpfile();
        foreach ($rows as $row) {
            fputcsv($tempFile, $row);
        }

        $metadata = stream_get_meta_data($tempFile);
        $csvContent = file_get_contents($metadata['uri']);
        fclose($tempFile);

        $response = Craft::$app->getResponse();
        $response->headers->add('Content-Type', 'text/csv');
        $response->headers->add('Content-Disposition', 'attachment;filename=downloads.csv');
        $response->content = $csvContent;

        return $response;
    }
}