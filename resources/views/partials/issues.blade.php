@foreach($issues as $issue)
    @php
        $isReporter = $currentUser['accountId'] === ($issue['fields']['reporter']['accountId'] ?? null);
        $statusColors = [
            '10000' => 'secondary',
            '10001' => 'primary',
            '10002' => 'success',
        ];
        $statusId = $issue['fields']['status']['id'] ?? '10000';
        $badgeColor = $statusColors[$statusId] ?? 'secondary';
        $description = data_get($issue, 'fields.description.content.0.content.0.text', '');
    @endphp

    <x-issue-card :issue="$issue"
                  :isReporter="$isReporter"
                  :badgeColor="$badgeColor"
                  :description="$description"
                  :issueTypes="$issueTypes"
                  :currentUser="$currentUser" />
@endforeach
