
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Guard Analytics</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f2f6f3;
        }

        .login-card {
            max-width: 420px;
            border-radius: 16px;
        }

        .input-icon {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: #4f6f52;
        }

        .form-control {
            padding-left: 40px;
            height: 48px;
        }

        .btn-login {
            background: #4f6f52;
            border: none;
        }

        .btn-login:hover {
            background: #3f5640;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100 p-3">

    <div class="card shadow-sm border login-card p-4">

        <div class="text-center mb-4">

            <img src="{{ asset('images/logo.png') }}" class="mb-3" style="max-width:200px">

            <h3 class="fw-bold mb-1">
                Welcome Back
            </h3>

            <p class="text-muted small">
                Sign in to access Guard Analytics
            </p>

        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Phone -->
            <div class="mb-3">

                <label class="form-label fw-semibold">
                    Phone Number
                </label>

                <div class="position-relative">

                    <i class="bi bi-telephone-fill input-icon"></i>

                    <input
                        type="text"
                        name="phone"
                        maxlength="10"
                        value="{{ old('phone') }}"
                        placeholder="Enter 10-digit number"
                        class="form-control"
                        required>

                </div>

                @error('phone')
                <div class="text-danger small mt-1">
                    ⚠ {{ $message }}
                </div>
                @enderror

            </div>

            <!-- Password -->
            <div class="mb-3">

                <label class="form-label fw-semibold">
                    Password
                </label>

                <div class="position-relative">

                    <i class="bi bi-lock-fill input-icon"></i>

                    <input
                        type="password"
                        name="password"
                        placeholder="5-15 characters"
                        class="form-control"
                        required>

                </div>

                @error('password')
                <div class="text-danger small mt-1">
                    ⚠ {{ $message }}
                </div>
                @enderror

            </div>

            <button class="btn btn-login text-white w-100 py-2 fw-semibold">
                Sign In
            </button>

        </form>

        <p class="text-center text-muted small mt-4">
            © 2026 Guard Analytics. All rights reserved.
        </p>

    </div>

</body>

</html>

