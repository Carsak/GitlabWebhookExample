<?php

namespace App\Http\Controllers;

use App\Webhook\Credential;
use App\Webhook\Response as WebhookResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Redmine\Client;

class RedmineController extends Controller
{
    /** @var Credential */
    private $credential;

    /** @var Client */
    private $redmineClient;

    /**
     * RedmineController constructor.
     *
     * @param \App\Webhook\Credential $credential
     * @param \Redmine\Client $redmineClient
     */
    public function __construct(\App\Webhook\Credential $credential, \Redmine\Client $redmineClient)
    {
        $this->credential = $credential;
        $this->redmineClient = $redmineClient;
    }

    public function updateIssue(Request $request)
    {
        $body = $request->getContent();

        $webhookResponse = new WebhookResponse($body);
        $issueId = $webhookResponse->getIssueId();

        if ($webhookResponse->isMergeRequestOpened()){
            // Подключение к Redmine
            $redmine = new Client($this->credential->getRedmineHost(), $this->credential->getBotApiKey());

// Можнно получить данные о задаче из Redmine
            $issue = $redmine->issue->show($issueId);
            $description = $issue['issue']['description'];
// Подготовим текст для issue description
            $appendedText = PHP_EOL . "Был создан новый мерж реквест " . $webhookResponse->getMergeRequestUrl() . PHP_EOL;

            $errorMessage = $redmine->issue->update($issueId, ['description' => $description . $appendedText]);
        } else {
            $errorMessage = 'Обновлять можно только при открытий Мерж реквеста';
        }

        if (empty($errorMessage)) {
            $code = 200;
            $msg = 'Обновление прошло успешно';
        } else {
            $code = 400;
            $msg = $errorMessage;
        }

        $response = new HttpResponse($msg, $code);

        return $response;
    }
}
