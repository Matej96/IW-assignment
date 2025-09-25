<?php

namespace App\Http\Controllers;

use App\Services\JiraService;
use Illuminate\Http\Request;

class JiraController extends Controller
{
    protected JiraService $jira;

    public function __construct(JiraService $jira)
    {
        $this->jira = $jira;
    }

    public function index(Request $request)
    {
        $pageToken = $request->query('pageToken');

        $result = $this->jira->getAllIssues('IT', 9, $pageToken);

        $issues = $result['issues'];
        $nextPageToken = $result['nextPageToken'];
        $isLast = $result['isLast'];

        $issueTypes = $this->jira->getIssueTypes();
        $issueStatuses = $this->jira->getStatuses();
        $currentUser = $this->jira->getCurrentUser();

        foreach ($issues as &$issue) {
            $issue['comments'] = $this->jira->getComments($issue['key']);
        }

        return view('index', compact(
            'issues',
            'issueTypes',
            'issueStatuses',
            'currentUser',
            'nextPageToken',
            'isLast'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateIssue($request);
        $result = $this->jira->createIssue($validated);

        return $this->redirectWithResult($result, 'Úloha bola vytvorená.', 'Nepodarilo sa vytvoriť úlohu.');
    }

    public function update(Request $request, string $issueKey)
    {
        $validated = $this->validateIssue($request);
        $result = $this->jira->updateIssue($issueKey, $validated);

        return $this->redirectWithResult($result, 'Úloha bola upravená.', 'Nepodarilo sa upraviť úlohu.');
    }

    public function destroy(string $key)
    {
        $result = $this->jira->deleteIssue($key);
        return $this->redirectWithResult($result, "Issue $key bolo odstránené.", "Nepodarilo sa odstrániť Issue $key.");
    }

    public function storeComment(Request $request, string $issueKey)
    {
        $validated = $request->validate(['body' => 'required|string']);
        $result = $this->jira->addComment($issueKey, $validated['body']);

        return $this->redirectWithResult($result, 'Komentár pridaný.', 'Nepodarilo sa pridať komentár.');
    }

    public function updateComment(Request $request, string $issueKey, string $commentId)
    {
        $validated = $request->validate(['body' => 'required|string']);
        $result = $this->jira->updateComment($issueKey, $commentId, $validated['body']);

        return $this->redirectWithResult($result, 'Komentár upravený.', 'Nepodarilo sa upraviť komentár.');
    }

    /* ---------------------- Helpers ---------------------- */

    private function validateIssue(Request $request): array
    {
        return $request->validate([
            'summary' => 'required|string|max:255',
            'description' => 'nullable|string',
            'issuetype' => 'required|string',
        ]);
    }

    private function redirectWithResult(array $result, string $successMessage, string $fallbackError)
    {
        if ($result['success'] ?? false) {
            return redirect()->back()->with('success', $successMessage);
        }

        return redirect()->back()->withErrors($result['errors'] ?? [$fallbackError]);
    }
}
