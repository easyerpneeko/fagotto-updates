<?php
  Debugbar::disable();
?>

<div class="fagotto-login-container">
  <div class="login-content">
    <!-- Logo Section -->
    <div class="logo-section">
      <div class="logo-container">
        <img src="{{ asset('images/logo_fagotto.png') }}" alt="Fagotto ERP" class="fagotto-logo" />
      </div>
      <h1 class="main-title">Fagotto</h1>
      <p class="subtitle">Bienvenido de vuelta</p>
      <p class="login-description">Ingresa a tu cuenta para continuar</p>
    </div>

    <!-- Login Card -->
    <div class="login-card">
      <div class="card-header">
        <div class="header-icon">🔐</div>
        <h2>Iniciar Sesión</h2>
      </div>

      <div class="card-body">
        <form method="POST" action="{{ route('login') }}">
          @csrf
          
          <!-- Error Messages -->
          @if (session('error'))
            <div class="alert alert-error">
              <span class="error-icon">⚠️</span>
              {{ session('error') }}
            </div>
          @endif
          @foreach ($errors->all() as $error)
            <div class="alert alert-error">
              <span class="error-icon">⚠️</span>
              {{ $error }}
            </div>
          @endforeach

          <!-- Username Input -->
          <div class="input-group">
            <div class="input-wrapper">
              <div class="input-icon">
                <i class="fas fa-user"></i>
              </div>
              <input 
                name="username" 
                type="text" 
                class="form-input" 
                value="{{ old('username') }}" 
                placeholder="Nombre de usuario"
                required
              >
            </div>
          </div>

          <!-- Password Input -->
          <div class="input-group">
            <div class="input-wrapper">
              <div class="input-icon">
                <i class="fas fa-lock"></i>
              </div>
              <input 
                name="password" 
                type="password" 
                class="form-input" 
                placeholder="Contraseña"
                required
              >
              <div class="password-toggle" onclick="togglePassword()">
                <i class="fas fa-eye" id="passwordToggleIcon"></i>
              </div>
            </div>
          </div>

          <!-- Remember Me -->
          <div class="remember-section">
            <label class="remember-checkbox">
              <input type="checkbox" id="remember" name="remember">
              <span class="checkmark"></span>
              <span class="remember-text">Recuérdame</span>
            </label>
          </div>

          <!-- Submit Button -->
          <button type="submit" class="login-btn">
            <span class="btn-content">
              <span class="btn-icon">✨</span>
              Iniciar Sesión
            </span>
          </button>
        </form>
      </div>
    </div>
  </div>
  
  <!-- Animated Background -->
  <div class="background-animation">
    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>
    <div class="floating-shape shape-3"></div>
    <div class="floating-shape shape-4"></div>
    <div class="floating-shape shape-5"></div>
  </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.querySelector('input[name="password"]');
    const toggleIcon = document.getElementById('passwordToggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// Add some interactive effects
document.addEventListener('DOMContentLoaded', function() {
    // Focus animation for inputs
    const inputs = document.querySelectorAll('.form-input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });
    
    // Button hover effect
    const loginBtn = document.querySelector('.login-btn');
    if (loginBtn) {
        loginBtn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.02)';
        });
        
        loginBtn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    }
});
</script>
