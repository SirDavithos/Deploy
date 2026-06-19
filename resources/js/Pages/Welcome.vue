<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    categories: Array,           // Datos reales de la tabla categories
    featuredProducts: Array,     // Productos reales (máximo 4)
});

const searchQuery = ref('');

// Mapa de emojis por slug de categoría para mantener el diseño visual
const categoryIcons = {
    'textiles-aguayos': '🧶',
    'joyeria-plata': '✨',
    'ceramica-arte': '🏺',
    'cuero-artesanal': '🎒',
    'instrumentos-nativos': '🪕',
    'tallados-madera': '🪵',
};

// Enriquece las categorías con icono y texto del contador
const enrichedCategories = computed(() => {
    return props.categories.map(cat => ({
        ...cat,
        icon: categoryIcons[cat.slug] || '📦',
        count: `${cat.products_count} productos`,
    }));
});

// Redirige al mercado público con el texto de búsqueda
const handleSearch = () => {
    if (searchQuery.value.trim() !== '') {
        router.get(route('products.index'), { search: searchQuery.value });
    }
};

// Redirige al mercado público filtrando por categoría
const searchByCategory = (categorySlug) => {
    router.get(route('products.index'), { category: categorySlug });
};

// Agregar producto al carrito real
const addToCart = (productId) => {
    const form = useForm({ product_id: productId, quantity: 1 });
    form.post(route('cart.store'), {
        preserveScroll: true,
        onSuccess: () => {
            alert('🛒 ¡Producto añadido al carrito!');
        },
    });
};

// Función helper para rutas (todas existen ahora, pero mantenemos safeRoute por seguridad)
const safeRoute = (name) => {
    try { return route(name); } catch (e) { return '#'; }
};
</script>

