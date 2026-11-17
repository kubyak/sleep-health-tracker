@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="dashboard-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">
                            <i class="fas fa-bed text-primary me-2"></i>Добавить запись о сне
                        </h2>
                        <a href="{{ route('sleep.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Назад
                        </a>
                    </div>

                    <form action="{{ route('sleep.store') }}" method="POST" id="sleepForm">
                        @csrf

                        <div class="row g-3">
                            <!-- Дата -->
                            <div class="col-md-6">
                                <label for="date" class="form-label">Дата *</label>
                                <input type="date" class="form-control @error('date') is-invalid @enderror"
                                       id="date" name="date" value="{{ old('date', $today) }}" required>
                                @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Время отхода ко сну -->
                            <div class="col-md-6">
                                <label for="bedtime" class="form-label">Время отхода ко сну *</label>
                                <input type="time" class="form-control @error('bedtime') is-invalid @enderror"
                                       id="bedtime" name="bedtime" value="{{ old('bedtime', $defaultBedtime) }}" required>
                                @error('bedtime')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Время пробуждения -->
                            <div class="col-md-6">
                                <label for="wakeup_time" class="form-label">Время пробуждения *</label>
                                <input type="time" class="form-control @error('wakeup_time') is-invalid @enderror"
                                       id="wakeup_time" name="wakeup_time" value="{{ old('wakeup_time', $defaultWakeup) }}" required>
                                @error('wakeup_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Автоматический расчет -->
                            <div class="col-md-6">
                                <label class="form-label">Расчет сна</label>
                                <div class="card bg-light border-0">
                                    <div class="card-body py-2">
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <small class="text-muted">Длительность</small>
                                                <div class="fw-bold text-primary" id="durationDisplay">0ч 0м</div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Качество</small>
                                                <div class="fw-bold text-success" id="qualityEstimate">~0%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Фазы сна -->
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-chart-pie me-2"></i>Фазы сна
                                    <small class="text-muted">(в минутах)</small>
                                </h5>
                            </div>

                            <div class="col-md-3">
                                <label for="deep_sleep" class="form-label">Глубокий сон</label>
                                <input type="number" class="form-control phase-input" id="deep_sleep" name="deep_sleep"
                                       value="{{ old('deep_sleep') }}" min="0" max="300" placeholder="0">
                                <small class="form-text text-muted">15-25% от общего времени</small>
                            </div>

                            <div class="col-md-3">
                                <label for="light_sleep" class="form-label">Легкий сон</label>
                                <input type="number" class="form-control phase-input" id="light_sleep" name="light_sleep"
                                       value="{{ old('light_sleep') }}" min="0" max="500" placeholder="0">
                                <small class="form-text text-muted">50-60% от общего времени</small>
                            </div>

                            <div class="col-md-3">
                                <label for="rem_sleep" class="form-label">REM сон</label>
                                <input type="number" class="form-control phase-input" id="rem_sleep" name="rem_sleep"
                                       value="{{ old('rem_sleep') }}" min="0" max="200" placeholder="0">
                                <small class="form-text text-muted">20-25% от общего времени</small>
                            </div>

                            <div class="col-md-3">
                                <label for="awake_time" class="form-label">Бодрствование</label>
                                <input type="number" class="form-control phase-input" id="awake_time" name="awake_time"
                                       value="{{ old('awake_time') }}" min="0" max="120" placeholder="0">
                                <small class="form-text text-muted">Меньше 5% - отлично</small>
                            </div>

                            <!-- Автоматическое распределение -->
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Оставьте поля пустыми для автоматического расчета фаз сна</small>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="autoCalculateBtn">
                                        <i class="fas fa-calculator me-1"></i>Авторасчет
                                    </button>
                                </div>
                            </div>

                            <!-- Визуализация фаз -->
                            <div class="col-12 mt-3">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">Распределение фаз сна:</h6>
                                        <div class="progress mb-2" style="height: 20px;">
                                            <div class="progress-bar deep-sleep" id="deepBar" style="width: 0%">
                                                <small>Глубокий: 0%</small>
                                            </div>
                                            <div class="progress-bar light-sleep" id="lightBar" style="width: 0%">
                                                <small>Легкий: 0%</small>
                                            </div>
                                            <div class="progress-bar rem-sleep" id="remBar" style="width: 0%">
                                                <small>REM: 0%</small>
                                            </div>
                                            <div class="progress-bar awake-sleep" id="awakeBar" style="width: 0%">
                                                <small>Бодр: 0%</small>
                                            </div>
                                        </div>
                                        <div class="row text-center small">
                                            <div class="col-3">
                                                <i class="fas fa-square text-primary"></i> Глубокий
                                            </div>
                                            <div class="col-3">
                                                <i class="fas fa-square text-danger"></i> Легкий
                                            </div>
                                            <div class="col-3">
                                                <i class="fas fa-square text-info"></i> REM
                                            </div>
                                            <div class="col-3">
                                                <i class="fas fa-square text-success"></i> Бодр
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Заметки -->
                            <div class="col-12 mt-3">
                                <label for="notes" class="form-label">
                                    <i class="fas fa-sticky-note me-1"></i>Заметки и наблюдения
                                </label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                          id="notes" name="notes" rows="4"
                                          placeholder="Опишите, как вы себя чувствовали перед сном и после пробуждения. Что могло повлиять на качество сна? (кофеин, стресс, физическая активность и т.д.)">{{ old('notes') }}</textarea>
                                @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Факторы влияния -->
                            <div class="col-12">
                                <h6 class="mb-3">Факторы, влияющие на сон:</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="factors[]" value="coffee" id="coffee">
                                            <label class="form-check-label" for="coffee">
                                                <i class="fas fa-coffee text-brown me-1"></i>Кофеин вечером
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="factors[]" value="alcohol" id="alcohol">
                                            <label class="form-check-label" for="alcohol">
                                                <i class="fas fa-wine-glass-alt text-purple me-1"></i>Алкоголь
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="factors[]" value="heavy_food" id="heavy_food">
                                            <label class="form-check-label" for="heavy_food">
                                                <i class="fas fa-utensils text-orange me-1"></i>Плотный ужин
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="factors[]" value="stress" id="stress">
                                            <label class="form-check-label" for="stress">
                                                <i class="fas fa-brain text-red me-1"></i>Стресс
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="factors[]" value="exercise" id="exercise">
                                            <label class="form-check-label" for="exercise">
                                                <i class="fas fa-running text-green me-1"></i>Физическая активность
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="factors[]" value="screen_time" id="screen_time">
                                            <label class="form-check-label" for="screen_time">
                                                <i class="fas fa-mobile-alt text-blue me-1"></i>Экран перед сном
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Самочувствие -->
                            <div class="col-12">
                                <label for="mood" class="form-label">Самочувствие после пробуждения</label>
                                <select class="form-select" id="mood" name="mood">
                                    <option value="">Выберите самочувствие...</option>
                                    <option value="excellent" {{ old('mood') == 'excellent' ? 'selected' : '' }}>😊 Отличное</option>
                                    <option value="good" {{ old('mood') == 'good' ? 'selected' : '' }}>🙂 Хорошее</option>
                                    <option value="normal" {{ old('mood') == 'normal' ? 'selected' : '' }}>😐 Нормальное</option>
                                    <option value="tired" {{ old('mood') == 'tired' ? 'selected' : '' }}>😪 Уставший</option>
                                    <option value="exhausted" {{ old('mood') == 'exhausted' ? 'selected' : '' }}>😫 Изможденный</option>
                                </select>
                            </div>

                            <!-- Информационные блоки -->
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Совет:</strong> Для точных данных используйте трекер сна или умные часы.
                                    Если данные о фазах сна недоступны, оставьте поля пустыми для автоматического расчета.
                                </div>
                            </div>

                            <!-- Кнопка отправки -->
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100 py-3 btn-lg">
                                    <i class="fas fa-save me-2"></i>Сохранить запись о сне
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @section('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const bedtimeInput = document.getElementById('bedtime');
                const wakeupInput = document.getElementById('wakeup_time');
                const durationDisplay = document.getElementById('durationDisplay');
                const qualityEstimate = document.getElementById('qualityEstimate');
                const autoCalculateBtn = document.getElementById('autoCalculateBtn');

                // Элементы фаз сна
                const deepSleepInput = document.getElementById('deep_sleep');
                const lightSleepInput = document.getElementById('light_sleep');
                const remSleepInput = document.getElementById('rem_sleep');
                const awakeInput = document.getElementById('awake_time');

                // Прогресс-бары
                const deepBar = document.getElementById('deepBar');
                const lightBar = document.getElementById('lightBar');
                const remBar = document.getElementById('remBar');
                const awakeBar = document.getElementById('awakeBar');

                // Функция расчета длительности сна
                function calculateDuration() {
                    if (bedtimeInput.value && wakeupInput.value) {
                        const bedtime = new Date('1970-01-01T' + bedtimeInput.value + 'Z');
                        let wakeup = new Date('1970-01-01T' + wakeupInput.value + 'Z');

                        // Если время пробуждения раньше времени отхода ко сну, добавляем день
                        if (wakeup < bedtime) {
                            wakeup.setDate(wakeup.getDate() + 1);
                        }

                        const durationMs = wakeup - bedtime;
                        const durationMinutes = Math.floor(durationMs / (1000 * 60));
                        const hours = Math.floor(durationMinutes / 60);
                        const minutes = durationMinutes % 60;

                        durationDisplay.textContent = `${hours}ч ${minutes}м`;

                        // Оценка качества сна на основе длительности
                        let quality = 50; // Базовая оценка

                        if (durationMinutes >= 420 && durationMinutes <= 540) { // 7-9 часов
                            quality = 85;
                        } else if (durationMinutes >= 360 && durationMinutes < 420) { // 6-7 часов
                            quality = 70;
                        } else if (durationMinutes >= 300 && durationMinutes < 360) { // 5-6 часов
                            quality = 60;
                        } else if (durationMinutes < 300) { // Меньше 5 часов
                            quality = 40;
                        } else { // Больше 9 часов
                            quality = 65;
                        }

                        qualityEstimate.textContent = `~${quality}%`;
                        qualityEstimate.className = `fw-bold ${
                            quality >= 80 ? 'text-success' :
                                quality >= 60 ? 'text-warning' : 'text-danger'
                        }`;

                        return durationMinutes;
                    }

                    durationDisplay.textContent = '0ч 0м';
                    qualityEstimate.textContent = '~0%';
                    qualityEstimate.className = 'fw-bold text-secondary';
                    return 0;
                }

                // Функция обновления визуализации фаз сна
                function updateSleepPhasesVisualization() {
                    const totalDuration = calculateDuration();
                    if (totalDuration === 0) return;

                    const deep = parseInt(deepSleepInput.value) || 0;
                    const light = parseInt(lightSleepInput.value) || 0;
                    const rem = parseInt(remSleepInput.value) || 0;
                    const awake = parseInt(awakeInput.value) || 0;

                    const totalPhases = deep + light + rem + awake;

                    // Если фазы не заполнены, используем автоматическое распределение для визуализации
                    if (totalPhases === 0) {
                        const autoPhases = calculateAutoPhases(totalDuration);
                        updateProgressBars(autoPhases, totalDuration);
                    } else {
                        updateProgressBars({deep, light, rem, awake}, totalDuration);
                    }
                }

                // Функция автоматического расчета фаз сна
                function calculateAutoPhases(totalDuration) {
                    const awake = Math.min(15, Math.floor(totalDuration * 0.03)); // 3% но не более 15 минут
                    const remaining = totalDuration - awake;

                    const deep = Math.floor(remaining * 0.20); // 20% глубокий сон
                    const rem = Math.floor(remaining * 0.25);  // 25% REM сон
                    const light = remaining - deep - rem;      // Остальное - легкий сон

                    return { deep, light, rem, awake };
                }

                // Функция обновления прогресс-баров
                function updateProgressBars(phases, totalDuration) {
                    const { deep, light, rem, awake } = phases;

                    const deepPercent = totalDuration > 0 ? (deep / totalDuration) * 100 : 0;
                    const lightPercent = totalDuration > 0 ? (light / totalDuration) * 100 : 0;
                    const remPercent = totalDuration > 0 ? (rem / totalDuration) * 100 : 0;
                    const awakePercent = totalDuration > 0 ? (awake / totalDuration) * 100 : 0;

                    deepBar.style.width = `${deepPercent}%`;
                    lightBar.style.width = `${lightPercent}%`;
                    remBar.style.width = `${remPercent}%`;
                    awakeBar.style.width = `${awakePercent}%`;

                    deepBar.innerHTML = `<small>Глубокий: ${Math.round(deepPercent)}%</small>`;
                    lightBar.innerHTML = `<small>Легкий: ${Math.round(lightPercent)}%</small>`;
                    remBar.innerHTML = `<small>REM: ${Math.round(remPercent)}%</small>`;
                    awakeBar.innerHTML = `<small>Бодр: ${Math.round(awakePercent)}%</small>`;
                }

                // Функция авторасчета фаз сна
                function autoCalculatePhases() {
                    const totalDuration = calculateDuration();
                    if (totalDuration === 0) {
                        alert('Сначала укажите время сна и пробуждения');
                        return;
                    }

                    const phases = calculateAutoPhases(totalDuration);

                    deepSleepInput.value = phases.deep;
                    lightSleepInput.value = phases.light;
                    remSleepInput.value = phases.rem;
                    awakeInput.value = phases.awake;

                    updateSleepPhasesVisualization();

                    // Показать уведомление
                    showNotification('Фазы сна автоматически рассчитаны!', 'success');
                }

                // Функция показа уведомлений
                function showNotification(message, type) {
                    const alert = document.createElement('div');
                    alert.className = `alert alert-${type} alert-dismissible fade show`;
                    alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

                    const form = document.getElementById('sleepForm');
                    form.parentNode.insertBefore(alert, form);

                    // Автоматически скрыть через 3 секунды
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.remove();
                        }
                    }, 3000);
                }

                // Обработчики событий
                bedtimeInput.addEventListener('change', function() {
                    calculateDuration();
                    updateSleepPhasesVisualization();
                });

                wakeupInput.addEventListener('change', function() {
                    calculateDuration();
                    updateSleepPhasesVisualization();
                });

                autoCalculateBtn.addEventListener('click', autoCalculatePhases);

                // Обработчики для полей фаз сна
                [deepSleepInput, lightSleepInput, remSleepInput, awakeInput].forEach(input => {
                    input.addEventListener('input', updateSleepPhasesVisualization);
                });

                // Инициализация при загрузке
                calculateDuration();
                updateSleepPhasesVisualization();

                // Валидация формы
                document.getElementById('sleepForm').addEventListener('submit', function(e) {
                    const totalDuration = calculateDuration();

                    if (totalDuration < 180) { // Меньше 3 часов
                        if (!confirm('Вы указали очень короткий сон (менее 3 часов). Вы уверены, что хотите сохранить?')) {
                            e.preventDefault();
                            return;
                        }
                    }

                    if (totalDuration > 720) { // Больше 12 часов
                        if (!confirm('Вы указали очень длинный сон (более 12 часов). Вы уверены, что хотите сохранить?')) {
                            e.preventDefault();
                            return;
                        }
                    }

                    // Проверка согласованности фаз сна
                    const deep = parseInt(deepSleepInput.value) || 0;
                    const light = parseInt(lightSleepInput.value) || 0;
                    const rem = parseInt(remSleepInput.value) || 0;
                    const awake = parseInt(awakeInput.value) || 0;

                    const totalPhases = deep + light + rem + awake;

                    if (totalPhases > 0 && Math.abs(totalPhases - totalDuration) > 30) {
                        if (!confirm(`Сумма фаз сна (${totalPhases} мин) значительно отличается от общей длительности (${totalDuration} мин). Продолжить сохранение?`)) {
                            e.preventDefault();
                            return;
                        }
                    }
                });
            });

            // Добавляем CSS для цветов
            const style = document.createElement('style');
            style.textContent = `
    .text-brown { color: #8B4513; }
    .text-purple { color: #9370DB; }
    .text-orange { color: #FFA500; }
    .text-red { color: #DC3545; }
    .text-green { color: #28A745; }
    .text-blue { color: #007BFF; }

    .progress-bar.deep-sleep { background: linear-gradient(135deg, #667eea, #764ba2); }
    .progress-bar.light-sleep { background: linear-gradient(135deg, #f093fb, #f5576c); }
    .progress-bar.rem-sleep { background: linear-gradient(135deg, #4facfe, #00f2fe); }
    .progress-bar.awake-sleep { background: linear-gradient(135deg, #43e97b, #38f9d7); }

    .progress-bar {
        position: relative;
        overflow: visible;
        color: white;
        font-weight: bold;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    }

    .progress-bar small {
        font-size: 0.7em;
        white-space: nowrap;
    }
`;
            document.head.appendChild(style);
        </script>
    @endsection
@endsection
