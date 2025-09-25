<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jira Issues</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light p-4">

@include('partials.alerts')

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <a href="{{ route('index') }}" class="text-decoration-none text-dark">
                Jira Issues
            </a>
        </h1>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="bi bi-plus-lg"></i> Pridať úlohu
        </button>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        @forelse($issues as $issue)
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

        @empty
            <p class="text-muted">Žiadne úlohy nenájdené.</p>
        @endforelse
    </div>

    @if(!$isLast && $nextPageToken)
        <div class="d-flex justify-content-center mt-4">
            <a href="{{ route('index', ['pageToken' => $nextPageToken]) }}" class="btn btn-outline-primary">
                Ďalšia strana →
            </a>
        </div>
    @endif
</div>

<x-issue-create-modal :issueTypes="$issueTypes" />

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('.load-comments-btn');

        buttons.forEach(button => {
            const issueKey = button.dataset.issueKey;
            const modal = document.getElementById(`commentsModal-${issueKey}`);
            const container = modal.querySelector(`#comments-container-${issueKey}`);
            const form = modal.querySelector(`#add-comment-form-${issueKey}`);

            button.addEventListener('click', function () {
                container.innerHTML = 'Načítavam...';

                fetch(`/issues/${issueKey}/comments`)
                    .then(res => res.json())
                    .then(comments => {
                        container.innerHTML = '';
                        if (comments.length === 0) {
                            container.innerHTML = '<p class="text-muted">Žiadne komentáre.</p>';
                        } else {
                            comments.forEach(comment => {
                                const isAuthor = comment.author.accountId === '{{ $currentUser['accountId'] }}';
                                const div = document.createElement('div');
                                div.classList.add('mb-3');

                                let buttonsHtml = '';
                                if (isAuthor) {
                                    buttonsHtml = `
                                    <button class="btn btn-sm btn-primary save-comment-btn" data-comment-id="${comment.id}">Upraviť</button>
                                    <button class="btn btn-sm btn-danger delete-comment-btn" data-comment-id="${comment.id}">Odstrániť</button>
                                `;
                                }

                                div.innerHTML = `
                                <textarea class="form-control mb-1" rows="2" ${isAuthor ? '' : 'readonly'}>${comment.body.content[0].content[0].text ?? ''}</textarea>
                                ${buttonsHtml}
                            `;
                                container.appendChild(div);

                                if (isAuthor) {
                                    // Edit
                                    div.querySelector('.save-comment-btn').addEventListener('click', function () {
                                        const body = div.querySelector('textarea').value;
                                        fetch(`/issues/${issueKey}/comments/${comment.id}`, {
                                            method: 'PUT',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                            },
                                            body: JSON.stringify({ body })
                                        }).then(res => res.json())
                                            .then(data => {
                                                if (data.success) alert('Komentár upravený');
                                            });
                                    });

                                    // Delete
                                    div.querySelector('.delete-comment-btn').addEventListener('click', function () {
                                        if (!confirm('Naozaj chcete odstrániť tento komentár?')) return;

                                        fetch(`/issues/${issueKey}/comments/${comment.id}`, {
                                            method: 'DELETE',
                                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                                        }).then(res => res.json())
                                            .then(data => {
                                                if (data.success) {
                                                    div.remove();
                                                } else {
                                                    alert((data.errors || ['Nepodarilo sa odstrániť komentár.']).join('\n'));
                                                }
                                            });
                                    });
                                }
                            });
                        }
                    })
                    .catch(() => {
                        container.innerHTML = '<p class="text-danger">Nepodarilo sa načítať komentáre.</p>';
                    });
            });

            // Add new comment
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const body = form.querySelector('textarea').value;

                fetch(`/issues/${issueKey}/comments`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ body })
                })
                    .then(res => res.json().then(data => ({ ok: res.ok, data })))
                    .then(({ ok, data }) => {
                        if (!ok || !data.success) {
                            alert((data.errors || ['Nepodarilo sa pridať komentár.']).join('\n'));
                            return;
                        }

                        form.querySelector('textarea').value = '';
                        const newComment = data.comment;
                        const text = newComment?.body?.content?.[0]?.content?.[0]?.text ?? '';
                        const div = document.createElement('div');
                        div.classList.add('mb-3');
                        div.innerHTML = `
                    <textarea class="form-control mb-1" rows="2">${text}</textarea>
                    <button class="btn btn-sm btn-primary save-comment-btn">Upraviť</button>
                    <button class="btn btn-sm btn-danger delete-comment-btn">Odstrániť</button>
                `;
                        container.appendChild(div);

                        // Attach edit/delete listeners to the newly added comment
                        div.querySelector('.save-comment-btn').addEventListener('click', function () {
                            const body = div.querySelector('textarea').value;
                            fetch(`/issues/${issueKey}/comments/${newComment.id}`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ body })
                            }).then(res => res.json())
                                .then(data => { if (data.success) alert('Komentár upravený'); });
                        });
                        div.querySelector('.delete-comment-btn').addEventListener('click', function () {
                            if (!confirm('Naozaj chcete odstrániť tento komentár?')) return;
                            fetch(`/issues/${issueKey}/comments/${newComment.id}`, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                            }).then(res => res.json())
                                .then(data => { if (data.success) div.remove(); });
                        });
                    })
                    .catch(err => console.error(err));
            });
        });
    });
</script>

</body>
</html>
