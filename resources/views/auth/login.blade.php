<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Guard Analytics</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    @vite(['resources/css/app.css','resources/js/app.js'])

</head>

<body class="font-[Inter] bg-[#f2f6f3] min-h-screen flex items-center justify-center p-5 relative overflow-hidden">

    <!-- Background circles -->
    <div class="absolute inset-0 overflow-hidden -z-10">

        <div class="absolute w-[300px] h-[300px] bg-[#4f6f52]/10 rounded-full -top-[100px] -left-[100px] animate-[float_20s_ease-in-out_infinite]"></div>

        <div class="absolute w-[200px] h-[200px] bg-[#4f6f52]/10 rounded-full -bottom-[50px] -right-[50px] animate-[float_20s_ease-in-out_infinite] [animation-delay:5s]"></div>

        <div class="absolute w-[150px] h-[150px] bg-[#4f6f52]/10 rounded-full top-1/2 right-[10%] animate-[float_20s_ease-in-out_infinite] [animation-delay:10s]"></div>

    </div>

    <!-- Login card -->
    <div class="bg-[#fcfefc] w-full max-w-[420px] p-9 rounded-2xl border border-[#4f6f52]/20 shadow-[0_4px_12px_rgba(47,62,47,0.06)] animate-[slideUp_0.6s_ease-out]">

        <div class="text-center mb-6">

            <img src="{{ asset('images/logo.png') }}" class="mx-auto mb-3 max-w-[200px]" />

            <h1 class="text-2xl font-bold text-[#1f2f1f] mb-1">
                Welcome Back
            </h1>

            <p class="text-[13px] text-gray-500">
                Sign in to access Guard Analytics
            </p>

        </div>


        <form method="POST" action="{{ route('login') }}">
            @csrf


            <!-- Phone -->
            <div class="mb-[18px]">

                <label class="block text-sm font-semibold text-[#1f2f1f] mb-2">
                    Phone Number
                </label>

                <div class="relative">

                    <i class="bi bi-telephone-fill absolute left-4 top-1/2 -translate-y-1/2 text-[#4f6f52] text-lg"></i>

                    <input
                        type="text"
                        name="phone"
                        value="{{ old('phone') }}"
                        placeholder="Enter 10-digit number"
                        class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-[10px] text-sm bg-white transition focus:outline-none focus:border-[#4f6f52] focus:ring-4 focus:ring-[#4f6f52]/20"
                        required>

                </div>

                @error('phone')
                <div class="text-red-500 text-sm mt-1 flex items-center gap-1">
                    ⚠ {{ $message }}
                </div>
                @enderror

            </div>


            <!-- Password -->
            <div class="mb-[18px]">

                <label class="block text-sm font-semibold text-[#1f2f1f] mb-2">
                    Password
                </label>

                <div class="relative">

                    <i class="bi bi-lock-fill absolute left-4 top-1/2 -translate-y-1/2 text-[#4f6f52] text-lg"></i>

                    <input
                        type="password"
                        name="password"
                        placeholder="5-15 characters"
                        class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-[10px] text-sm bg-white transition focus:outline-none focus:border-[#4f6f52] focus:ring-4 focus:ring-[#4f6f52]/20"
                        required>

                </div>

                @error('password')
                <div class="text-red-500 text-sm mt-1 flex items-center gap-1">
                    ⚠ {{ $message }}
                </div>
                @enderror

            </div>


            <button
                class="w-full py-[13px] bg-[#4f6f52] text-white rounded-[10px] text-[15px] font-semibold shadow-lg transition hover:bg-[#3f5640] hover:-translate-y-[2px] hover:shadow-xl">

                Sign In

            </button>

        </form>


        <p class="text-center text-xs text-gray-400 mt-6">
            © 2026 Guard Analytics. All rights reserved.
        </p>

    </div>

</body>

</html>
