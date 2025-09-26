@props(['issue', 'description', 'isReporter', 'currentUser'])

<div class="modal fade" id="commentsModal-{{ $issue['key'] }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Komentáre pre {{ $issue['key'] }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="comments-container-{{ $issue['key'] }}"></div>
                <hr>
                <form id="add-comment-form-{{ $issue['key'] }}" data-issue-key="{{ $issue['key'] }}">
                    @csrf
                    <textarea name="body" class="form-control mb-1" rows="2" placeholder="Pridať komentár..."></textarea>
                    <button type="submit" class="btn btn-sm btn-success">Pridať</button>
                </form>
            </div>

        </div>
    </div>
</div>
