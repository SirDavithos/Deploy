<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user || null);
const userRoles = computed(() => page.props.auth?.userRoles || []);
const userShop = computed(() => page.props.auth?.userShop || null);
const cartCount = computed(() => page.props.auth?.cartCount || 0);
const cartItems = computed(() => page.props.auth?.cartItems || []);

// Avatar o iniciales
const userAvatar = computed(() => user.value?.avatar ? `/storage/${user.value.avatar}` : null);
const userInitials = computed(() => {
    if (!user.value) return '';
    return (user.value.first_name?.charAt(0) || '') + (user.value.paternal_last_name?.charAt(0) || '');
});

// Buscador
const searchQuery = ref('');
const handleSearch = () => {
    if (searchQuery.value.trim() !== '') {
        router.get(route('products.index'), { search: searchQuery.value });
    }
};

// Dropdown del carrito
const showCartDropdown = ref(false);

const toggleCartDropdown = () => {
    showCartDropdown.value = !showCartDropdown.value;
};

const hideCartDropdown = () => {
    showCartDropdown.value = false;
};
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-950 flex flex-col">
    <!-- HEADER ESTILO ETSY -->
    <header class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Fila superior: logo, buscador, iconos -->
        <div class="flex items-center justify-between py-3 gap-4">
          <!-- Logo -->
          <Link :href="route('home')" class="flex items-center flex-shrink-0">
            <span class="text-2xl font-black tracking-wider text-red-600 dark:text-red-500">PUNTO</span>
            <span class="bg-red-600 px-2 py-0.5 text-sm font-bold text-white rounded ml-1 dark:bg-red-500">BOLIVIANO</span>
          </Link>

          <!-- Buscador -->
          <div class="flex-1 max-w-2xl hidden sm:block">
            <form @submit.prevent="handleSearch" class="relative">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Buscar artesanías, tiendas..."
                class="input-field py-2 pl-10 pr-4 text-sm rounded-full"
              />
              <button type="submit" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                </svg>
              </button>
            </form>
          </div>

          <!-- Iconos y menú de usuario -->
          <div class="flex items-center gap-3">
            <!-- Favoritos -->
            <Link :href="route('favorites.index')" class="btn-icon relative" title="Favoritos">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
              </svg>
            </Link>

            <!-- Notificaciones (placeholder) -->
            <button class="btn-icon relative" title="Notificaciones">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
              </svg>
              <span class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">3</span>
            </button>

            <!-- Carrito con dropdown -->
            <div class="relative" @mouseleave="hideCartDropdown">
              <button @click="toggleCartDropdown" class="btn-icon relative" title="Carrito">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
                <span v-if="cartCount > 0" class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">{{ cartCount }}</span>
              </button>

              <!-- Dropdown del carrito -->
              <div v-if="showCartDropdown" class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-900 rounded-lg shadow-lg ring-1 ring-black/5 z-50 p-4">
                <div v-if="cartItems.length > 0" class="space-y-3">
                  <div v-for="item in cartItems.slice(0, 3)" :key="item.id" class="flex items-center gap-3">
                    <img v-if="item.product.images?.length" :src="'/storage/' + item.product.images[0]" class="h-12 w-12 object-cover rounded" />
                    <div v-else class="h-12 w-12 bg-gray-100 dark:bg-gray-800 rounded flex items-center justify-center text-xl">📦</div>
                    <div class="flex-1 min-w-0">
                      <p class="text-xs font-medium truncate">{{ item.product.title }}</p>
                      <p class="text-xs text-gray-500">{{ item.quantity }} x {{ item.product.price }} BOB</p>
                    </div>
                  </div>
                  <Link :href="route('cart.index')" class="btn-primary text-xs w-full py-2 text-center block" @click="showCartDropdown = false">
                    Ver carrito completo
                  </Link>
                </div>
                <div v-else class="text-center text-sm text-gray-500 py-4">
                  Tu carrito está vacío
                </div>
              </div>
            </div>

            <!-- Botones según rol (visibles) -->
            <template v-if="userRoles?.includes('artisan')">
              <Link v-if="userShop" :href="route('shop.show', userShop.id)" class="btn-outline text-xs px-3 py-1.5">
                🏪 Mi Tienda
              </Link>
              <Link v-else :href="route('shop.create')" class="btn-outline text-xs px-3 py-1.5">
                🏪 Abrir Tienda
              </Link>
            </template>

            <!-- Visitante -->
            <template v-if="!user">
              <Link :href="route('login')" class="btn-outline text-xs px-3 py-1.5">Iniciar sesión</Link>
              <Link :href="route('register')" class="btn-primary text-xs px-3 py-1.5">Registrarse</Link>
            </template>

            <!-- Usuario logueado: avatar + dropdown -->
            <div v-if="user" class="dropdown">
              <button class="flex items-center gap-2 focus:outline-none">
                <img v-if="userAvatar" :src="userAvatar" class="h-8 w-8 rounded-full object-cover border border-gray-200 dark:border-gray-700" />
                <div v-else class="h-8 w-8 rounded-full bg-red-100 dark:bg-red-900 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-xs font-bold text-red-800 dark:text-red-200">
                  {{ userInitials }}
                </div>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400 hidden sm:inline">{{ user.first_name }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
              </button>

              <!-- Menú desplegable -->
              <div class="dropdown-content">
                <Link :href="route('dashboard')" class="dropdown-item flex items-center gap-2">
                  <span>📋</span> Mi Panel
                </Link>
                <template v-if="userRoles?.includes('artisan')">
                  <Link v-if="userShop" :href="route('shop.show', userShop.id)" class="dropdown-item flex items-center gap-2">
                    <span>🏪</span> Mi Tienda
                  </Link>
                  <Link v-else :href="route('shop.create')" class="dropdown-item flex items-center gap-2">
                    <span>🏪</span> Abrir Tienda
                  </Link>
                </template>
                <Link v-if="userRoles?.includes('admin')" :href="route('admin.users.index')" class="dropdown-item flex items-center gap-2">
                  <span>⚙️</span> Panel Admin
                </Link>
                <Link :href="route('cart.index')" class="dropdown-item flex items-center gap-2">
                  <span>🛒</span> Carrito
                </Link>
                <Link :href="route('orders.index')" class="dropdown-item flex items-center gap-2">
                  <span>📦</span> Mis Pedidos
                </Link>
                <Link :href="route('favorites.index')" class="dropdown-item flex items-center gap-2">
                  <span>❤️</span> Favoritos
                </Link>
                <Link :href="route('profile.edit')" class="dropdown-item flex items-center gap-2">
                  <span>👤</span> Editar Perfil
                </Link>
                <div class="border-t border-gray-200 dark:border-gray-700"></div>
                <Link :href="route('logout')" method="post" as="button" class="dropdown-item w-full text-left text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                  Cerrar Sesión
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- Barra de categorías -->
        <nav class="flex items-center gap-4 py-2 overflow-x-auto text-nowrap border-t border-gray-100 dark:border-gray-800">
          <Link :href="route('products.index')" class="nav-link">Descubrir</Link>
          <Link :href="route('products.index', { category: 'textiles-aguayos' })" class="nav-link">Textiles</Link>
          <Link :href="route('products.index', { category: 'joyeria-plata' })" class="nav-link">Joyería</Link>
          <Link :href="route('products.index', { category: 'ceramica-arte' })" class="nav-link">Cerámica</Link>
          <Link :href="route('products.index', { category: 'cuero-artesanal' })" class="nav-link">Cuero</Link>
          <Link :href="route('products.index', { category: 'tallados-madera' })" class="nav-link">Madera</Link>
          <Link :href="route('products.index', { category: 'manualidades' })" class="nav-link">Manualidades</Link>
          <Link :href="route('products.index', { category: 'velas-aromas' })" class="nav-link">Velas</Link>
        </nav>
      </div>
    </header>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="flex-1">
      <slot />
    </main>

    <!-- FOOTER MEJORADO -->
    <footer class="border-t border-gray-200 bg-white dark:bg-gray-900 dark:border-gray-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="footer-grid">
          <div>
            <h3 class="footer-title">Punto Boliviano</h3>
            <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">
              Mercado de artesanías paceñas. Conectamos artesanos con compradores de todo el mundo.
            </p>
            <div class="flex items-center space-x-2">
              <span class="text-2xl font-black tracking-wider text-red-600 dark:text-red-500">PUNTO</span>
              <span class="bg-red-600 px-2 py-0.5 text-sm font-bold text-white rounded dark:bg-red-500">BOLIVIANO</span>
            </div>
          </div>

          <div>
            <h3 class="footer-title">Explorar</h3>
            <Link :href="route('products.index')" class="footer-link">Mercado</Link>
            <Link :href="route('products.index', { category: 'textiles-aguayos' })" class="footer-link">Textiles</Link>
            <Link :href="route('products.index', { category: 'joyeria-plata' })" class="footer-link">Joyería</Link>
            <Link :href="route('products.index', { category: 'ceramica-arte' })" class="footer-link">Cerámica</Link>
            <Link :href="route('products.index', { category: 'cuero-artesanal' })" class="footer-link">Cuero</Link>
            <Link :href="route('products.index', { category: 'tallados-madera' })" class="footer-link">Madera</Link>
          </div>

          <div>
            <h3 class="footer-title">Vender</h3>
            <Link :href="route('shop.create')" class="footer-link">Abrir una tienda</Link>
            <Link :href="route('register')" class="footer-link">Registrarse como artesano</Link>
            <a href="#" class="footer-link">Cómo funciona</a>
            <a href="#" class="footer-link">Tarifas</a>
          </div>

          <div>
            <h3 class="footer-title">Ayuda</h3>
            <a href="#" class="footer-link">Centro de ayuda</a>
            <a href="#" class="footer-link">Contacto</a>
            <a href="#" class="footer-link">Términos y condiciones</a>
            <a href="#" class="footer-link">Privacidad</a>
          </div>
        </div>

        <div class="border-t border-gray-100 dark:border-gray-800 pt-8 mt-8 flex flex-col sm:flex-row justify-between items-center gap-4">
          <p class="text-xs text-gray-500 dark:text-gray-400">
            &copy; 2026 Punto Boliviano. Hecho con ❤️ para la comunidad artesanal de La Paz, Bolivia.
          </p>
          <div class="flex gap-4">
            <a href="#" class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Facebook</a>
            <a href="#" class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Instagram</a>
            <a href="#" class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">WhatsApp</a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>