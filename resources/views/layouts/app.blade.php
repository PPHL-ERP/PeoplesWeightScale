<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My App</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">My App</a>
            @if(session('user'))
                <div class="ms-auto">
                    Welcome, {{ session('user')['name'] ?? 'User' }}
                    <a href="{{ route('logout') }}" class="btn btn-sm btn-outline-danger">Logout</a>
                </div>
            @endif
        </div>
    </nav>

    <div class="container mt-4">
        @yield('content')
    </div>
</body>
</html>
