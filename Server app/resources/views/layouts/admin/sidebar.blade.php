<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
        <img src="/images/logo-5.png" alt="Logo" class="brand-image1 img-circle elevation-3">
        <span class="brand-text font-weight-light">
          <img src="/images/logo-4.png" alt="Logo" class="brand-image2">
           </span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex" >
            <div class="image">
                <img src="/images/admin.jpg" class="img-circle elevation-2 user-panel-img" alt="User Image">
            </div>
            <div class="info">
              <a class="d-block text-capitallice" href="#">
                Master
              </a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <router-link tag="a" to="/admin/inicio" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Inicio</p>
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link tag="a" to="/admin/clientes" class="nav-link">
                        <i class="nav-icon fas fa-user-friends"></i>
                        <p>Clientes</p>
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link tag="a" to="/admin/aplicaciones" class="nav-link">
                        <i class="nav-icon fas fa-th"></i>
                        <p>Aplicaciones</p>
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link tag="a" to="/admin/modulos" class="nav-link">
                        <i class="nav-icon far fa-copy"></i>
                        <p>Modulos</p>
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link tag="a" to="/admin/noticias" class="nav-link">
                        <i class="nav-icon far fa-comment-alt"></i>
                        <p>Noticias</p>
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link tag="a" to="/admin/pedidos" class="nav-link">
                        <i class="nav-icon fas fa-truck"></i>
                        <p>Pedidos Fagotto</p>
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link tag="a" to="/admin/reposteria" class="nav-link">
                        <i class="nav-icon fas fa-birthday-cake"></i>
                        <p>Reposteria Fagotto</p>
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link tag="a" to="/admin/ingredients" class="nav-link">
                        <i class="fas fa-wine-bottle"></i>
                        <p>Ingredientes de Salsas</p>
                    </router-link>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
