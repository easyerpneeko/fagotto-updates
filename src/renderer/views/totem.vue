<template>
  <div class="totem-wrapper">
    <!-- PANTALLA DE BIENVENIDA -->
    <div v-if="pantalla === 'bienvenida'" class="pantalla-bienvenida">
      <div class="bienvenida-content">
        <div class="logo-fagotto">
          <h1>🍝 Fagotto</h1>
        </div>
        <h2 class="bienvenida-titulo">Bienvenido al POS Fagotto</h2>
        <p class="bienvenida-subtitulo">Toca la pantalla para comenzar</p>
        <button class="btn-comenzar" @click="irASeleccion">
          <i class="fas fa-hand-pointer"></i>
          Comenzar Pedido
        </button>
      </div>
    </div>

    <!-- PANTALLA SELECCIÓN COMER AQUÍ / PARA LLEVAR -->
    <div v-else-if="pantalla === 'seleccion'" class="pantalla-seleccion">
      <div class="seleccion-content">
        <h2 class="seleccion-titulo">¿Dónde vas a comer hoy?</h2>
        
        <div class="seleccion-opciones">
          <div class="opcion-card" @click="seleccionarTipo('aqui')">
            <div class="opcion-icon">
              <i class="fas fa-utensils"></i>
            </div>
            <h3>Comer aquí</h3>
          </div>

          <div class="opcion-card" @click="seleccionarTipo('llevar')">
            <div class="opcion-icon">
              <i class="fas fa-shopping-bag"></i>
            </div>
            <h3>Para llevar</h3>
          </div>
        </div>

        <button class="btn-volver" @click="volverBienvenida">
          <i class="fas fa-arrow-left"></i>
          Volver
        </button>
      </div>
    </div>

    <!-- PANTALLA MENÚ PRINCIPAL -->
    <div v-else class="mcd-container">
    <!-- SIDEBAR CATEGORÍAS -->
    <aside class="mcd-sidebar">
      <div class="mcd-logo">
        <h2>� Menú</h2>
      </div>
      <nav class="mcd-categories">
        <button 
          v-for="categoria in categorias" 
          :key="categoria.id"
          :class="['mcd-category-btn', { active: categoriaActiva === categoria.id }]"
          @click="filtrarPorCategoria(categoria.id)">
          <i :class="categoria.icono"></i>
          <span>{{ categoria.nombre }}</span>
        </button>
      </nav>
    </aside>

    <!-- MAIN PRODUCTOS -->
    <main class="mcd-main">
      <div class="mcd-header">
        <h1>¡Hora del antojo!</h1>
        <p>Descubre nuestro menú</p>
      </div>

      <div class="mcd-products-grid">
        <div 
          v-for="producto in productosFiltrados" 
          :key="producto.id"
          class="mcd-product-card"
          @click="clickProducto(producto)">
          <div class="mcd-product-image">
            <img :src="producto.imagen || 'https://via.placeholder.com/150'" :alt="producto.nombre">
          </div>
          <div class="mcd-product-info">
            <h3>{{ producto.nombre }}</h3>
            <p v-if="producto.descripcion">{{ producto.descripcion }}</p>
            <div class="mcd-product-price">${{ formatPrice(producto.precio) }}</div>
          </div>
        </div>

        <div v-if="productosFiltrados.length === 0" class="mcd-no-products">
          <i class="fas fa-search"></i>
          <p>No hay productos en esta categoría</p>
        </div>
      </div>
    </main>

    <!-- CART PANEL -->
    <aside class="mcd-cart-panel">
      <div class="mcd-cart-header">
        <h3><i class="fas fa-shopping-cart"></i> Tu Pedido</h3>
      </div>

      <div class="mcd-cart-items">
        <div v-if="carrito.length === 0" class="mcd-cart-empty">
          <i class="fas fa-shopping-basket"></i>
          <p>Tu carrito está vacío</p>
        </div>

        <div v-for="(item, index) in carrito" :key="index" class="mcd-cart-item">
          <div class="mcd-cart-item-info">
            <strong>{{ item.nombre }}</strong>
            <div class="mcd-cart-item-controls">
              <button class="mcd-btn-qty" @click="decrementarCantidad(index)">
                <i class="fas fa-minus"></i>
              </button>
              <span class="mcd-qty">{{ item.cantidad }}</span>
              <button class="mcd-btn-qty" @click="incrementarCantidad(index)">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </div>
          <div class="mcd-cart-item-price">
            ${{ formatPrice(item.precio * item.cantidad) }}
            <button class="mcd-btn-remove" @click="eliminarDelCarrito(index)">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
      </div>

      <div class="mcd-cart-footer">
        <div class="mcd-cart-total">
          <span>Total:</span>
          <strong>${{ formatPrice(totalCarrito) }}</strong>
        </div>
        <button 
          class="mcd-btn-pay" 
          @click="procesarPago"
          :disabled="isProcessing || carrito.length === 0">
          <i :class="isProcessing ? 'fas fa-spinner fa-spin' : 'fas fa-credit-card'"></i>
          {{ isProcessing ? 'Procesando...' : 'Pagar con Mercado Pago' }}
        </button>
        <button 
          class="mcd-btn-clear" 
          @click="limpiarCarrito"
          :disabled="carrito.length === 0">
          <i class="fas fa-trash"></i>
          Limpiar Carrito
        </button>
      </div>
    </aside>
  </div>

    <!-- MODAL SELECTOR DE SALSAS -->
    <div v-if="mostrarModalSalsas" class="modal-salsas-overlay" @click="cerrarModalSalsas">
      <div class="modal-salsas-content" @click.stop>
        <div class="modal-salsas-header">
          <h2>🍝 {{ pastaSeleccionada.nombre }}</h2>
          <p>Elige tu salsa favorita</p>
          <button class="btn-cerrar-modal" @click="cerrarModalSalsas">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="salsas-grid">
          <div 
            v-for="salsa in salsasDisponibles" 
            :key="salsa.id" 
            class="salsa-card"
            @click="seleccionarSalsa(salsa)"
          >
            <div class="salsa-imagen">
              <i class="fas fa-pepper-hot"></i>
            </div>
            <h4>{{ salsa.nombre }}</h4>
            <p class="salsa-precio">${{ formatPrice(salsa.precio) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL PROMOCIÓN MULTI-PASO -->
    <div v-if="mostrarModalPromocion" class="modal-salsas-overlay" @click="cerrarModalPromocion">
      <div class="modal-promo-content" @click.stop>
        <button class="btn-cerrar-modal" @click="cerrarModalPromocion">
          <i class="fas fa-times"></i>
        </button>

        <!-- PASO 1: ELEGIR PASTA -->
        <div v-if="pasoPromocion === 1" class="paso-promo">
          <div class="promo-header">
            <h2>🍝 Paso 1: Elige tu Pasta</h2>
            <p class="paso-indicador">Paso 1 de 4</p>
          </div>
          <div class="opciones-grid-2">
            <div class="opcion-promo-card" @click="seleccionarPastaPromo('Bigoli')">
              <i class="fas fa-bowl-rice"></i>
              <h3>Bigoli</h3>
            </div>
            <div class="opcion-promo-card" @click="seleccionarPastaPromo('Fettuccine')">
              <i class="fas fa-bowl-rice"></i>
              <h3>Fettuccine</h3>
            </div>
          </div>
        </div>

        <!-- PASO 2: ELEGIR SALSA -->
        <div v-if="pasoPromocion === 2" class="paso-promo">
          <div class="promo-header">
            <h2>🍝 Paso 2: Elige tu Salsa</h2>
            <p class="paso-indicador">Paso 2 de 4</p>
            <p class="seleccion-previa">{{ promocion.pasta }}</p>
          </div>
          <div class="salsas-grid">
            <div 
              v-for="salsa in salsasDisponibles" 
              :key="salsa.id"
              class="salsa-card-promo"
              @click="seleccionarSalsaPromo(salsa.nombre, salsa.precio)">
              <div class="salsa-icon">
                <i class="fas fa-bowl-food"></i>
              </div>
              <h3>{{ salsa.nombre }}</h3>
            </div>
          </div>
        </div>

        <!-- PASO 3: ELEGIR BEBIDA -->
        <div v-if="pasoPromocion === 3" class="paso-promo">
          <div class="promo-header">
            <h2>🥤 Paso 3: Elige tu Bebida</h2>
            <p class="paso-indicador">Paso 3 de 4</p>
            <p class="seleccion-previa">{{ promocion.pasta }} {{ promocion.salsa }}</p>
          </div>
          <div class="opciones-grid-2">
            <div 
              v-for="bebida in bebidas" 
              :key="bebida.id"
              class="opcion-promo-card"
              @click="seleccionarBebidaPromo(bebida.nombre)">
              <i class="fas fa-glass-whiskey"></i>
              <h3>{{ bebida.nombre }}</h3>
            </div>
          </div>
        </div>

        <!-- PASO 4: EXTRAS -->
        <div v-if="pasoPromocion === 4" class="paso-promo">
          <div class="promo-header">
            <h2>✨ Paso 4: ¿Quieres agregar algo más?</h2>
            <p class="paso-indicador">Paso 4 de 4</p>
            <p class="seleccion-previa">{{ promocion.pasta }} {{ promocion.salsa }} + {{ promocion.bebida }}</p>
          </div>
          
          <div class="extras-container">
            <div class="extra-card" @click="promocion.quesoExtra = !promocion.quesoExtra" :class="{ active: promocion.quesoExtra }">
              <div class="extra-icon">
                <i class="fas fa-cheese"></i>
              </div>
              <h3>Queso Extra</h3>
              <p class="extra-precio">+${{ formatPrice(promocion.precioQuesoExtra) }}</p>
              <div class="extra-check" v-if="promocion.quesoExtra">
                <i class="fas fa-check"></i>
              </div>
            </div>

            <div class="extra-card" @click="mostrarSelectorSalsaExtra" :class="{ active: promocion.salsaExtra }">
              <div class="extra-icon">
                <i class="fas fa-bottle-droplet"></i>
              </div>
              <h3>Salsa Extra</h3>
              <p class="extra-precio">+${{ formatPrice(promocion.precioSalsaExtra) }}</p>
              <p v-if="promocion.nombreSalsaExtra" class="salsa-elegida">{{ promocion.nombreSalsaExtra }}</p>
              <div class="extra-check" v-if="promocion.salsaExtra">
                <i class="fas fa-check"></i>
              </div>
            </div>
          </div>

          <div class="promo-total">
            <span>Total Promoción:</span>
            <strong>${{ formatPrice((promocion.precioSalsa || 4990) + 1000 + (promocion.quesoExtra ? promocion.precioQuesoExtra : 0) + (promocion.salsaExtra ? promocion.precioSalsaExtra : 0)) }}</strong>
          </div>

          <button class="btn-finalizar-promo" @click="finalizarPromocion">
            <i class="fas fa-check-circle"></i>
            Agregar al Carrito
          </button>
        </div>
        
        <!-- SELECTOR DE SALSA EXTRA -->
        <div v-if="mostrandoSelectorSalsaExtra" class="selector-salsa-extra-overlay">
          <div class="selector-salsa-extra-content">
            <h3>🌶️ ¿Qué salsa extra querés?</h3>
            <div class="salsas-grid-extra">
              <div 
                v-for="salsa in salsasDisponibles" 
                :key="salsa.id"
                class="salsa-extra-card"
                @click="seleccionarSalsaExtra(salsa)"
              >
                <i class="fas fa-pepper-hot"></i>
                <h4>{{ salsa.nombre }}</h4>
              </div>
            </div>
            <button class="btn-cancelar-salsa" @click="cancelarSalsaExtra">
              <i class="fas fa-times"></i> Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TotemView',
  data() {
    return {
      pantalla: 'bienvenida', // bienvenida, seleccion, menu
      tipoServicio: null, // 'aqui' o 'llevar'
      categorias: [],
      productos: [],
      carrito: [],
      categoriaActiva: null,
      isProcessing: false,
      mostrarModalSalsas: false,
      pastaSeleccionada: null,
      salsas: [
        { id: 1, nombre: 'ALFREDO', precio: 3990 },
        { id: 2, nombre: 'BOLOÑESA', precio: 3990 },
        { id: 3, nombre: 'CAMARÓN', precio: 4990 },
        { id: 4, nombre: 'CHAMPIÑÓN', precio: 4990 },
        { id: 5, nombre: 'PESTO', precio: 4990 },
        { id: 6, nombre: 'CREMA POLLO MOSTAZA', precio: 4990 }
      ],
      // Promoción multi-paso
      mostrarModalPromocion: false,
      mostrandoSelectorSalsaExtra: false,
      pasoPromocion: 1, // 1=pasta, 2=salsa, 3=bebida, 4=extras
      promocion: {
        pasta: null,
        salsa: null,
        bebida: null,
        quesoExtra: false,
        salsaExtra: false,
        precioBase: 5990,
        precioQuesoExtra: 500,
        precioSalsaExtra: 700
      },
      bebidas: [
        { id: 1, nombre: 'Pepsi 220cc' },
        { id: 2, nombre: 'Pepsi Zero 220cc' },
        { id: 3, nombre: 'Crush 220cc' },
        { id: 4, nombre: '7up 220cc' },
        { id: 5, nombre: 'Kem 220cc' },
        { id: 6, nombre: 'Cachantun con gas 220cc' },
        { id: 7, nombre: 'Cachantun sin gas 220cc' },
        { id: 8, nombre: 'Coca Cola 220cc' },
        { id: 9, nombre: 'Coca Cola Zero 220cc' },
        { id: 10, nombre: 'Fanta 220cc' },
        { id: 11, nombre: 'Sprite 220cc' }
      ]
    };
  },
  
  computed: {
    productosFiltrados() {
      if (!this.categoriaActiva) {
        return this.productos;
      }
      
      let filtrados = this.productos.filter(p => p.categoria_id === this.categoriaActiva);
      
      // Si es categoría Extras (4), solo mostrar productos con "Extra" en el nombre
      if (this.categoriaActiva === 4) {
        filtrados = filtrados.filter(p => p.nombre.toLowerCase().includes('extra'));
      }
      
      return filtrados;
    },
    
    salsasDisponibles() {
      // Filtrar productos de categoría Extras que sean salsas (no incluir Queso Extra ni Salsa Extra)
      return this.productos.filter(p => 
        p.categoria_id === 4 && 
        !p.nombre.toLowerCase().includes('extra') &&
        !p.nombre.toLowerCase().includes('queso')
      );
    },
    
    totalCarrito() {
      return this.carrito.reduce((total, item) => {
        return total + (parseFloat(item.precio) * item.cantidad);
      }, 0);
    }
  },
  
  mounted() {
    this.cargarDatos();
  },
  
  methods: {
    irASeleccion() {
      this.pantalla = 'seleccion';
    },
    
    volverBienvenida() {
      this.pantalla = 'bienvenida';
      this.tipoServicio = null;
    },
    
    seleccionarTipo(tipo) {
      this.tipoServicio = tipo;
      this.pantalla = 'menu';
    },
    
    async cargarDatos() {
      try {
        const response = await fetch('https://posfagotto.cl/api/totem/data');
        const data = await response.json();
        
        if (data.success) {
          this.categorias = data.data.categorias;
          this.productos = data.data.productos;
          
          // Seleccionar primera categoría por defecto
          if (this.categorias.length > 0) {
            this.categoriaActiva = this.categorias[0].id;
          }
          
          console.log('✅ Datos cargados:', data.data);
        }
      } catch (error) {
        console.error('❌ Error al cargar datos:', error);
        this.$bvToast.toast('No se pudieron cargar los productos', {
          title: 'Error',
          variant: 'danger',
          solid: true
        });
      }
    },
    
    filtrarPorCategoria(categoriaId) {
      this.categoriaActiva = categoriaId;
    },
    
    clickProducto(producto) {
      // Si es de categoría Pastas (id 1), mostrar selector de salsas
      if (producto.categoria_id === 1) {
        this.pastaSeleccionada = producto;
        this.mostrarModalSalsas = true;
      } 
      // Si es de categoría Promociones (id 3), iniciar flujo de promoción
      else if (producto.categoria_id === 3) {
        this.iniciarPromocion();
      } 
      else {
        // Para otros productos, agregar directamente
        this.agregarProducto(producto);
      }
    },
    
    cerrarModalSalsas() {
      this.mostrarModalSalsas = false;
      this.pastaSeleccionada = null;
    },
    
    seleccionarSalsa(salsa) {
      if (!this.pastaSeleccionada) return;
      
      // Crear producto compuesto: Pasta + Salsa con precio de la salsa
      const productoCompleto = {
        id: `${this.pastaSeleccionada.id}_${salsa.id}`,
        nombre: `${this.pastaSeleccionada.nombre} ${salsa.nombre}`,
        precio: salsa.precio,
        cantidad: 1
      };
      
      // Verificar si ya existe en el carrito
      const index = this.carrito.findIndex(item => 
        item.id === productoCompleto.id
      );
      
      if (index !== -1) {
        this.carrito[index].cantidad++;
      } else {
        this.carrito.push(productoCompleto);
      }
      
      this.cerrarModalSalsas();
    },
    
    // ==================== PROMOCIÓN MULTI-PASO ====================
    iniciarPromocion() {
      this.pasoPromocion = 1;
      this.promocion = {
        pasta: null,
        salsa: null,
        precioSalsa: 0,
        bebida: null,
        quesoExtra: false,
        salsaExtra: false,
        precioBase: 5990,
        precioQuesoExtra: 500,
        precioSalsaExtra: 700
      };
      this.mostrarModalPromocion = true;
    },
    
    cerrarModalPromocion() {
      this.mostrarModalPromocion = false;
      this.pasoPromocion = 1;
      this.mostrandoSelectorSalsaExtra = false;
      // Resetear promoción
      this.promocion = {
        pasta: null,
        salsa: null,
        precioSalsa: 0,
        bebida: null,
        quesoExtra: false,
        salsaExtra: false,
        nombreSalsaExtra: null,
        precioBase: 5990,
        precioQuesoExtra: 500,
        precioSalsaExtra: 700
      };
    },
    
    seleccionarPastaPromo(pasta) {
      this.promocion.pasta = pasta;
      this.pasoPromocion = 2;
    },
    
    seleccionarSalsaPromo(salsaNombre, salsaPrecio) {
      console.log('🍝 Salsa seleccionada:', salsaNombre, 'Precio:', salsaPrecio);
      this.promocion.salsa = salsaNombre;
      this.promocion.precioSalsa = parseFloat(salsaPrecio);
      this.pasoPromocion = 3;
    },
    
    seleccionarBebidaPromo(bebida) {
      this.promocion.bebida = bebida;
      this.pasoPromocion = 4;
    },
    
    mostrarSelectorSalsaExtra() {
      this.mostrandoSelectorSalsaExtra = true;
    },
    
    seleccionarSalsaExtra(salsa) {
      console.log('🌶️ Salsa extra seleccionada:', salsa.nombre);
      this.promocion.salsaExtra = true;
      this.promocion.nombreSalsaExtra = salsa.nombre;
      this.mostrandoSelectorSalsaExtra = false;
    },
    
    cancelarSalsaExtra() {
      this.promocion.salsaExtra = false;
      this.promocion.nombreSalsaExtra = null;
      this.mostrandoSelectorSalsaExtra = false;
    },
    
    mostrarSelectorSalsaExtra() {
      this.mostrandoSelectorSalsaExtra = true;
    },
    
    seleccionarSalsaExtra(salsa) {
      console.log('🌶️ Salsa extra seleccionada:', salsa.nombre);
      this.promocion.salsaExtra = true;
      this.promocion.nombreSalsaExtra = salsa.nombre;
      this.mostrandoSelectorSalsaExtra = false;
    },
    
    cancelarSalsaExtra() {
      this.promocion.salsaExtra = false;
      this.promocion.nombreSalsaExtra = null;
      this.mostrandoSelectorSalsaExtra = false;
    },
    
    finalizarPromocion() {
      // Calcular precio total: precio salsa + bebida (1000) + extras
      const precioSalsa = parseFloat(this.promocion.precioSalsa) || 4990;
      const precioBebida = 1000;
      
      console.log('💰 Calculando promoción:');
      console.log('   Salsa:', this.promocion.salsa, '=', precioSalsa);
      console.log('   Bebida: 1000');
      console.log('   Queso Extra:', this.promocion.quesoExtra ? 500 : 0);
      console.log('   Salsa Extra:', this.promocion.salsaExtra ? 700 : 0);
      
      let precioTotal = precioSalsa + precioBebida;
      
      if (this.promocion.quesoExtra) {
        precioTotal += this.promocion.precioQuesoExtra;
      }
      
      if (this.promocion.salsaExtra) {
        precioTotal += this.promocion.precioSalsaExtra;
      }
      
      console.log('   TOTAL:', precioTotal);
      
      // Crear descripción detallada
      let extras = [];
      if (this.promocion.quesoExtra) extras.push('Queso Extra');
      if (this.promocion.salsaExtra && this.promocion.nombreSalsaExtra) extras.push(`Salsa Extra (${this.promocion.nombreSalsaExtra})`);
      const extrasText = extras.length > 0 ? ` + ${extras.join(' + ')}` : '';
      
      const productoCompleto = {
        id: `promo_${Date.now()}`,
        nombre: `PROMO: ${this.promocion.pasta} ${this.promocion.salsa} + ${this.promocion.bebida}${extrasText}`,
        precio: precioTotal,
        cantidad: 1
      };
      
      console.log('📦 Producto a agregar:', productoCompleto);
      
      this.carrito.push(productoCompleto);
      
      console.log('🛒 Carrito actualizado:', this.carrito);
      
      this.cerrarModalPromocion();
    },
    
    agregarProducto(producto) {
      const index = this.carrito.findIndex(item => item.id === producto.id);
      
      if (index !== -1) {
        this.carrito[index].cantidad++;
      } else {
        this.carrito.push({
          id: producto.id,
          nombre: producto.nombre,
          precio: parseFloat(producto.precio),
          cantidad: 1
        });
      }
    },
    
    incrementarCantidad(index) {
      this.carrito[index].cantidad++;
    },
    
    decrementarCantidad(index) {
      if (this.carrito[index].cantidad > 1) {
        this.carrito[index].cantidad--;
      } else {
        this.eliminarDelCarrito(index);
      }
    },
    
    eliminarDelCarrito(index) {
      this.carrito.splice(index, 1);
    },
    
    limpiarCarrito() {
      if (confirm('¿Estás seguro que querés limpiar el carrito?')) {
        this.carrito = [];
      }
    },
    
    formatPrice(price) {
      return price ? parseFloat(price).toLocaleString('es-CL') : '0';
    },
    
    async procesarPago() {
      if (this.totalCarrito <= 0) {
        alert('Agregá productos para continuar');
        return;
      }

      this.isProcessing = true;

      try {
        console.log('💳 Procesando pago con Mercado Pago...', {
          total: this.totalCarrito,
          items: this.carrito
        });

        const orderData = {
          monto: Math.round(this.totalCarrito),
          productos: this.carrito.map(item => ({
            nombre: item.nombre,
            cantidad: item.cantidad,
            precio: item.precio,
            subtotal: item.precio * item.cantidad
          })),
          cliente: 'Cliente Totem',
          descripcion: 'Pedido desde Kiosko Digital'
        };

        console.log('📤 Enviando a Mercado Pago:', orderData);

        const response = await fetch('https://apimercado.posfagotto.cl/api-pago.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams(orderData)
        });

        const result = await response.json();
        console.log('✅ Respuesta del servidor:', result);
        
        if (result.success && result.orderId) {
          const orderId = result.orderId;
          
          this.$notify({
            type: 'success',
            title: '✅ ¡Pago enviado!',
            text: `Order ID: ${orderId}\n\n💳 Acercá tu tarjeta al Point Smart para completar el pago`
          });

          window.open(
            `https://apimercado.posfagotto.cl/validar-pago.php?order_id=${orderId}`,
            '_blank',
            'width=600,height=800'
          );

          this.carrito = [];
          
        } else {
          throw new Error(result.message || 'Error desconocido');
        }
        
      } catch (error) {
        console.error('❌ Error al procesar pago:', error);
        
        this.$bvToast.toast(error.message || 'No se pudo procesar el pago', {
          title: '❌ Error',
          variant: 'danger',
          solid: true
        });
      } finally {
        this.isProcessing = false;
      }
    }
  }
};
</script>
  },
  data() {
    return {
      products: [],
      totalPrice: 0,
      cartIcon: '🛒',
      isProcessing: false
    };
  },
  
  methods: {
    async openCatalog() {
      console.log('📱 Abriendo catálogo de productos...');
      $('#modalCatalog').modal('show');
    },

    updateProducts(newProducts) {
      this.products = newProducts;
      console.log('🔄 Productos actualizados:', this.products);
    },

    updateTotal(newTotal) {
      this.totalPrice = newTotal;
      console.log('💰 Total actualizado:', this.totalPrice);
    },

    clearCart() {
      if (confirm('¿Estás seguro que querés limpiar el carrito?')) {
        this.products = [];
        this.totalPrice = 0;
        this.$bvToast.toast('Se han eliminado todos los productos', {
          title: '🗑️ Carrito limpiado',
          variant: 'info',
          solid: true
        });
      }
    },
    
    formatPrice(price) {
      return price ? price.toLocaleString('es-CL') : '0';
    },
    
    async procesarPago() {
      if (this.totalPrice <= 0) {
        this.$notify({
          type: 'warning',
          title: '🛒 Carrito vacío',
          text: 'Agregá productos para continuar'
        });
        return;
      }

      this.isProcessing = true;
      this.cartIcon = '⏳';

      try {
        console.log('💳 Procesando pago con Mercado Pago...', {
          total: this.totalPrice,
          items: this.products
        });

        // Preparar datos del pedido
        const orderData = {
          monto: Math.round(this.totalPrice),
          productos: this.products.map(item => ({
            nombre: item.name,
            cantidad: item.quantity,
            precio: item.price,
            subtotal: item.price * item.quantity
          })),
          cliente: 'Cliente Totem',
          descripcion: 'Pedido desde Kiosko Digital'
        };

        console.log('📤 Enviando a Mercado Pago:', orderData);

        // Enviar pago a Mercado Pago - API JSON con CORS
        const response = await fetch('https://apimercado.posfagotto.cl/api-pago.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams(orderData)
        });

        const result = await response.json();
        console.log('✅ Respuesta del servidor:', result);
        
        if (result.success && result.orderId) {
          const orderId = result.orderId;
          
          this.$bvToast.toast(`Order ID: ${orderId}\n\n💳 Acercá tu tarjeta al Point Smart para completar el pago`, {
            title: '✅ ¡Pago enviado!',
            variant: 'success',
            solid: true
          });

          // Abrir ventana de validación
          window.open(
            `https://apimercado.posfagotto.cl/validar-pago.php?order_id=${orderId}`,
            '_blank',
            'width=1200,height=800'
          );

          // Limpiar carrito después de enviar el pago
          this.products = [];
          this.totalPrice = 0;
          
        } else if (html.includes('Ya hay un pago pendiente')) {
          throw new Error('Ya hay un pago pendiente en el terminal.\nCompletá o cancelá el pago actual primero.');
        } else {
          throw new Error('No se pudo crear el pago en el terminal.');
        }

      } catch (error) {
        console.error('❌ Error al procesar pago:', error);
        this.$bvToast.toast(error.message || 'No se pudo conectar con Mercado Pago. Verifica la conexión.', {
          title: '❌ Error en el pago',
          variant: 'danger',
          solid: true
        });
      } finally {
        this.isProcessing = false;
        this.cartIcon = '🛒';
      }
    }
  },
  
  mounted() {
    console.log('📱 Totem Kiosko cargado');
  }
};
</script>
<style scoped>
/* WRAPPER PRINCIPAL */
.totem-wrapper {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  overflow: hidden;
  z-index: 99999;
  background: white;
}

