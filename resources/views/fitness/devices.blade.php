@extends('layouts.app')

@section('title', 'Фитнес-устройства - SleepTracker')

@section('content')
    <div class="fade-in">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="text-white">
                        <i class="fas fa-watch me-2"></i>Фитнес-устройства
                    </h2>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-2"></i>Назад
                    </a>
                </div>
            </div>
        </div>

        <!-- Подключенные устройства -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card p-4">
                    <h4 class="mb-4">
                        <i class="fas fa-link text-primary me-2"></i>Подключенные устройства
                    </h4>

                    @foreach($devices as $device)
                        <div class="device-card card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            @if($device->api_provider === 'google_fit')
                                                <i class="fab fa-google fa-3x text-danger me-3"></i>
                                            @else
                                                <i class="fas fa-watch fa-3x text-primary me-3"></i>
                                            @endif
                                            <div>
                                                <h5 class="mb-1">{{ $device->device_name }}</h5>
                                                <p class="text-muted mb-1">
                                            <span class="badge bg-{{ $device->isActive() ? 'success' : 'warning' }}">
                                                {{ $device->isActive() ? 'Подключено' : 'Не активно' }}
                                            </span>
                                                </p>
                                                <small class="text-muted">
                                                    Последняя синхронизация:
                                                    {{ $device->last_sync_at ? $device->last_sync_at->diffForHumans() : 'никогда' }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <div class="btn-group">
                                            @if($device->isActive())
                                                <button class="btn btn-success sync-btn" data-device-id="{{ $device->id }}">
                                                    <i class="fas fa-sync me-2"></i>Синхронизировать
                                                </button>
                                            @else
                                                <button class="btn btn-warning refresh-token-btn" data-device-id="{{ $device->id }}">
                                                    <i class="fas fa-redo me-2"></i>Обновить токен
                                                </button>
                                            @endif

                                            <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split"
                                                    data-bs-toggle="dropdown">
                                                <span class="visually-hidden">Меню</span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item settings-btn" href="#" data-device-id="{{ $device->id }}">
                                                        <i class="fas fa-cog me-2"></i>Настройки
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('fitness.google-fit.disconnect', $device->id) }}"
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger"
                                                                onclick="return confirm('Вы уверены, что хотите отключить устройство?')">
                                                            <i class="fas fa-unlink me-2"></i>Отключить
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if($devices->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-watch fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">Нет подключенных устройств</h4>
                            <p class="text-muted">Подключите ваше фитнес-устройство для автоматической синхронизации данных</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Доступные для подключения устройства -->
        <div class="row">
            <div class="col-12">
                <div class="dashboard-card p-4">
                    <h4 class="mb-4">
                        <i class="fas fa-plus-circle text-success me-2"></i>Доступные устройства
                    </h4>

                    <div class="row">
                        <!-- Google Fit -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-0 h-100 hover-lift">
                                <div class="card-body text-center p-4">
                                    <i class="fab fa-google fa-4x text-danger mb-3"></i>
                                    <h5 class="card-title">Google Fit</h5>
                                    <p class="card-text text-muted mb-3">
                                        Синхронизация данных с Android устройствами, умными часами Wear OS и фитнес-трекерами
                                    </p>
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-check text-success me-1"></i> Сон
                                        </small>
                                        <small class="text-muted ms-3">
                                            <i class="fas fa-check text-success me-1"></i> Активность
                                        </small>
                                        <small class="text-muted ms-3">
                                            <i class="fas fa-check text-success me-1"></i> Пульс
                                        </small>
                                    </div>
                                    <a href="{{ route('fitness.google-fit.connect') }}" class="btn btn-danger w-100">
                                        <i class="fas fa-link me-2"></i>Подключить Google Fit
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Apple Health (в разработке) -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-0 h-100">
                                <div class="card-body text-center p-4">
                                    <i class="fab fa-apple fa-4x text-dark mb-3"></i>
                                    <h5 class="card-title">Apple Health</h5>
                                    <p class="card-text text-muted mb-3">
                                        Интеграция с Apple Watch и iOS устройствами через HealthKit
                                    </p>
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-check text-success me-1"></i> Сон
                                        </small>
                                        <small class="text-muted ms-3">
                                            <i class="fas fa-check text-success me-1"></i> Активность
                                        </small>
                                        <small class="text-muted ms-3">
                                            <i class="fas fa-check text-success me-1"></i> Пульс
                                        </small>
                                    </div>
                                    <button class="btn btn-dark w-100" disabled>
                                        <i class="fas fa-clock me-2"></i>Скоро доступно
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно настроек -->
    <div class="modal fade" id="settingsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-cog me-2"></i>Настройки синхронизации
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="settingsForm">
                        @csrf
                        <input type="hidden" name="device_id" id="deviceId">

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="sync_sleep" id="syncSleep" checked>
                                <label class="form-check-label" for="syncSleep">
                                    Синхронизировать данные о сне
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="sync_activity" id="syncActivity" checked>
                                <label class="form-check-label" for="syncActivity">
                                    Синхронизировать данные об активности
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="sync_heart_rate" id="syncHeartRate" checked>
                                <label class="form-check-label" for="syncHeartRate">
                                    Синхронизировать данные о пульсе
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="auto_sync" id="autoSync" checked>
                                <label class="form-check-label" for="autoSync">
                                    Автоматическая синхронизация
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="syncInterval" class="form-label">Интервал синхронизации (минут)</label>
                            <input type="number" class="form-control" id="syncInterval" name="sync_interval" value="60" min="15" max="1440">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" id="saveSettings">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Синхронизация данных
            document.querySelectorAll('.sync-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const deviceId = this.dataset.deviceId;
                    const originalText = this.innerHTML;

                    this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Синхронизация...';
                    this.disabled = true;

                    fetch(`/fitness/google-fit/${deviceId}/sync`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showAlert('Данные успешно синхронизированы!', 'success');
                                // Обновляем страницу через 2 секунды
                                setTimeout(() => location.reload(), 2000);
                            } else {
                                showAlert(data.message, 'error');
                            }
                        })
                        .catch(error => {
                            showAlert('Ошибка синхронизации', 'error');
                            console.error('Sync error:', error);
                        })
                        .finally(() => {
                            this.innerHTML = originalText;
                            this.disabled = false;
                        });
                });
            });

            // Обновление токена
            document.querySelectorAll('.refresh-token-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const deviceId = this.dataset.deviceId;
                    const originalText = this.innerHTML;

                    this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Обновление...';
                    this.disabled = true;

                    fetch(`/fitness/google-fit/${deviceId}/refresh-token`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showAlert('Токен успешно обновлен!', 'success');
                                setTimeout(() => location.reload(), 2000);
                            } else {
                                showAlert(data.message, 'error');
                            }
                        })
                        .catch(error => {
                            showAlert('Ошибка обновления токена', 'error');
                            console.error('Token refresh error:', error);
                        })
                        .finally(() => {
                            this.innerHTML = originalText;
                            this.disabled = false;
                        });
                });
            });

            // Настройки
            const settingsModal = new bootstrap.Modal(document.getElementById('settingsModal'));

            document.querySelectorAll('.settings-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const deviceId = this.dataset.deviceId;
                    document.getElementById('deviceId').value = deviceId;
                    settingsModal.show();
                });
            });

            // Сохранение настроек
            document.getElementById('saveSettings').addEventListener('click', function() {
                const deviceId = document.getElementById('deviceId').value;
                const formData = new FormData(document.getElementById('settingsForm'));

                fetch(`/fitness/devices/${deviceId}/settings`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('Настройки сохранены!', 'success');
                            settingsModal.hide();
                        } else {
                            showAlert(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        showAlert('Ошибка сохранения настроек', 'error');
                        console.error('Settings save error:', error);
                    });
            });

            // Функция показа уведомлений
            function showAlert(message, type) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const alert = document.createElement('div');
                alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
                alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

                document.body.appendChild(alert);

                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 5000);
            }
        });
    </script>
@endsection
