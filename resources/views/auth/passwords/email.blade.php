@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="dashboard-card p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-key fa-3x text-primary mb-3"></i>
                        <h2 class="gradient-text">Сброс пароля</h2>
                        <p class="text-muted">Введите ваш email для получения ссылки сброса</p>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="mb-3">
                            <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror"
                                   name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                                   placeholder="Email адрес">
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg py-2">
                                <i class="fas fa-paper-plane me-2"></i>Отправить ссылку сброса
                            </button>
                        </div>
                    </form>

                    <div class="mt-4 pt-3 border-top">
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Назад к входу
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
