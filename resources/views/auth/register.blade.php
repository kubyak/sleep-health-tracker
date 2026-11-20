@extends('layouts.app1')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="dashboard-card p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-moon fa-3x text-primary mb-3"></i>
                        <h2 class="gradient-text">SleepTracker</h2>
                        <p class="text-muted">Создайте новый аккаунт</p>
                    </div>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="mb-3">
                            <input id="name" type="text" class="form-control form-control-lg @error('name') is-invalid @enderror"
                                   name="name" value="{{ old('name') }}" required autocomplete="name" autofocus
                                   placeholder="Ваше имя">
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror"
                                   name="email" value="{{ old('email') }}" required autocomplete="email"
                                   placeholder="Email адрес">
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror"
                                   name="password" required autocomplete="new-password" placeholder="Пароль">
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <input id="password-confirm" type="password" class="form-control form-control-lg"
                                   name="password_confirmation" required autocomplete="new-password" placeholder="Подтвердите пароль">
                        </div>

                        <!-- Галочка согласия на обработку персональных данных -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input @error('privacy_policy') is-invalid @enderror"
                                       type="checkbox" name="privacy_policy" id="privacy_policy"
                                       {{ old('privacy_policy') ? 'checked' : '' }} required>
                                <label class="form-check-label text-start" for="privacy_policy">
                                    Я согласен на обработку моих персональных данных
                                    в соответствии с <a href="#" class="text-primary">Политикой конфиденциальности</a>
                                </label>
                                @error('privacy_policy')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg py-2">
                                <i class="fas fa-user-plus me-2"></i>Зарегистрироваться
                            </button>
                        </div>
                    </form>

                    <div class="mt-4 pt-3 border-top">
                        <p class="text-muted mb-0">Уже есть аккаунт?</p>
                        <a href="{{ route('login') }}" class="btn btn-outline-primary mt-2">
                            <i class="fas fa-sign-in-alt me-2"></i>Войти
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
