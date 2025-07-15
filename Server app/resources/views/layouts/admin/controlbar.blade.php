<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
    <div class="p-3">
        <h5 class="text-capitallice">
          Master
        </h5>
        <a class="d-block" href="javascript:{}" onclick="document.getElementById('logout-form').submit();">Cerrar sección</a>
        <form id="logout-form" action="{{ route('logout') }}" method="GET" style="display: none;">
            @csrf
        </form>
    </div>
</aside>
