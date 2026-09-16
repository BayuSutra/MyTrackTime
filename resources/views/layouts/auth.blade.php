<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'My TrackTime')</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
        }
        
        .auth-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        
        .auth-card {
            background: white;
            border-radius: 20px;
            padding: 40px 36px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.04);
        }
        
        .auth-logo {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .auth-logo .icon {
            width: 52px;
            height: 52px;
            background: #4f46e5;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            margin-bottom: 12px;
        }
        
        .auth-logo h2 {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.5px;
        }
        
        .auth-logo p {
            font-size: 13px;
            color: #94a3b8;
            margin: 4px 0 0;
        }
        
        .auth-card .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
        }
        
        .auth-card .form-control {
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            padding: 10px 14px;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .auth-card .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .auth-card .form-control.is-invalid {
            border-color: #ef4444;
        }
        
        .auth-card .btn-login {
            background: #4f46e5;
            border: none;
            border-radius: 10px;
            padding: 11px;
            font-weight: 600;
            font-size: 15px;
            color: white;
            width: 100%;
            transition: all 0.2s;
        }
        
        .auth-card .btn-login:hover {
            background: #4338ca;
            transform: translateY(-1px);
        }
        
        .auth-card .auth-link {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }
        
        .auth-card .auth-link:hover {
            color: #4338ca;
            text-decoration: underline;
        }
        
        .auth-card .text-muted-custom {
            color: #94a3b8;
            font-size: 14px;
        }
        
        .auth-card .form-check-label {
            font-size: 13px;
            color: #64748b;
        }
        
        .auth-card .alert {
            border-radius: 10px;
            font-size: 13px;
            border: none;
        }
        
        .auth-card .alert-danger {
            background: #fef2f2;
            color: #dc2626;
        }
        
        .auth-card .alert-success {
            background: #f0fdf4;
            color: #16a34a;
        }
        
        .demo-badge {
            display: inline-block;
            font-size: 11px;
            padding: 2px 10px;
            border-radius: 20px;
            background: #f1f5f9;
            color: #64748b;
        }
        
        @media (max-width: 480px) {
            .auth-card { padding: 28px 20px; }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <div class="icon">
                    <i class="bi bi-satellite"></i>
                </div>
                <h2>My TrackTime</h2>
                <p>Real-time Monitoring System</p>
            </div>
            
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif
            
            @yield('content')
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>