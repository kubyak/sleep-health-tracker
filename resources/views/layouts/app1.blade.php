<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sleep & Health Tracker')</title>
    <link href="\resources\css\css.css" rel="stylesheet">
    <link href="\resources\css\css.css" rel="stylesheet">
    <script src="\resources\css\css.css"></script>
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #10b981;
            --accent: #f59e0b;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.2);
        }

        .dashboard-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: none;
            overflow: hidden;
        }

        .dashboard-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .stat-card {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border-radius: 12px;
            padding: 1.25rem;
            text-align: center;
        }

        .nav-glass {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .progress-bar-custom {
            background: linear-gradient(90deg, var(--secondary), var(--accent));
            border-radius: 8px;
        }

        .sleep-phase {
            border-radius: 10px;
            padding: 0.875rem;
            margin: 0.375rem 0;
            color: white;
            font-weight: 500;
        }

        .deep-sleep {
            background: linear-gradient(135deg, #667eea, #764ba2);
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }
        .light-sleep {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            box-shadow: 0 2px 8px rgba(240, 147, 251, 0.3);
        }
        .rem-sleep {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            box-shadow: 0 2px 8px rgba(79, 172, 254, 0.3);
        }
        .awake-sleep {
            background: linear-gradient(135deg, #43e97b, #38f9d7);
            box-shadow: 0 2px 8px rgba(67, 233, 123, 0.3);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            border-radius: 10px;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--secondary), #0d9488);
            border: none;
            border-radius: 10px;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .hover-lift {
            transition: all 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
        }

        .nav-link {
            font-weight: 500;
            border-radius: 8px;
            margin: 0 0.25rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        .alert {
            border: none;
            border-radius: 12px;
            font-weight: 500;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
        }

        .badge {
            font-weight: 600;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
        }

        .progress {
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.05);
        }

        .progress-bar {
            border-radius: 8px;
        }

        /* Анимации */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        /* Кастомные цвета для иконок */
        .text-brown { color: #8B4513 !important; }
        .text-purple { color: #9370DB !important; }
        .text-orange { color: #FFA500 !important; }
        .text-coral { color: #FF7F50 !important; }
        .text-teal { color: #20B2AA !important; }

        /* Responsive улучшения */
        @media (max-width: 768px) {
            .stat-value {
                font-size: 1.5rem;
            }

            .dashboard-card {
                margin-bottom: 1rem;
            }

            .btn {
                padding: 0.625rem 1.25rem;
            }
        }
    </style>
</head>
<body>
<!-- Навигация -->
<nav class="navbar navbar-expand-lg navbar-dark nav-glass">
    <div class="container">
         <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
    <img src="/public/images/лого.png"  class="me-2" style="height: 50px; width: auto;">
    <span>SleepTracker</span>
</a>

        


    </div>
</nav>

<!-- Основной контент -->
<main class="py-4">
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show fade-in" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show fade-in" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</main>

<!-- Футер -->
<footer class="mt-5 py-4">
    <div class="container">
        <div class="text-center text-white-50">
            <p class="mb-0">&copy; 2025 SleepTracker. Все права защищены.</p>
            <small>Следите за своим здоровьем с умом</small>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@yield('scripts')
</body>
</html>
