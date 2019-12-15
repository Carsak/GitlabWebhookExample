<?php
namespace App\Webhook;

class Response
{
    /** @var string */
    protected $response;

    public function __construct(string $jsonResponse)
    {
        $this->response = json_decode($jsonResponse, true);
    }

    /** Имя ветки */
    public function getBranchName(): string
    {
        $branchName = $this->response['object_attributes']['source_branch'];

        return $branchName;
    }

    /**
     * Действие с мерж реквестом, вызвавшее вебхук
     * open - мерж реквест был создан
     * update - мерж реквест был обновлен
     * null - мерж реквест был замержен
     *
     * @return string
     */
    public function getActionName(): string
    {
        $action = $this->response['object_attributes']['action'] ?? '';

        return $action;
    }

    /** Распарсить номер задачи из имени ветки
     * Например, имя ветки wh-1111-some-description, здесь 1111 - это номер задачи
     */
    public function getIssueId(): int
    {
        $pattern = '#(?P<issueId>[\d]+)#';
        preg_match($pattern, $this->getBranchName(), $matches);
        $issueId = $matches['issueId'];

        return $issueId;
    }

    /** Запрос был отправлен при создании мерж реквеста */
    public function isMergeRequestOpened(): bool
    {
        $isOpened = $this->getActionName() === 'open';

        return $isOpened;
    }

    public function getMergeRequestUrl(): string
    {
        $url = $this->response['object_attributes']['url'];

        return $url;
    }
}
