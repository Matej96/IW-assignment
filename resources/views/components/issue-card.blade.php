@props(['issue', 'isReporter', 'badgeColor', 'description', 'issueTypes', 'currentUser'])

<div class="col">
    <div class="card h-100 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">{{ $issue['key'] }} - {{ $issue['fields']['summary'] }}</h5>

                @if($isReporter)
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-secondary load-comments-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#commentsModal-{{ $issue['key'] }}"
                                data-issue-key="{{ $issue['key'] }}">
                            <i class="bi bi-chat-left-text"></i>
                        </button>

                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#editModal-{{ $issue['key'] }}">
                            <i class="bi bi-pencil"></i>
                        </button>

                        <form action="{{ route('issues.destroy', $issue['key']) }}" method="POST"
                              onsubmit="return confirm('Naozaj chcete odstrániť toto issue?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <h6 class="card-subtitle mt-1 mb-2 text-muted d-flex align-items-center gap-1">
                Typ: {{ $issue['fields']['issuetype']['name'] }}
                <img src="{{ $issue['fields']['issuetype']['iconUrl'] }}" alt="icon" width="16" height="16">
                | Stav:
                <span class="badge text-bg-{{ $badgeColor }}">
                    {{ $issue['fields']['status']['name'] ?? 'Unknown' }}
                </span>
            </h6>

            <p class="card-text mb-1">Zadávatel: {{ $issue['fields']['reporter']['displayName'] ?? 'N/A' }}</p>
            <p class="card-text text-muted" style="font-size: 0.85rem;">
                Vytvořené: {{ \Carbon\Carbon::parse($issue['fields']['created'])->format('d.m.Y H:i') }}
            </p>
        </div>
    </div>

    <x-issue-comments-modal :issue="$issue" :description="$description" :currentUser="$currentUser" />
    <x-issue-edit-modal :issue="$issue" :description="$description" :issueTypes="$issueTypes" />
</div>
