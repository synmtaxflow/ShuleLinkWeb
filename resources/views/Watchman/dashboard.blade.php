<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Watchman Dashboard</title>
    <link rel="stylesheet" href="{{ asset('vendors/bootstrap/dist/css/bootstrap.min.css') }}">
    <style>
        body, .card, .btn { font-family: "Century Gothic", Arial, sans-serif; }
        .btn-primary-custom { background-color: #940000; border-color: #940000; color: #fff; }
        .btn-primary-custom:hover { background-color: #b30000; border-color: #b30000; color: #fff; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-2">Watchman Dashboard</h4>
                <p class="text-muted mb-3">Welcome, {{ session('watchman_name') ?? 'Watchman' }}.</p>
                <div class="d-flex gap-2">
                    <a href="{{ route('watchman.visitors') }}" class="btn btn-primary-custom">Register Visitors</a>
                    <a href="{{ route('logout') }}" class="btn btn-secondary">Logout</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
