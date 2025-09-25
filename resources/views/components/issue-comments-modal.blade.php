@props(['issue', 'description'])

<div class="modal fade" id="commentsModal-{{ $issue['key'] }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Komentáre pre {{ $issue['key'] }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @foreach($issue['comments'] ?? [] as $comment)
                    @php
                        $text = data_get($comment, 'body.content.0.content.0.text', '');
                    @endphp
                    <div class="mb-3">
                        <form method="POST" action="{{ route('comments.update', [$issue['key'], $comment['id']]) }}">
                            @csrf
                            @method('PUT')
                            <textarea name="body" class="form-control mb-1" rows="2">{{ $text }}</textarea>
                            <button type="submit" class="btn btn-sm btn-primary">Upraviť</button>
                        </form>
                    </div>
                @endforeach

                <hr>
                <form method="POST" action="{{ route('comments.store', $issue['key']) }}">
                    @csrf
                    <textarea name="body" class="form-control mb-1" rows="2" placeholder="Pridať komentár..."></textarea>
                    <button type="submit" class="btn btn-sm btn-success">Pridať</button>
                </form>
            </div>
        </div>
    </div>
</div>
