@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-white">
                        <i class="fas fa-heart me-2"></i>Рекомендации для здоровья
                    </h2>
                    <button class="btn btn-outline-light" id="dailyTipBtn">
                        <i class="fas fa-lightbulb me-1"></i>Случайный совет
                    </button>
                </div>
            </div>
        </div>

        <!-- Персональные рекомендации -->
        @if(count($personalizedRecommendations) > 0)
            <div class="row mb-5">
                <div class="col-12">
                    <div class="dashboard-card p-4">
                        <h3 class="h4 mb-4">
                            <i class="fas fa-user-check text-primary me-2"></i>Персональные рекомендации
                        </h3>

                        <div class="row">
                            @foreach($personalizedRecommendations as $recommendation)
                                <div class="col-md-6 mb-3">
                                    <div class="card border-0 shadow-sm h-100
                            {{ $recommendation['priority'] == 'high' ? 'border-danger' :
                               ($recommendation['priority'] == 'medium' ? 'border-warning' : 'border-info') }}">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-{{ $recommendation['priority'] == 'high' ? 'exclamation-triangle text-danger' :
                                                           ($recommendation['priority'] == 'medium' ? 'info-circle text-warning' : 'check-circle text-info') }}
                                            fa-2x"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h5 class="card-title mb-2">{{ $recommendation['title'] }}</h5>
                                                    <p class="card-text text-muted">{{ $recommendation['message'] }}</p>
                                                    <span class="badge bg-{{ $recommendation['priority'] == 'high' ? 'danger' :
                                                               ($recommendation['priority'] == 'medium' ? 'warning' : 'info') }}">
                                            {{ $recommendation['priority'] == 'high' ? 'Высокий приоритет' :
                                               ($recommendation['priority'] == 'medium' ? 'Средний приоритет' : 'Низкий приоритет') }}
                                        </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Рекомендации по категориям -->
        @foreach($recommendations as $category => $items)
            <div class="row mb-5">
                <div class="col-12">
                    <div class="dashboard-card p-4">
                        <h3 class="h4 mb-4 text-capitalize">
                            @switch($category)
                                @case('sleep')
                                    <i class="fas fa-bed text-primary me-2"></i>Сон и отдых
                                    @break
                                @case('activity')
                                    <i class="fas fa-running text-success me-2"></i>Физическая активность
                                    @break
                                @case('nutrition')
                                    <i class="fas fa-apple-alt text-warning me-2"></i>Питание
                                    @break
                                @case('mental_health')
                                    <i class="fas fa-spa text-info me-2"></i>Ментальное здоровье
                                    @break
                                @default
                                    <i class="fas fa-star text-secondary me-2"></i>{{ ucfirst($category) }}
                            @endswitch
                        </h3>

                        <div class="row">
                            @foreach($items as $recommendation)
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 border-0 shadow-sm hover-lift">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="flex-shrink-0">
                                                    <i class="{{ $recommendation->icon }} fa-2x text-{{ $recommendation->category_color }}"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h5 class="card-title mb-2">{{ $recommendation->title }}</h5>
                                                    <p class="card-text text-muted">{{ $recommendation->description }}</p>
                                                </div>
                                            </div>

                                            @if(count($recommendation->tips_array) > 0)
                                                <div class="mt-3">
                                                    <h6 class="small fw-bold text-uppercase text-muted mb-2">Практические советы:</h6>
                                                    <ul class="list-unstyled small mb-0">
                                                        @foreach($recommendation->tips_array as $tip)
                                                            <li class="mb-1">
                                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                                {{ $tip }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="card-footer bg-transparent border-top-0 pt-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="fas fa-star text-warning me-1"></i>
                                                    Приоритет: {{ $recommendation->priority }}/10
                                                </small>
                                                <button class="btn btn-sm btn-outline-primary mark-read-btn"
                                                        data-id="{{ $recommendation->id }}">
                                                    <i class="fas fa-check me-1"></i>Прочитано
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Нет рекомендаций -->
        @if($recommendations->isEmpty() && empty($personalizedRecommendations))
            <div class="row">
                <div class="col-12">
                    <div class="dashboard-card p-5 text-center">
                        <i class="fas fa-heart fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Рекомендации появятся скоро</h4>
                        <p class="text-muted mb-4">Добавляйте данные о сне и активности для получения персонализированных рекомендаций</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="{{ route('sleep.create') }}" class="btn btn-primary">
                                <i class="fas fa-bed me-2"></i>Добавить сон
                            </a>
                            <a href="{{ route('activity.create') }}" class="btn btn-success">
                                <i class="fas fa-running me-2"></i>Добавить активность
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Модальное окно для ежедневного совета -->
    <div class="modal fade" id="dailyTipModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-lightbulb text-warning me-2"></i>Совет дня
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="dailyTipContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Загрузка...</span>
                        </div>
                        <p class="mt-2">Загружаем совет...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    @section('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Кнопка случайного совета
                const dailyTipBtn = document.getElementById('dailyTipBtn');
                const dailyTipModal = new bootstrap.Modal(document.getElementById('dailyTipModal'));
                const dailyTipContent = document.getElementById('dailyTipContent');

                dailyTipBtn.addEventListener('click', function() {
                    dailyTipModal.show();

                    // Загружаем случайный совет
                    axios.get('{{ route("recommendations.daily.tip") }}')
                        .then(response => {
                            dailyTipContent.innerHTML = `
                    <div class="text-center">
                        <i class="${response.data.icon} fa-3x text-primary mb-3"></i>
                        <h5>${response.data.title}</h5>
                        <p class="text-muted">${response.data.description}</p>
                        <span class="badge bg-secondary">${response.data.category}</span>
                    </div>
                `;
                        })
                        .catch(error => {
                            dailyTipContent.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Не удалось загрузить совет. Попробуйте позже.
                    </div>
                `;
                        });
                });

                // Отметка как прочитанного
                document.querySelectorAll('.mark-read-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const recommendationId = this.dataset.id;
                        const card = this.closest('.card');

                        axios.post(`/recommendations/${recommendationId}/read`)
                            .then(response => {
                                card.style.opacity = '0.6';
                                this.innerHTML = '<i class="fas fa-check me-1"></i>Прочитано';
                                this.classList.remove('btn-outline-primary');
                                this.classList.add('btn-outline-secondary');
                                this.disabled = true;
                            })
                            .catch(error => {
                                alert('Ошибка при отметке рекомендации');
                            });
                    });
                });
            });
        </script>
    @endsection
@endsection