/* ==================== PANTALLA BIENVENIDA ==================== */
.pantalla-bienvenida {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #ffbc0d 0%, #ffa500 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  animation: fadeIn 0.5s ease;
}

.bienvenida-content {
  text-align: center;
  color: white;
}

.logo-fagotto {
  margin-bottom: 40px;
  animation: bounceIn 0.8s ease;
}

.logo-fagotto h1 {
  font-size: 6rem;
  margin: 0;
  text-shadow: 4px 4px 8px rgba(0,0,0,0.3);
  font-weight: bold;
}

.bienvenida-titulo {
  font-size: 3.5rem;
  margin: 0 0 20px 0;
  font-weight: bold;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}

.bienvenida-subtitulo {
  font-size: 1.8rem;
  margin: 0 0 50px 0;
  opacity: 0.95;
}

.btn-comenzar {
  background: white;
  color: #ffa500;
  border: none;
  padding: 25px 60px;
  font-size: 1.8rem;
  font-weight: bold;
  border-radius: 50px;
  cursor: pointer;
  box-shadow: 0 8px 20px rgba(0,0,0,0.2);
  transition: all 0.3s ease;
}

.btn-comenzar:hover {
  transform: translateY(-5px) scale(1.05);
  box-shadow: 0 12px 30px rgba(0,0,0,0.3);
}

