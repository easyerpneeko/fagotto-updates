@extends('layouts.admin')

@section('title', 'Configuración Gelateria')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-gradient-primary">
                    <h3 class="card-title text-white">
                        <i class="fas fa-ice-cream mr-2"></i>
                        Configuración del Módulo Gelateria
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 offset-md-2">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Información:</strong> Este módulo permite activar o desactivar el botón "Gelateria" 
                                en el sistema de ventas. Cuando está activo, los usuarios podrán acceder a las categorías de 
                                Copas, Barquillos, Postres y Extras.
                            </div>
                            
                            <div class="card">
                                <div class="card-body">
                                    <form id="gelateriaConfigForm">
                                        <div class="form-group">
                                            <label class="d-flex align-items-center justify-content-between">
                                                <span class="font-weight-bold">
                                                    <i class="fas fa-toggle-on mr-2 text-success"></i>
                                                    Estado del Módulo
                                                </span>
                                                <div class="custom-control custom-switch custom-switch-lg">
                                                    <input 
                                                        type="checkbox" 
                                                        class="custom-control-input" 
                                                        id="isActiveSwitch"
                                                        {{ $config->is_active ? 'checked' : '' }}
                                                    >
                                                    <label class="custom-control-label" for="isActiveSwitch">
                                                        <span class="status-text font-weight-bold">
                                                            {{ $config->is_active ? 'Activado' : 'Desactivado' }}
                                                        </span>
                                                    </label>
                                                </div>
                                            </label>
                                        </div>
                                        
                                        <div class="alert alert-warning mt-3" id="categoryWarning">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            <strong>Importante:</strong> Asegúrate de tener productos en las siguientes categorías:
                                            <ul class="mb-0 mt-2">
                                                <li><strong>Categoría 55:</strong> Copas</li>
                                                <li><strong>Categoría 56:</strong> Barquillos</li>
                                                <li><strong>Categoría 57:</strong> Postres</li>
                                                <li><strong>Categoría 58:</strong> Extras</li>
                                            </ul>
                                        </div>
                                        
                                        <div class="form-group mt-4">
                                            <button type="submit" class="btn btn-success btn-lg btn-block">
                                                <i class="fas fa-save mr-2"></i>
                                                Guardar Configuración
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="card mt-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="fas fa-layer-group mr-2"></i>
                                        Categorías del Sistema
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="info-box bg-gradient-info">
                                                <span class="info-box-icon"><i class="fas fa-ice-cream"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Copas</span>
                                                    <span class="info-box-number">Categoría ID: 55</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="info-box bg-gradient-warning">
                                                <span class="info-box-icon"><i class="fas fa-cookie"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Barquillos</span>
                                                    <span class="info-box-number">Categoría ID: 56</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="info-box bg-gradient-pink">
                                                <span class="info-box-icon"><i class="fas fa-birthday-cake"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Postres</span>
                                                    <span class="info-box-number">Categoría ID: 57</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="info-box bg-gradient-success">
                                                <span class="info-box-icon"><i class="fas fa-candy-cane"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Extras</span>
                                                    <span class="info-box-number">Categoría ID: 58</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Actualizar texto del estado al cambiar el switch
    $('#isActiveSwitch').on('change', function() {
        const isActive = $(this).is(':checked');
        $('.status-text').text(isActive ? 'Activado' : 'Desactivado');
        $('.status-text').removeClass('text-danger text-success');
        $('.status-text').addClass(isActive ? 'text-success' : 'text-danger');
    });
    
    // Enviar formulario
    $('#gelateriaConfigForm').on('submit', function(e) {
        e.preventDefault();
        
        const isActive = $('#isActiveSwitch').is(':checked');
        
        $.ajax({
            url: '/admin/gelateria/config/update',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                is_active: isActive ? 1 : 0
            },
            beforeSend: function() {
                Swal.fire({
                    title: 'Guardando...',
                    text: 'Por favor espera',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo actualizar la configuración',
                });
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.custom-switch-lg .custom-control-label::before {
    height: 2rem;
    width: 3.5rem;
    border-radius: 4rem;
}

.custom-switch-lg .custom-control-label::after {
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
}

.custom-switch-lg .custom-control-input:checked ~ .custom-control-label::after {
    transform: translateX(1.5rem);
}

.bg-gradient-pink {
    background: linear-gradient(135deg, #FF6B9D 0%, #FFA07A 100%) !important;
    color: white;
}

.info-box {
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.status-text {
    font-size: 1.1rem;
}
</style>
@endpush
