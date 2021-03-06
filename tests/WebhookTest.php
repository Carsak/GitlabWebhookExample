<?php

use App\Webhook\Response;
use Redmine\Client;

class WebhookTest extends TestCase
{

    /** проверка класса-парсера запроса вебхука */
    public function testWebhookResponse()
    {
        $webhookResponse = new Response($this->getFakeResponseFromWebhook());

        $this->assertTrue($webhookResponse->getBranchName() === 'ma-851250-viewport', "Проверка, что из запроса корректно получено имя ветки");
        $this->assertTrue($webhookResponse->getActionName() === 'open', "Проверка, что из запроса корректно получено имя события/действия");
        $this->assertTrue($webhookResponse->getIssueId() === 851250, "Проверка, что корректно получено номер задачи из имени ветки");
        $this->assertTrue($webhookResponse->isMergeRequestOpened(), "Проверка, что событие вызвавшее веб хук это создание нового мерж реквеста");

    }

    /**
     * Если обновление прошло успешно
     */
    public function testRedmineIssueUpdatedSuccess()
    {
        $this->json('POST', '/redmine/updateIssue', json_decode($this->getFakeResponseFromWebhook(), true));

        $this->assertResponseOk();
        $this->assertEquals(
            'Обновление прошло успешно', $this->response->getContent()
        );
    }

    /**
     * Если вебхук прилетел при обновлении уже существующего мерж реквеста
     */
    public function testRedmineIssueUpdatedNegative()
    {
        $body = json_decode($this->getFakeResponseFromWebhook(), true);
        $body['object_attributes']['action'] = 'updated';
        $this->json('POST', '/redmine/updateIssue', $body);

        $this->assertResponseStatus(400);
        $this->assertEquals(
            'Обновлять можно только при открытий Мерж реквеста', $this->response->getContent()
        );
    }

    /**
     * Если пришла ошибка от редмайна
     */
    public function testRedmineIssueUpdatingFail()
    {
        $body = json_decode($this->getFakeResponseFromWebhook(), true);
        //Мок редмайн клиента, регистрация мока в приложении
        $this->app->singleton(Client::class, function () {
            $mock = $this->createMock(Client::class);
            $mockApi = $this->createMock(Redmine\Api\Issue::class);
            $mockApi->method('show')->willReturn(['issue' => ['description' => 'Some text']]);
            $mockApi->method('update')->willReturn('Some error text');
            $mock->issue = $mockApi;

            return $mock;
        });
        $this->json('POST', '/redmine/updateIssue', $body);

        $this->assertResponseStatus(400);
        $this->assertEquals(
            'Some error text', $this->response->getContent()
        );
    }

