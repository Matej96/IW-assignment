<script>
    document.addEventListener('DOMContentLoaded', function () {
        const issuesContainer = document.getElementById('issues-container');
        const loadMoreBtn = document.getElementById('load-more');

        // -------------------- Load More --------------------
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function () {
                const token = loadMoreBtn.dataset.token;
                fetch(`{{ route('issues.loadMore') }}?pageToken=${token}`)
                    .then(res => res.json())
                    .then(data => {
                        issuesContainer.insertAdjacentHTML('beforeend', data.html);
                        if (data.isLast || !data.nextPageToken) loadMoreBtn.remove();
                        else loadMoreBtn.dataset.token = data.nextPageToken;
                    })
                    .catch(err => console.error(err));
            });
        }

        // -------------------- Delegated Comments --------------------
        issuesContainer.addEventListener('click', function (e) {
            // Open comments modal
            const loadBtn = e.target.closest('.load-comments-btn');
            if (loadBtn) {
                const issueKey = loadBtn.dataset.issueKey;
                const modal = document.getElementById(`commentsModal-${issueKey}`);
                const commentsContainer = modal.querySelector(`#comments-container-${issueKey}`);
                const form = modal.querySelector(`#add-comment-form-${issueKey}`);

                commentsContainer.innerHTML = 'Načítavam...';

                fetch(`/issues/${issueKey}/comments`)
                    .then(res => res.json())
                    .then(comments => {
                        commentsContainer.innerHTML = '';
                        if (!comments.length) {
                            commentsContainer.innerHTML = '<p class="text-muted">Žiadne komentáre.</p>';
                            return;
                        }

                        comments.forEach(comment => {
                            const isAuthor = comment.author.accountId === '{{ $currentUser["accountId"] }}';
                            const div = document.createElement('div');
                            div.classList.add('mb-3');
                            div.dataset.commentId = comment.id;
                            div.dataset.issueKey = issueKey;

                            div.innerHTML = `
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>Autor: <strong>${comment.author.displayName}</strong></div>
                                ${isAuthor ? `
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-primary save-comment-btn">Upraviť</button>
                                        <button class="btn btn-sm btn-danger delete-comment-btn">Odstrániť</button>
                                    </div>
                                ` : ''}
                            </div>
                            <textarea class="form-control" rows="2" ${isAuthor ? '' : 'disabled'}>${comment.body.content[0].content[0].text ?? ''}</textarea>
                        `;
                            commentsContainer.appendChild(div);
                        });
                    })
                    .catch(() => {
                        commentsContainer.innerHTML = '<p class="text-danger">Nepodarilo sa načítať komentáre.</p>';
                    });
            }

            // Edit comment
            const editBtn = e.target.closest('.save-comment-btn');
            if (editBtn) {
                const div = editBtn.closest('.mb-3');
                const textarea = div.querySelector('textarea');
                const commentId = div.dataset.commentId;
                const issueKey = div.dataset.issueKey;

                fetch(`/issues/${issueKey}/comments/${commentId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ body: textarea.value })
                }).then(res => res.json())
                    .then(data => { if (data.success) alert('Komentár upravený'); });
            }

            // Delete comment
            const deleteBtn = e.target.closest('.delete-comment-btn');
            if (deleteBtn) {
                const div = deleteBtn.closest('.mb-3');
                const commentId = div.dataset.commentId;
                const issueKey = div.dataset.issueKey;

                if (!confirm('Naozaj chcete odstrániť tento komentár?')) return;

                fetch(`/issues/${issueKey}/comments/${commentId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                }).then(res => res.json())
                    .then(data => { if (data.success) div.remove(); });
            }
        });

        // -------------------- Add new comment (delegated via forms) --------------------
        issuesContainer.addEventListener('submit', function (e) {
            const form = e.target.closest('form[id^="add-comment-form-"]');
            if (!form) return;
            e.preventDefault();

            const issueKey = form.dataset.issueKey;
            const textarea = form.querySelector('textarea');
            const commentsContainer = document.getElementById(`comments-container-${issueKey}`);

            fetch(`/issues/${issueKey}/comments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ body: textarea.value })
            }).then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        alert((data.errors || ['Nepodarilo sa pridať komentár.']).join('\n'));
                        return;
                    }

                    textarea.value = '';
                    const comment = data.comment;
                    const isAuthor = comment.author.accountId === '{{ $currentUser["accountId"] }}';
                    const div = document.createElement('div');
                    div.classList.add('mb-3');
                    div.dataset.commentId = comment.id;
                    div.dataset.issueKey = issueKey;

                    div.innerHTML = `
                  <div class="d-flex justify-content-between align-items-center mb-1">
                      <div>Autor: <strong>${comment.author.displayName}</strong></div>
                      ${isAuthor ? `
                          <div class="d-flex gap-1">
                              <button class="btn btn-sm btn-primary save-comment-btn">Upraviť</button>
                              <button class="btn btn-sm btn-danger delete-comment-btn">Odstrániť</button>
                          </div>
                      ` : ''}
                  </div>
                  <textarea class="form-control" rows="2" ${isAuthor ? '' : 'disabled'}>${comment.body.content[0].content[0].text ?? ''}</textarea>
              `;

                    commentsContainer.appendChild(div);
                })
                .catch(err => console.error(err));
        });
    });
</script>