.btn-comenzar i {
  margin-right: 15px;
  font-size: 2rem;
}

/* ==================== PANTALLA SELECCIÓN ==================== */
.pantalla-seleccion {
  width: 100%;
  height: 100%;
  background: linear-gradient(180deg, #f5f5f5 0%, #e0e0e0 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  animation: fadeIn 0.5s ease;
}

.seleccion-content {
  text-align: center;
  max-width: 1200px;
  padding: 40px;
}

.seleccion-titulo {
  font-size: 3.5rem;
  color: #333;
  margin: 0 0 60px 0;
  font-weight: bold;
}

.seleccion-opciones {
  display: flex;
  gap: 50px;
  justify-content: center;
  margin-bottom: 60px;
}

.opcion-card {
  background: white;
  width: 350px;
  height: 400px;
  border-radius: 24px;
  padding: 40px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 8px 24px rgba(0,0,0,0.1);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.opcion-card:hover {
  transform: translateY(-10px) scale(1.05);
  box-shadow: 0 16px 40px rgba(0,0,0,0.2);
  background: linear-gradient(135deg, #ffbc0d 0%, #ffa500 100%);
  color: white;
}

.opcion-icon {
  width: 180px;
  height: 180px;
  background: linear-gradient(135deg, #ffbc0d 0%, #ffa500 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 30px;
  transition: all 0.3s ease;
}

.opcion-card:hover .opcion-icon {
  background: white;
  color: #ffa500;
}

.opcion-icon i {
  font-size: 5rem;
  color: white;
}

.opcion-card:hover .opcion-icon i {
  color: #ffa500;
}

.opcion-card h3 {
  font-size: 2.2rem;
  margin: 0;
  font-weight: bold;
  color: #333;
  transition: all 0.3s ease;
}

.opcion-card:hover h3 {
  color: white;
}

.btn-volver {
  background: #6c757d;
  color: white;
  border: none;
  padding: 18px 40px;
  font-size: 1.4rem;
  font-weight: 600;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.btn-volver:hover {
  background: #5a6268;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0,0,0,0.15);
}

.btn-volver i {
  margin-right: 10px;
}

/* ANIMACIONES */
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes bounceIn {
  0% {
    transform: scale(0.5);
    opacity: 0;
  }
  50% {
    transform: scale(1.1);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

/* ==================== MODAL SELECTOR DE SALSAS ==================== */
.modal-salsas-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  animation: fadeIn 0.3s ease;
}

.modal-salsas-content {
  background: white;
  border-radius: 24px;
  padding: 40px;
  max-width: 900px;
  width: 90%;
  max-height: 85vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.4s ease;
}

.modal-salsas-header {
  text-align: center;
  margin-bottom: 40px;
  position: relative;
}

.modal-salsas-header h2 {
  font-size: 2.5rem;
  color: #dc3545;
  margin: 0 0 10px 0;
  font-weight: bold;
}

.modal-salsas-header p {
  font-size: 1.4rem;
  color: #666;
  margin: 0;
}

.btn-cerrar-modal {
  position: absolute;
  top: -10px;
  right: -10px;
  background: #dc3545;
  color: white;
  border: none;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  font-size: 1.5rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.btn-cerrar-modal:hover {
  background: #c82333;
  transform: rotate(90deg) scale(1.1);
}

.salsas-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

.salsa-card {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 30px 20px;
  border-radius: 16px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 3px solid transparent;
}

.salsa-card:hover {
  transform: translateY(-8px);
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  color: white;
  border-color: #ffc107;
  box-shadow: 0 12px 30px rgba(220, 53, 69, 0.3);
}

.salsa-icon {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
  transition: all 0.3s ease;
}

.salsa-card:hover .salsa-icon {
  background: white;
  transform: scale(1.1);
}

.salsa-icon i {
  font-size: 2.5rem;
  color: white;
}

.salsa-card:hover .salsa-icon i {
  color: #dc3545;
}

.salsa-card h3 {
  font-size: 1.3rem;
  font-weight: bold;
  margin: 0 0 10px 0;
  color: #333;
  transition: all 0.3s ease;
}

.salsa-card:hover h3 {
  color: white;
}

.salsa-precio {
  font-size: 1.5rem;
  font-weight: bold;
  color: #dc3545;
  transition: all 0.3s ease;
}

.salsa-card:hover .salsa-precio {
  color: white;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(50px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* ==================== MODAL PROMOCIÓN MULTI-PASO ==================== */
.modal-promo-content {
  background: white;
  border-radius: 24px;
  padding: 50px;
  max-width: 1000px;
  width: 95%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.4s ease;
}

.paso-promo {
  min-height: 400px;
}

.promo-header {
  text-align: center;
  margin-bottom: 40px;
}

.promo-header h2 {
  font-size: 2.5rem;
  color: #dc3545;
  margin: 0 0 10px 0;
  font-weight: bold;
}

.paso-indicador {
  font-size: 1.2rem;
  color: #999;
  margin: 10px 0;
}

.seleccion-previa {
  font-size: 1.3rem;
  color: #666;
  background: #f8f9fa;
  padding: 15px;
  border-radius: 10px;
  margin-top: 15px;
  font-weight: 600;
}

.opciones-grid-2 {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 25px;
  max-width: 700px;
  margin: 0 auto;
}

.opcion-promo-card {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 50px 30px;
  border-radius: 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 3px solid transparent;
}

.opcion-promo-card:hover {
  transform: translateY(-10px) scale(1.05);
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  color: white;
  border-color: #ffc107;
  box-shadow: 0 15px 40px rgba(220, 53, 69, 0.4);
}

.opcion-promo-card i {
  font-size: 4rem;
  margin-bottom: 20px;
  display: block;
  color: #dc3545;
  transition: all 0.3s ease;
}

.opcion-promo-card:hover i {
  color: white;
}

.opcion-promo-card h3 {
  font-size: 1.8rem;
  font-weight: bold;
  margin: 0;
  color: #333;
  transition: all 0.3s ease;
}

.opcion-promo-card:hover h3 {
  color: white;
}

.salsa-card-promo {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 25px 15px;
  border-radius: 16px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 3px solid transparent;
}

.salsa-card-promo:hover {
  transform: translateY(-8px);
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  color: white;
  border-color: #ffc107;
  box-shadow: 0 12px 30px rgba(220, 53, 69, 0.3);
}

.salsa-card-promo .salsa-icon {
  width: 70px;
  height: 70px;
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 15px;
  transition: all 0.3s ease;
}

.salsa-card-promo:hover .salsa-icon {
  background: white;
  transform: scale(1.1);
}

.salsa-card-promo .salsa-icon i {
  font-size: 2rem;
  color: white;
}

.salsa-card-promo:hover .salsa-icon i {
  color: #dc3545;
}

.salsa-card-promo h3 {
  font-size: 1.2rem;
  font-weight: bold;
  margin: 0;
  color: #333;
  transition: all 0.3s ease;
}

.salsa-card-promo:hover h3 {
  color: white;
}

.extras-container {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 25px;
  max-width: 700px;
  margin: 0 auto 30px;
}

.extra-card {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 40px 25px;
  border-radius: 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 3px solid transparent;
  position: relative;
}

.extra-card:hover {
  transform: translateY(-8px);
  border-color: #dc3545;
  box-shadow: 0 12px 30px rgba(220, 53, 69, 0.2);
}

.extra-card.active {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  color: white;
  border-color: #ffc107;
  transform: scale(1.05);
}

.extra-icon {
  width: 80px;
  height: 80px;
  background: #dc3545;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
  transition: all 0.3s ease;
}

.extra-card.active .extra-icon {
  background: white;
}

.extra-icon i {
  font-size: 2.5rem;
  color: white;
}

.extra-card.active .extra-icon i {
  color: #28a745;
}

.extra-card h3 {
  font-size: 1.5rem;
  font-weight: bold;
  margin: 0 0 10px 0;
  color: #333;
  transition: all 0.3s ease;
}

.extra-card.active h3 {
  color: white;
}

.extra-precio {
  font-size: 1.3rem;
  font-weight: bold;
  color: #dc3545;
  margin: 0;
  transition: all 0.3s ease;
}

.extra-card.active .extra-precio {
  color: white;
}

.extra-check {
  position: absolute;
  top: 15px;
  right: 15px;
  width: 40px;
  height: 40px;
  background: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.extra-check i {
  font-size: 1.5rem;
  color: #28a745;
}

.promo-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8f9fa;
  padding: 25px 30px;
  border-radius: 15px;
  margin: 30px 0;
  font-size: 1.8rem;
}

.promo-total strong {
  color: #dc3545;
  font-size: 2.2rem;
}

.btn-finalizar-promo {
  width: 100%;
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  color: white;
  border: none;
  padding: 25px;
  border-radius: 15px;
  font-size: 1.6rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
}

.btn-finalizar-promo:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 30px rgba(40, 167, 69, 0.5);
}

.btn-finalizar-promo i {
  margin-right: 15px;
  font-size: 1.8rem;
}

/* ==================== SELECTOR SALSA EXTRA ==================== */
.selector-salsa-extra-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.95);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  border-radius: 40px;
}

.selector-salsa-extra-content {
  background: white;
  padding: 40px;
  border-radius: 30px;
  max-width: 800px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
}

.selector-salsa-extra-content h3 {
  font-size: 2.5rem;
  color: #dc3545;
  margin-bottom: 30px;
  text-align: center;
}

.salsas-grid-extra {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-bottom: 30px;
}

.salsa-extra-card {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 30px;
  border-radius: 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 3px solid transparent;
}

.salsa-extra-card:hover {
  transform: translateY(-10px);
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  border-color: #dc3545;
  box-shadow: 0 10px 30px rgba(220, 53, 69, 0.4);
}

.salsa-extra-card:hover i {
  color: white;
  transform: scale(1.3) rotate(15deg);
}

.salsa-extra-card:hover h4 {
  color: white;
}

.salsa-extra-card i {
  font-size: 3rem;
  color: #dc3545;
  margin-bottom: 15px;
  transition: all 0.3s ease;
}

.salsa-extra-card h4 {
  font-size: 1.4rem;
  color: #333;
  font-weight: bold;
  margin: 0;
  transition: all 0.3s ease;
}

.btn-cancelar-salsa {
  width: 100%;
  padding: 18px;
  background: #6c757d;
  color: white;
  border: none;
  border-radius: 15px;
  font-size: 1.3rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-cancelar-salsa:hover {
  background: #5a6268;
  transform: translateY(-3px);
}

.salsa-elegida {
  font-size: 0.9rem;
  color: #28a745;
  font-weight: bold;
  margin-top: 5px;
}

/* ==================== LAYOUT MENÚ PRINCIPAL ==================== */
/* LAYOUT PRINCIPAL */
.mcd-container {
  display: flex;
  height: 100vh;
  width: 100vw;
  background: #f5f5f5;
  overflow: hidden;
}

/* SIDEBAR CATEGORÍAS */
.mcd-sidebar {
  width: 280px;
  background: linear-gradient(180deg, #ffbc0d 0%, #ffa500 100%);
  padding: 20px;
  overflow-y: auto;
  box-shadow: 2px 0 10px rgba(0,0,0,0.1);
  flex-shrink: 0;
}

.mcd-logo {
  text-align: center;
  padding: 20px 0;
  border-bottom: 2px solid rgba(255,255,255,0.3);
  margin-bottom: 20px;
}

.mcd-logo h2 {
  color: white;
  font-size: 2rem;
  font-weight: bold;
  margin: 0;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}

.mcd-categories {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.mcd-category-btn {
  background: white;
  border: none;
  padding: 18px 20px;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  color: #333;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.mcd-category-btn i {
  font-size: 1.5rem;
}

.mcd-category-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  background: #fff8e1;
}

.mcd-category-btn.active {
  background: #dc3545;
  color: white;
  transform: scale(1.05);
}

/* MAIN PRODUCTOS */
.mcd-main {
  flex: 1;
  overflow-y: auto;
  padding: 30px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.mcd-header {
  margin-bottom: 30px;
  text-align: center;
  width: 100%;
}

.mcd-header h1 {
  font-size: 2.5rem;
  font-weight: bold;
  color: #333;
  margin: 0 0 10px 0;
}

.mcd-header p {
  font-size: 1.3rem;
  color: #666;
  margin: 0;
}

.mcd-products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 30px;
  padding-bottom: 20px;
  width: 100%;
  max-width: 1400px;
  justify-items: center;
}

.mcd-product-card {
  background: white;
  border-radius: 20px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  width: 100%;
  max-width: 350px;
}

.mcd-product-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 12px 30px rgba(0,0,0,0.15);
}

.mcd-product-image {
  width: 100%;
  height: 220px;
  overflow: hidden;
  background: #f8f8f8;
}

.mcd-product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.mcd-product-info {
  padding: 25px;
}

.mcd-product-info h3 {
  font-size: 1.4rem;
  font-weight: bold;
  color: #333;
  margin: 0 0 10px 0;
}

.mcd-product-info p {
  font-size: 1rem;
  color: #666;
  margin: 0 0 15px 0;
  line-height: 1.5;
}

.mcd-product-price {
  font-size: 1.8rem;
  font-weight: bold;
  color: #dc3545;
}

.mcd-no-products {
  grid-column: 1 / -1;
  text-align: center;
  padding: 60px 20px;
  color: #999;
}

.mcd-no-products i {
  font-size: 4rem;
  margin-bottom: 20px;
  display: block;
}

.mcd-no-products p {
  font-size: 1.3rem;
}

/* CART PANEL */
.mcd-cart-panel {
  width: 350px;
  background: white;
  display: flex;
  flex-direction: column;
  box-shadow: -2px 0 10px rgba(0,0,0,0.1);
}

.mcd-cart-header {
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  color: white;
  padding: 25px;
}

.mcd-cart-header h3 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: bold;
}

.mcd-cart-items {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
}

.mcd-cart-empty {
  text-align: center;
  padding: 40px 20px;
  color: #999;
}

.mcd-cart-empty i {
  font-size: 3rem;
  margin-bottom: 15px;
  display: block;
}

.mcd-cart-item {
  background: #f8f9fa;
  padding: 15px;
  border-radius: 10px;
  margin-bottom: 12px;
}

.mcd-cart-item-info strong {
  display: block;
  font-size: 1.1rem;
  color: #333;
  margin-bottom: 10px;
}

.mcd-cart-item-controls {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-top: 8px;
}

.mcd-btn-qty {
  background: #dc3545;
  color: white;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.mcd-btn-qty:hover {
  background: #c82333;
  transform: scale(1.1);
}

.mcd-qty {
  font-size: 1.2rem;
  font-weight: bold;
  min-width: 30px;
  text-align: center;
}

.mcd-cart-item-price {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 8px;
  font-size: 1.2rem;
  font-weight: bold;
  color: #dc3545;
}

.mcd-btn-remove {
  background: #6c757d;
  color: white;
  border: none;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 0.8rem;
  transition: all 0.2s;
}

.mcd-btn-remove:hover {
  background: #dc3545;
  transform: scale(1.1);
}

.mcd-cart-footer {
  padding: 20px;
  border-top: 2px solid #e9ecef;
  background: #f8f9fa;
}

.mcd-cart-total {
  display: flex;
  justify-content: space-between;
  font-size: 1.5rem;
  margin-bottom: 20px;
  padding: 15px;
  background: white;
  border-radius: 10px;
}

.mcd-cart-total strong {
  color: #dc3545;
  font-size: 1.8rem;
}

.mcd-btn-pay {
  width: 100%;
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  color: white;
  border: none;
  padding: 18px;
  border-radius: 12px;
  font-size: 1.2rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
  margin-bottom: 10px;
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.mcd-btn-pay:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

.mcd-btn-pay:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.mcd-btn-clear {
  width: 100%;
  background: #6c757d;
  color: white;
  border: none;
  padding: 15px;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.mcd-btn-clear:hover:not(:disabled) {
  background: #5a6268;
  transform: translateY(-2px);
}

.mcd-btn-clear:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* SCROLLBAR */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
  border: none;
  padding: 2rem 4rem;
  border-radius: 20px;
  font-size: 1.5rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 8px 24px rgba(40, 167, 69, 0.3);
}

.btn-open-catalog:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 32px rgba(40, 167, 69, 0.4);
}

.order-summary-card {
  background: white;
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 4px 16px rgba(0,0,0,0.1);
  max-width: 800px;
  margin: 2rem auto;
}

.order-summary-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2c2c2c;
  margin-bottom: 1.5rem;
  border-bottom: 3px solid #ffbc0d;
  padding-bottom: 0.75rem;
}

.order-items {
  margin-bottom: 1.5rem;
}

.order-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #e8e8e8;
}

.order-item:last-child {
  border-bottom: none;
}

.order-item-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.order-item-name {
  font-weight: 600;
  color: #2c2c2c;
}

.order-item-qty {
  background: #f5f5f5;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.9rem;
  color: #666;
}

.order-item-price {
  font-weight: 700;
  color: #ffbc0d;
  font-size: 1.1rem;
}

.order-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 1rem;
  background: linear-gradient(135deg, #f8f9fa, #e8e9ea);
  border-radius: 12px;
  font-size: 1.3rem;
  font-weight: 700;
}

.order-total-amount {
  color: #28a745;
  font-size: 1.8rem;
}

.empty-cart-message {
  text-align: center;
  padding: 4rem 2rem;
  color: #999;
}

.empty-icon {
  font-size: 5rem;
  margin-bottom: 1rem;
  opacity: 0.3;
}

.footer-cart {
  background: white;
  padding: 1.5rem 3rem;
  box-shadow: 0 -4px 16px rgba(0,0,0,0.15);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.cart-info {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.cart-icon {
  width: 70px;
  height: 70px;
  background: linear-gradient(135deg, #ffbc0d, #ffa500);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: white;
  box-shadow: 0 4px 12px rgba(255, 188, 13, 0.3);
}

.cart-total {
  font-size: 2.2rem;
  font-weight: 800;
  color: #2c2c2c;
}

.footer-actions {
  display: flex;
  gap: 1rem;
}

.btn-action {
  padding: 1.2rem 2.5rem;
  border: none;
  border-radius: 50px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.btn-action:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

.btn-continue {
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
}

.btn-continue:hover:not(:disabled) {
  transform: translateY(-3px);
  box-shadow: 0 6px 18px rgba(40, 167, 69, 0.4);
}

.btn-clear {
  background: linear-gradient(135deg, #dc3545, #c82333);
  color: white;
}

.btn-clear:hover:not(:disabled) {
  transform: translateY(-3px);
  box-shadow: 0 6px 18px rgba(220, 53, 69, 0.4);
}

::-webkit-scrollbar {
  width: 10px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
  background: #ffbc0d;
  border-radius: 5px;
}

::-webkit-scrollbar-thumb:hover {
  background: #ffa500;
}
</style>