<template>
    <Head title="Punto Boliviano - El Corazón del Arte Artesanal" />

    <div class="min-h-screen bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100 transition-colors duration-200">
        <nav class="border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-800 dark:bg-gray-900 sticky top-0 z-50 shadow-sm">
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-2xl font-black tracking-wider text-red-600 dark:text-red-500">PUNTO</span>
                    <span class="bg-red-600 px-2 py-0.5 text-sm font-bold text-white rounded dark:bg-red-500">BOLIVIANO</span>
                </div>

                <div class="flex items-center space-x-4">
                    <!-- Enlace de administración, visible solo para administradores -->
                    <Link v-if="$page.props.auth.user?.roles?.includes('admin')" :href="route('admin.users.index')" class="text-sm text-gray-700 dark:text-gray-300">
                        Admin
                    </Link>
                    
                    <!-- Enlace al catálogo público real -->
                    <Link :href="route('products.index')" class="text-xs font-bold text-gray-600 dark:text-gray-400 hover:text-red-600">
                        Ver Catálogo
                    </Link>
                    
                    <div v-if="canLogin" class="flex items-center space-x-3">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="safeRoute('dashboard')"
                            class="rounded-xl bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-red-500"
                        >
                            Ir al Panel
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="text-xs font-bold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                            >
                                Iniciar Sesión
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="rounded-xl bg-gray-950 px-4 py-2 text-xs font-bold text-white shadow-sm hover:opacity-90 dark:bg-white dark:text-gray-950"
                            >
                                Registrarse
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <header class="relative overflow-hidden bg-gradient-to-b from-red-50 to-white px-6 py-20 text-center dark:from-gray-900 dark:to-gray-950">
            <div class="mx-auto max-w-3xl">
                <span class="inline-flex items-center rounded-md bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700 ring-1 ring-inset ring-red-700/10 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/30 mb-4 uppercase tracking-wider">
                    Mercado del Pasaje Marina Núñez del Prado & Pérez Velasco
                </span>
                <h1 class="text-4xl font-black tracking-tight text-gray-900 dark:text-white sm:text-6xl">
                    Artesanías auténticas hechas por manos bolivianas
                </h1>
                <p class="mt-6 text-base leading-8 text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    Compra directamente a los artesanos locales de La Paz. Apoya el comercio justo y lleva la cultura viva andina y amazónica a tu hogar de forma directa.
                </p>

                <form @submit.prevent="handleSearch" class="mt-10 flex max-w-xl mx-auto items-center gap-x-2 rounded-2xl bg-white p-2 shadow-md border border-gray-200 dark:bg-gray-900 dark:border-gray-800">
                    <div class="flex-1 pl-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-gray-400 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                        </svg>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Buscar por aguayos, platería, artesanos o categorías..."
                            class="w-full border-0 bg-transparent p-0 text-gray-950 placeholder:text-gray-400 focus:ring-0 text-xs dark:text-white"
                        />
                    </div>
                    <button type="submit" class="rounded-xl bg-red-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-red-500">
                        Buscar Ahora
                    </button>
                </form>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-6 py-12 space-y-24">
            <!-- Categorías (datos reales) -->
            <section>
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-black uppercase tracking-wider text-gray-900 dark:text-white">Explora las Categorías</h2>
                    <Link :href="route('products.index')" class="text-xs font-bold text-red-600 hover:text-red-500 dark:text-red-400">Ver todos los filtros &rarr;</Link>
                </div>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                    <div
                        v-for="category in enrichedCategories"
                        :key="category.id"
                        @click="searchByCategory(category.slug)"
                        class="group relative flex flex-col items-center justify-center rounded-2xl bg-white border border-gray-200 p-5 text-center shadow-sm hover:shadow-md dark:bg-gray-900 dark:border-gray-800 cursor-pointer transition-all duration-200 hover:-translate-y-1"
                    >
                        <span class="text-4xl mb-3 block group-hover:scale-110 transition-transform duration-200" aria-hidden="true">{{ category.icon }}</span>
                        <h3 class="text-xs font-bold text-gray-900 dark:text-white group-hover:text-red-600 transition-colors">{{ category.name }}</h3>
                        <p class="mt-1 text-[10px] text-gray-400 font-semibold">{{ category.count }}</p>
                    </div>
                </div>
            </section>

            <!-- Productos destacados (datos reales) -->
            <section class="border-t border-gray-100 dark:border-gray-900 pt-16">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-wider text-gray-900 dark:text-white">Galería de Muestra</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Obras maestras disponibles esta semana en la hoyada paceña.</p>
                    </div>
                    <Link :href="route('products.index')" class="text-xs font-bold text-red-600 hover:text-red-500 dark:text-red-400">Explorar Catálogo Completo &rarr;</Link>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="product in featuredProducts" :key="product.id" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 flex flex-col justify-between shadow-sm hover:shadow-md transition-shadow">
                        <div>
                            <!-- Imagen del producto -->
                            <div class="w-full aspect-square bg-gray-50 dark:bg-gray-950 rounded-xl flex items-center justify-center mb-4 shadow-inner overflow-hidden">
                                <img v-if="product.images && product.images.length" :src="'/storage/' + product.images[0]" class="w-full h-full object-cover" />
                                <span v-else class="text-6xl">📦</span>
                            </div>
                            <div class="space-y-1">
                                <h4 class="text-xs font-bold text-gray-900 dark:text-white line-clamp-1">{{ product.title }}</h4>
                                <p class="text-[11px] text-gray-400">
                                    Por: <span class="font-medium text-gray-500 dark:text-gray-300">{{ product.shop?.name }}</span>
                                </p>
                                <p class="text-[10px] text-gray-400">
                                    📍 {{ product.shop?.city || 'La Paz' }}, {{ product.shop?.zone || '' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-3 mt-4 border-t border-gray-50 dark:border-gray-800">
                            <span class="text-sm font-black text-red-600 dark:text-red-500">{{ product.price }} BOB</span>
                            <div class="flex gap-1.5">
                                <Link :href="route('products.show', product.id)" class="bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 font-bold px-2.5 py-1.5 rounded-lg text-[10px] hover:bg-gray-200">
                                    Detalle
                                </Link>
                                <button @click="addToCart(product.id)" class="bg-gray-900 text-white dark:bg-white dark:text-gray-900 font-bold px-2.5 py-1.5 rounded-lg text-[10px] hover:opacity-90">
                                    Adquirir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Sección para artesanos -->
            <section class="rounded-3xl bg-red-700 px-6 py-12 text-center text-white dark:bg-red-900 shadow-xl relative overflow-hidden">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-red-600 rounded-full blur-2xl opacity-50"></div>
                <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-red-500 rounded-full blur-2xl opacity-50"></div>
                
                <div class="relative z-10 max-w-2xl mx-auto">
                    <h2 class="text-3xl font-black tracking-tight sm:text-4xl">¿Eres artesano en La Paz?</h2>
                    <p class="mx-auto mt-4 max-w-xl text-sm text-red-100 leading-relaxed">
                        Abre tu tienda digital en Punto Boliviano. Sube tus artesanías, define tus precios en BOB y coordina entregas directas por WhatsApp o recojos físicos desde tu propio puesto en el pasaje.
                    </p>
                    <div class="mt-8 flex justify-center gap-x-4">
                        <Link
                            :href="route('shop.create')"
                            class="rounded-xl bg-white px-5 py-3 text-xs font-bold text-red-700 shadow-sm hover:bg-red-50"
                        >
                            Comenzar a Vender Digitalmente
                        </Link>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-gray-200 bg-white py-8 px-6 text-center text-xs text-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
            <p>&copy; 2026 Punto Boliviano. Hecho con ❤️ para la comunidad artesanal de La Paz, Bolivia.</p>
        </footer>
    </div>
</template>