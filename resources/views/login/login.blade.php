<?php
  Debugbar::disable();
?>
<div class="login-page-rgba">

  <div class="login-page">
    <div class="login-box">
        <div class="login-logo">
          <a><b>Easy</b>ERP</a>
        </div>
        <!-- /.login-logo -->
        <div class="card">
          <div class="card-body login-card-body card_size">
            <p class="login-box-msg login-init-section">Inicia sesión</p>

            <form method="POST" action="{{ route('login') }}">
              @csrf
              @if (session('error'))
                <div class="alert alert-danger aler-error-login" >{{ session('error') }}</div>
              @endif
              @foreach ($errors->all() as $error)
                <div class="alert alert-danger aler-error-login">{{ $error }}</div>
              @endforeach
              <div class="input-group mb-3">
                <input name="username" type="text" class="form-control form-font" value="{{ old('username') }}" placeholder="Nombre de usuario">
                <div class="input-group-append">
                  <div class="input-group-text">
                    <span class="fas fa-user"></span>
                  </div>
                </div>
              </div>
              <div class="input-group mb-3">
                <input name="password" type="password" class="form-control form-font" value="{{ old('password') }}" placeholder="Contraseña">
                <div class="input-group-append">
                  <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                  </div>
                </div>

              </div>
              <div class="row">
                <div class="col-8">
                  <div class="icheck-primary">
                    <input type="checkbox" id="remember">
                    <label for="remember">
                        Recuérdame
                    </label>
                  </div>
                </div>
                <!-- /.col -->
                <!-- /.col -->
              </div>
              <div class="social-auth-links text-center mb-3 ">
                <button type="submit" class="btn btn-primary btn-block font-btn">iniciar sesión</button>
              </div>
            </form>

            <!-- <div class="social-auth-links text-center mb-3">
              <a href="#" class="btn btn-block btn-primary btn_login">
                iniciar sesión
              </a>
              <a href="#" class="btn btn-block btn-danger">
                Register
              </a>
            </div> -->
          </div>
          <!-- /.login-card-body -->
        </div>
      </div>
    </div>
</div>
