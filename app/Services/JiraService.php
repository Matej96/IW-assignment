<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class JiraService
{
    protected string $baseUrl;
    protected array $auth;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('JIRA_HOST'), '/') . '/rest/api/3';
        $this->auth = [env('JIRA_EMAIL'), env('JIRA_API_TOKEN')];
    }

    protected function request(string $method, string $url, array $payload = []): Response
    {
        return Http::withBasicAuth(...$this->auth)
            ->acceptJson()
            ->withoutVerifying()
            ->$method($this->baseUrl . $url, $payload);
    }

    protected function docText(string $text): array
    {
        return [
            'type' => 'doc',
            'version' => 1,
            'content' => [[
                'type' => 'paragraph',
                'content' => [['type' => 'text', 'text' => $text]],
            ]],
        ];
    }

    /**
     * Make a request and return structured result with success and errors
     */
    protected function requestWithResult(string $method, string $url, array $payload = []): array
    {
        $response = $this->request($method, $url, $payload);

        if ($response->successful()) {
            return [
                'success' => true,
                'data' => $response->json() ?? []
            ];
        }

        return [
            'success' => false,
            'errors' => $response->json()['errorMessages'] ?? ['Unknown error'],
            'data' => $response->json() ?? []
        ];
    }

    public function getAllIssues(string $projectKey = 'IT', int $maxResults = 9, ?string $pageToken = null): array
    {
        $payload = [
            'jql' => "project = {$projectKey} ORDER BY created DESC",
            'maxResults' => $maxResults,
            'fields' => [
                'summary', 'description', 'status',
                'issuetype', 'reporter', 'created'
            ],
        ];

        if ($pageToken) {
            $payload['nextPageToken'] = $pageToken;
        }

        $result = $this->requestWithResult('post', '/search/jql', $payload);

        if (!$result['success']) {
            return [
                'issues' => [],
                'nextPageToken' => null,
                'isLast' => true,
            ];
        }

        return [
            'issues' => $result['data']['issues'] ?? [],
            'nextPageToken' => $result['data']['nextPageToken'] ?? null,
            'isLast' => $result['data']['isLast'] ?? true,
        ];
    }

    public function createIssue(array $data): array
    {
        $payload = [
            'fields' => [
                'project' => ['key' => env('JIRA_PROJECT', 'IT')],
                'summary' => $data['summary'],
                'description' => $this->docText($data['description'] ?? ''),
                'issuetype' => ['id' => $data['issuetype']],
            ],
        ];

        return $this->requestWithResult('post', '/issue', $payload);
    }

    public function updateIssue(string $issueKey, array $fields): array
    {
        if (isset($fields['description'])) {
            $fields['description'] = $this->docText($fields['description']);
        }

        if (isset($fields['issuetype'])) {
            $fields['issuetype'] = ['id' => $fields['issuetype']];
        }

        return $this->requestWithResult('put', "/issue/{$issueKey}", ['fields' => $fields]);
    }

    public function deleteIssue(string $issueKey): array
    {
        return $this->requestWithResult('delete', "/issue/{$issueKey}");
    }

    public function addComment(string $issueKey, string $body): array
    {
        return $this->requestWithResult('post', "/issue/{$issueKey}/comment", [
            'body' => $this->docText($body),
        ]);
    }

    public function updateComment(string $issueKey, string $commentId, string $body): array
    {
        return $this->requestWithResult('put', "/issue/{$issueKey}/comment/{$commentId}", [
            'body' => $this->docText($body),
        ]);
    }

    public function deleteComment(string $issueKey, string $commentId): array
    {
        $result = $this->requestWithResult('delete', "/issue/{$issueKey}/comment/{$commentId}");

        return [
            'success' => $result['success'] ?? false,
            'errors' => $result['errors'] ?? ($result['data']['errorMessages'] ?? []),
        ];
    }


    public function getComments(string $issueKey): array
    {
        $result = $this->requestWithResult('get', "/issue/{$issueKey}/comment");

        return $result['success'] ? ($result['data']['comments'] ?? []) : [];
    }

    public function getIssueTypes(): array
    {
        $result = $this->requestWithResult('get', '/issuetype');
        if (!$result['success']) return [];

        return array_filter($result['data'] ?? [], fn($t) => !$t['subtask'] && $t['hierarchyLevel'] == 0);
    }

    public function getStatuses(): array
    {
        $result = $this->requestWithResult('get', '/status');

        return $result['success'] ? ($result['data'] ?? []) : [];
    }

    public function getCurrentUser(): array
    {
        $result = $this->requestWithResult('get', '/myself');

        return $result['success'] ? ($result['data'] ?? []) : [];
    }
}
