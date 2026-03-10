<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CAPS Panel</title>
    <!-- Favicon -->
    <link rel="icon" href="{{asset('assets/img/seo-img.png')}}" type="image/x-icon"/>
    <link href="{{asset('assets/css/style.css')}}" rel="stylesheet">
    <!-- Fonts & Bootstrap -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />

    <!-- Styles -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        #title-text {
            line-height: 42px;
            height: 42px;
            overflow: hidden;
        }

        #subtitle-text {
            line-height: 24px;
            height: 24px;
            overflow: hidden;
        }

        .login-card {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        .login-card:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }
        .form-control {
            border-radius: 8px;
            border-color: #d1d5db;
        }
        .form-control:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 0 0.2rem rgba(96, 165, 250, 0.25);
        }
        .btn-primary {
            background-color: #3b82f6;
            border: none;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #2563eb;
        }
        .field-icon {
            float: right;
            margin-top: -30px;
            margin-right: 10px;
            cursor: pointer;
            color: #6b7280;
        }
    </style>
    <style>
        .btn-primary {
            position: relative;
            display: inline-block;
            background: linear-gradient(45deg, #3b82f6, #06b6d4);
            color: #fff;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            padding: 12px 25px;
            font-size: 1rem;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .btn-primary::after {
            content: '';
            position: absolute;
            top: 0;
            left: -75%;
            width: 50%;
            height: 100%;
            background: rgba(255, 255, 255, 0.3);
            transform: skewX(-25deg);
            transition: all 0.3s ease;
            animation: shine 3s linear infinite;
        }

        @keyframes shine {
            0% { left: -75%; }
            50% { left: 100%; }
            100% { left: -75%; }
        }

        .btn-primary:active {
            transform: translateY(-100px) rotate(-15deg) scale(0.8);
            transition: transform 0.5s ease-in;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.7);
        }
    </style>

</head>
<body>
<!-- Loader -->
<div id="global-loader">
    <img src="{{asset('assets/img/loader.svg')}}" class="loader-img" alt="Loader">
</div>
<!-- /Loader -->
<div class="login-card">
    <div class="text-center mb-4">
        <h2 class="fw-bold text-primary" id="title-text">CAPS Panel</h2>
        <p class="text-muted" id="subtitle-text">Login To Your Admin Panel !</p>
    </div>

    <form action="{{ route('admin.login.submit') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Enter Your Email" required autofocus>
            @error('email')
            <div class="text-danger mt-1 small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3 position-relative">
            <label class="form-label">Password</label>
            <input id="password-field" type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter Your Password" required>
            <span toggle="#password-field" class="fas fa-eye field-icon toggle-password"></span>
            @error('password')
            <div class="text-danger mt-1 small">{{ $message }}</div>
            @enderror
        </div>
        <div class="d-grid mt-5">
            <button type="submit" class="btn btn-primary">Login</button>
        </div>
    </form>
</div>
<!--- JQuery min js --->
<script src="{{asset('assets/plugins/jquery/jquery.min.js')}}"></script>

<!--- Custom js --->
<script src="{{asset('assets/js/custom.js')}}"></script>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


<script>

    $(".toggle-password").click(function () {
        const input = $("#password-field");
        const type = input.attr("type") === "password" ? "text" : "password";
        input.attr("type", type);
        $(this).toggleClass("fa-eye fa-eye-slash");
    });

    @if(session('success'))
    toastr.success("{{ session('success') }}");
    @endif

    @if(session('error'))
    toastr.error("{{ session('error') }}");
    @endif

    @if(session('warning'))
    toastr.warning("{{ session('warning') }}");
    @endif

    @if(session('info'))
    toastr.info("{{ session('info') }}");
    @endif
</script>
<script>
    function typeWriter(elementId, text, speed = 100, callback = null) {
        const element = document.getElementById(elementId);
        element.textContent = "";
        let i = 0;
        function type() {
            if (i < text.length) {
                element.textContent += text.charAt(i);
                i++;
                setTimeout(type, speed);
            } else if (callback) {
                setTimeout(callback, 1000);
            }
        }
        type();
    }

    function loopTyping() {
        const title = "CAPS Panel";
        const subtitle = "Login To Your Admin Panel !";

        typeWriter("title-text", title, 90, () => {
            typeWriter("subtitle-text", subtitle, 60, () => {
                setTimeout(() => {
                    document.getElementById("title-text").textContent = "";
                    document.getElementById("subtitle-text").textContent = "";
                    setTimeout(loopTyping, 0);
                }, 1000);
            });
        });
    }
    window.addEventListener('DOMContentLoaded', loopTyping);
</script>

</body>
</html>
