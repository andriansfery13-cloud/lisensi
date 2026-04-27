<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — License Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 30%, #ecfeff 60%, #f0fdfa 100%);
            position: relative;
            overflow: hidden;
        }
        /* Decorative circles */
        body::before {
            content: '';
            position: fixed;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(14,165,233,0.08) 0%, transparent 70%);
            top: -150px;
            right: -100px;
            border-radius: 50%;
            animation: float1 15s ease-in-out infinite;
        }
        body::after {
            content: '';
            position: fixed;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(20,184,166,0.08) 0%, transparent 70%);
            bottom: -100px;
            left: -100px;
            border-radius: 50%;
            animation: float2 12s ease-in-out infinite;
        }
        @keyframes float1 { 0%,100% { transform: translate(0,0); } 50% { transform: translate(-30px, 30px); } }
        @keyframes float2 { 0%,100% { transform: translate(0,0); } 50% { transform: translate(20px, -20px); } }

        .login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
            background: rgba(255,255,255,0.85);
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            backdrop-filter: blur(20px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.06);
        }
        .login-brand {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-brand .icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #0ea5e9, #06b6d4);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
            box-shadow: 0 8px 30px rgba(14, 165, 233, 0.25);
        }
        .login-brand h1 {
            font-size: 1.35rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }
        .login-brand p {
            font-size: 0.8rem;
            color: #94a3b8;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.5rem;
        }
        .input-wrapper {
            position: relative;
        }
        .input-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.85rem;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            color: #1e293b;
            font-size: 0.85rem;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            border-color: #0ea5e9;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.12);
        }
        .form-control::placeholder { color: #94a3b8; }
        .remember-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .remember-row input[type="checkbox"] {
            accent-color: #0ea5e9;
            width: 16px;
            height: 16px;
            cursor: pointer;
        }
        .remember-row label {
            font-size: 0.8rem;
            color: #475569;
            cursor: pointer;
        }
        .btn-login {
            width: 100%;
            padding: 0.85rem;
            background: linear-gradient(135deg, #0ea5e9, #06b6d4);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 0.9rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.25);
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.35);
        }
        .alert-error {
            padding: 0.75rem 1rem;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            color: #991b1b;
            font-size: 0.8rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .footer-text {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.7rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-brand">
            <div class="icon"><i class="fas fa-shield-halved"></i></div>
            <h1>License Manager</h1>
            <p>Secure License Management System</p>
        </div>

        @if($errors->any())
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ url('/login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" class="form-control" placeholder="admin@admin.com" value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <div class="remember-row">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Remember me</label>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-right-to-bracket"></i> Sign In
            </button>
        </form>

        <div class="footer-text">
            <i class="fas fa-lock"></i> Protected by License Management System v2
        </div>
    </div>
</body>
</html>
