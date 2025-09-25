@props(['issue', 'description', 'issueTypes'])

<div class="modal fade" id="editModal-{{ $issue['key'] }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('issues.update', $issue['key']) }}">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upraviť úlohu {{ $issue['key'] }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="summary-{{ $issue['key'] }}" class="form-label">Názov</label>
                        <input type="text" name="summary" id="summary-{{ $issue['key'] }}"
                               value="{{ $issue['fields']['summary'] }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="description-{{ $issue['key'] }}" class="form-label">Popis</label>
                        <textarea name="description" id="description-{{ $issue['key'] }}" class="form-control" rows="4">{{ $description }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="issuetype-{{ $issue['key'] }}" class="form-label">Typ úlohy</label>
                        <select name="issuetype" id="issuetype-{{ $issue['key'] }}" class="form-select" required>
                            @foreach($issueTypes as $type)
                                <option value="{{ $type['id'] }}" {{ $issue['fields']['issuetype']['id'] == $type['id'] ? 'selected' : '' }}>
                                    {{ $type['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zavrieť</button>
                    <button type="submit" class="btn btn-primary">Uložiť</button>
                </div>
            </div>
        </form>
    </div>
</div>
