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

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="issues-container">
        @include('partials.issues', compact('issues', 'issueTypes', 'issueStatuses', 'currentUser'))
    </div>

    @if(!$isLast && $nextPageToken)
        <div class="d-flex justify-content-center mt-4">
            <button id="load-more" class="btn btn-outline-primary"
                    data-token="{{ $nextPageToken }}">
                Načítať ďalšie
            </button>
        </div>
    @endif
</div>

<x-issue-create-modal :issueTypes="$issueTypes" />

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@include('partials.comments-script')
</body>
</html>
