<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ADILENTERPRICE</title>
    <link href="{{ asset('css/bootstrap_5_3.css') }}" rel="stylesheet">
    <style>
        body {
            background: #f4f7f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            background: #ffffff;
        }

        .login-header h2 {
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25 margin rgba(13, 110, 253, 0.1);
            border-color: #0d6efd;
        }

        .btn-login {
            background-color: #0d6efd;
            border: none;
            padding: 0.75rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background-color: #0b5ed7;
            transform: translateY(-1px);
        }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="login-header text-center mb-4">
            <h2>ADILENTERPRICE</h2>
            <p class="text-muted small">Enter credentials to access distribution panel</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label small fw-bold text-uppercase text-muted">User Name</label>
                <input id="name" type="text" name="name"
                    class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required
                    autofocus>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="form-label small fw-bold text-uppercase text-muted">Password</label>
                <input id="password" type="password" name="password"
                    class="form-control @error('password') is-invalid @enderror" required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary btn-login">
                    Sign In
                </button>
            </div>

            <div class="text-center">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-decoration-none small text-muted">
                        Forgot password?
                    </a>
                @endif
            </div>
        </form>
    </div>
    <script src="{{ asset('js/bootstrap_5_3.js') }}"></script>
</body>

</html>
