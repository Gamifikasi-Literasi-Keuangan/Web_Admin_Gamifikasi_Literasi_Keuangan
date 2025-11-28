<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f6fa;
            font-family: 'Inter', sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 380px;
            border-radius: 18px;
        }

        .form-control {
            border-radius: 12px !important;
        }

        .btn-primary {
            border-radius: 12px !important;
        }
    </style>
</head>
<body>

<div class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card shadow-lg p-4 login-card">

        <h3 class="text-center mb-4 fw-bold">
            Login
        </h3>

        @if(session('error'))
            <div class="alert alert-danger py-2">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('login.attempt') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold">Email / Username</label>
                <input 
                    type="text" 
                    name="login" 
                    class="form-control form-control-lg" 
                    placeholder="Masukkan username..."
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input 
                    type="password" 
                    name="password" 
                    class="form-control form-control-lg" 
                    placeholder="Masukkan password..."
                    required
                >
            </div>

            <button 
                class="btn btn-primary w-100 py-2 fw-semibold" 
                style="font-size: 1.05rem;">
                Login
            </button>
        </form>
    </div>
</div>

</body>
</html>
php