    /**
     * Данные от вебхука
     * @return string
     */
    private function getFakeResponseFromWebhook(): string
    {
        // пример запроса от Вебхука при событии с мерж реквестами
        return
                    '{
          "object_kind": "merge_request",
          "user": {
            "name": "Administrator",
            "username": "root",
            "avatar_url": "http://www.gravatar.com/avatar/e64c7d89f26bd1972efa854d13d7dd61?s=40\u0026d=identicon"
          },
          "project": {
            "id": 1,
            "name":"Gitlab Test",
            "description":"Aut reprehenderit ut est.",
            "web_url":"http://example.com/gitlabhq/gitlab-test",
            "avatar_url":null,
            "git_ssh_url":"git@example.com:gitlabhq/gitlab-test.git",
            "git_http_url":"http://example.com/gitlabhq/gitlab-test.git",
            "namespace":"GitlabHQ",
            "visibility_level":20,
            "path_with_namespace":"gitlabhq/gitlab-test",
            "default_branch":"master",
            "homepage":"http://example.com/gitlabhq/gitlab-test",
            "url":"http://example.com/gitlabhq/gitlab-test.git",
            "ssh_url":"git@example.com:gitlabhq/gitlab-test.git",
            "http_url":"http://example.com/gitlabhq/gitlab-test.git"
          },
          "repository": {
            "name": "Gitlab Test",
            "url": "http://example.com/gitlabhq/gitlab-test.git",
            "description": "Aut reprehenderit ut est.",
            "homepage": "http://example.com/gitlabhq/gitlab-test"
          },
          "object_attributes": {
            "id": 99,
            "target_branch": "master",
            "source_branch": "ma-851250-viewport",
            "source_project_id": 14,
            "author_id": 51,
            "assignee_id": 6,
            "title": "MS-Viewport",
            "created_at": "2013-12-03T17:23:34Z",
            "updated_at": "2013-12-03T17:23:34Z",
            "milestone_id": null,
            "state": "opened",
            "merge_status": "unchecked",
            "target_project_id": 14,
            "iid": 1,
            "description": "",
            "source": {
              "name":"Awesome Project",
              "description":"Aut reprehenderit ut est.",
              "web_url":"http://example.com/awesome_space/awesome_project",
              "avatar_url":null,
              "git_ssh_url":"git@example.com:awesome_space/awesome_project.git",
              "git_http_url":"http://example.com/awesome_space/awesome_project.git",
              "namespace":"Awesome Space",
              "visibility_level":20,
              "path_with_namespace":"awesome_space/awesome_project",
              "default_branch":"master",
              "homepage":"http://example.com/awesome_space/awesome_project",
              "url":"http://example.com/awesome_space/awesome_project.git",
              "ssh_url":"git@example.com:awesome_space/awesome_project.git",
              "http_url":"http://example.com/awesome_space/awesome_project.git"
            },
            "target": {
              "name":"Awesome Project",
              "description":"Aut reprehenderit ut est.",
              "web_url":"http://example.com/awesome_space/awesome_project",
              "avatar_url":null,
              "git_ssh_url":"git@example.com:awesome_space/awesome_project.git",
              "git_http_url":"http://example.com/awesome_space/awesome_project.git",
              "namespace":"Awesome Space",
              "visibility_level":20,
              "path_with_namespace":"awesome_space/awesome_project",
              "default_branch":"master",
              "homepage":"http://example.com/awesome_space/awesome_project",
              "url":"http://example.com/awesome_space/awesome_project.git",
              "ssh_url":"git@example.com:awesome_space/awesome_project.git",
              "http_url":"http://example.com/awesome_space/awesome_project.git"
            },
            "last_commit": {
              "id": "da1560886d4f094c3e6c9ef40349f7d38b5d27d7",
              "message": "fixed readme",
              "timestamp": "2012-01-03T23:36:29+02:00",
              "url": "http://example.com/awesome_space/awesome_project/commits/da1560886d4f094c3e6c9ef40349f7d38b5d27d7",
              "author": {
                "name": "GitLab dev user",
                "email": "gitlabdev@dv6700.(none)"
              }
            },
            "work_in_progress": false,
            "url": "http://example.com/diaspora/merge_requests/1",
            "action": "open",
            "assignee": {
              "name": "User1",
              "username": "user1",
              "avatar_url": "http://www.gravatar.com/avatar/e64c7d89f26bd1972efa854d13d7dd61?s=40\u0026d=identicon"
            }
          },
          "labels": [{
            "id": 206,
            "title": "API",
            "color": "#ffffff",
            "project_id": 14,
            "created_at": "2013-12-03T17:15:43Z",
            "updated_at": "2013-12-03T17:15:43Z",
            "template": false,
            "description": "API related issues",
            "type": "ProjectLabel",
            "group_id": 41
          }],
          "changes": {
            "updated_by_id": {
              "previous": null,
              "current": 1
            },
            "updated_at": {
              "previous": "2017-09-15 16:50:55 UTC",
              "current":"2017-09-15 16:52:00 UTC"
            },
            "labels": {
              "previous": [{
                "id": 206,
                "title": "API",
                "color": "#ffffff",
                "project_id": 14,
                "created_at": "2013-12-03T17:15:43Z",
                "updated_at": "2013-12-03T17:15:43Z",
                "template": false,
                "description": "API related issues",
                "type": "ProjectLabel",
                "group_id": 41
              }],
              "current": [{
                "id": 205,
                "title": "Platform",
                "color": "#123123",
                "project_id": 14,
                "created_at": "2013-12-03T17:15:43Z",
                "updated_at": "2013-12-03T17:15:43Z",
                "template": false,
                "description": "Platform related issues",
                "type": "ProjectLabel",
                "group_id": 41
              }]
            }
          }
        }';

    }
}
