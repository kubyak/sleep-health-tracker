@extends('layouts.app1')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="dashboard-card p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-moon fa-3x text-primary mb-3"></i>
                        <h2 class="gradient-text">SleepTracker</h2>
                        <p class="text-muted">Войдите в свой аккаунт</p>
                    </div>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror"
                                   name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                                   placeholder="Email адрес">
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror"
                                   name="password" required autocomplete="current-password" placeholder="Пароль">
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">Запомнить меня</label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg py-2">
                                <i class="fas fa-sign-in-alt me-2"></i>Войти
                            </button>

                            @if (Route::has('password.request'))
                                <a class="btn btn-link" href="{{ route('password.request') }}">
                                    Забыли пароль?
                                </a>
                            @endif
                        </div>
                    </form>

                    <div class="mt-4 pt-3 border-top">
                        <p class="text-muted mb-0">Еще нет аккаунта?</p>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary mt-2">
                            <i class="fas fa-user-plus me-2"></i>Зарегистрироваться
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
