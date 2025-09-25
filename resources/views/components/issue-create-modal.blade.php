@props(['issueTypes'])

<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('issues.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pridať novú úlohu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="summary-new" class="form-label">Názov</label>
                        <input type="text" name="summary" id="summary-new" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="description-new" class="form-label">Popis</label>
                        <textarea name="description" id="description-new" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="issuetype-new" class="form-label">Typ úlohy</label>
                        <select name="issuetype" id="issuetype-new" class="form-select" required>
                            @foreach($issueTypes as $type)
                                <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zavrieť</button>
                    <button type="submit" class="btn btn-success">Pridať</button>
                </div>
            </div>
        </form>
    </div>
</div>
