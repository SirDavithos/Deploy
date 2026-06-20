<script setup>
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    categories: Array,
    featuredProducts: Array,
    bestSellers: Array,
    topRated: Array,
    newArrivals: Array,
});

// Datos del usuario desde el layout
const page = usePage();
const user = computed(() => page.props.auth?.user || null);

// Categorías enriquecidas con iconos
const categoryIcons = {
    'textiles-aguayos': '🧶',
    'joyeria-plata': '✨',
    'ceramica-arte': '🏺',
    'cuero-artesanal': '🎒',
    'instrumentos-nativos': '🪕',
    'tallados-madera': '🪵',
};
const enrichedCategories = computed(() => {
    return props.categories.map(cat => ({
        ...cat,
        icon: categoryIcons[cat.slug] || '📦',
        count: `${cat.products_count} productos`,
    }));
});

// Redirigir a la búsqueda por categoría
const searchByCategory = (categorySlug) => {
    router.get(route('products.index'), { category: categorySlug });
};

// Añadir al carrito con toast
const toastMessage = ref('');
const toastVisible = ref(false);

const addToCart = (productId) => {
    const form = useForm({ product_id: productId, quantity: 1 });
    form.post(route('cart.store'), {
        preserveScroll: true,
        onSuccess: () => {
            toastMessage.value = '🛒 Producto añadido al carrito';
            toastVisible.value = true;
            setTimeout(() => { toastVisible.value = false; }, 3000);
        },
    });
};
</script>

<template>
    <Head title="Punto Boliviano - El Corazón del Arte Artesanal" />

    <!-- Toast de notificación -->
    <div
        v-if="toastVisible"
        class="fixed top-4 right-4 z-50 bg-green-600 text-white text-sm px-4 py-3 rounded-lg shadow-lg transition-all duration-300"
    >
        {{ toastMessage }}
    </div>

    <!-- Hero -->
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
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-6 py-12 space-y-24">
        <!-- Categorías -->
        <section>
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-xl font-black uppercase tracking-wider text-gray-900 dark:text-white">Explora las Categorías</h2>
                <Link :href="route('products.index')" class="btn-outline text-xs px-3 py-1.5">Ver todos los filtros &rarr;</Link>
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

        <!-- Sección: Más Vendidos -->
        <section v-if="bestSellers && bestSellers.length" class="border-t border-gray-100 dark:border-gray-900 pt-16">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-xl font-black uppercase tracking-wider text-gray-900 dark:text-white">🔥 Más Vendidos</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Los favoritos de la comunidad paceña.</p>
                </div>
                <Link :href="route('products.index', { sort: 'popular' })" class="btn-outline text-xs px-3 py-1.5">Ver todos &rarr;</Link>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <Link
                    v-for="product in bestSellers"
                    :key="product.id"
                    :href="route('products.show', product.id)"
                    class="product-card group cursor-pointer"
                >
                    <div>
                        <div class="w-full aspect-square bg-gray-50 dark:bg-gray-950 rounded-xl flex items-center justify-center mb-4 shadow-inner overflow-hidden">
                            <img v-if="product.images && product.images.length" :src="'/storage/' + product.images[0]" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                            <span v-else class="text-6xl">📦</span>
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white line-clamp-1 group-hover:text-red-600 transition-colors">{{ product.title }}</h4>
                            <p class="text-[11px] text-gray-400">
                                Por: <span class="font-medium text-gray-500 dark:text-gray-300">{{ product.shop?.name }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-3 mt-4 border-t border-gray-50 dark:border-gray-800">
                        <span class="text-lg font-black text-red-600 dark:text-red-500">{{ product.price }} BOB</span>
                        <button @click.prevent="addToCart(product.id)" class="btn-dark px-2.5 py-1 rounded-md text-[10px]">Adquirir</button>
                    </div>
                </Link>
            </div>
        </section>

        <!-- Sección: Mejor Valorados -->
        <section v-if="topRated && topRated.length" class="border-t border-gray-100 dark:border-gray-900 pt-16">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-xl font-black uppercase tracking-wider text-gray-900 dark:text-white">⭐ Mejor Valorados</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Los que más gustan a nuestros compradores.</p>
                </div>
                <Link :href="route('products.index', { sort: 'rating' })" class="btn-outline text-xs px-3 py-1.5">Ver todos &rarr;</Link>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <Link
                    v-for="product in topRated"
                    :key="product.id"
                    :href="route('products.show', product.id)"
                    class="product-card group cursor-pointer"
                >
                    <div>
                        <div class="w-full aspect-square bg-gray-50 dark:bg-gray-950 rounded-xl flex items-center justify-center mb-4 shadow-inner overflow-hidden">
                            <img v-if="product.images && product.images.length" :src="'/storage/' + product.images[0]" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                            <span v-else class="text-6xl">📦</span>
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white line-clamp-1 group-hover:text-red-600 transition-colors">{{ product.title }}</h4>
                            <div class="flex items-center gap-1">
                                <span class="text-[10px] text-yellow-500">{{ '★'.repeat(Math.round(product.reviews_avg_rating || 0)) }}</span>
                                <span class="text-[10px] text-gray-400">{{ Number(product.reviews_avg_rating).toFixed(1) }}</span>                            </div>
                            <p class="text-[11px] text-gray-400">
                                Por: <span class="font-medium text-gray-500 dark:text-gray-300">{{ product.shop?.name }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-3 mt-4 border-t border-gray-50 dark:border-gray-800">
                        <span class="text-lg font-black text-red-600 dark:text-red-500">{{ product.price }} BOB</span>
                        <button @click.prevent="addToCart(product.id)" class="btn-dark px-2.5 py-1 rounded-md text-[10px]">Adquirir</button>
                    </div>
                </Link>
            </div>
        </section>

        <!-- Sección: Recién Llegados -->
        <section v-if="newArrivals && newArrivals.length" class="border-t border-gray-100 dark:border-gray-900 pt-16">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-xl font-black uppercase tracking-wider text-gray-900 dark:text-white">✨ Recién Llegados</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Lo último de los artesanos paceños.</p>
                </div>
                <Link :href="route('products.index', { sort: 'newest' })" class="btn-outline text-xs px-3 py-1.5">Ver todos &rarr;</Link>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <Link
                    v-for="product in newArrivals"
                    :key="product.id"
                    :href="route('products.show', product.id)"
                    class="product-card group cursor-pointer"
                >
                    <div>
                        <div class="w-full aspect-square bg-gray-50 dark:bg-gray-950 rounded-xl flex items-center justify-center mb-4 shadow-inner overflow-hidden">
                            <img v-if="product.images && product.images.length" :src="'/storage/' + product.images[0]" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                            <span v-else class="text-6xl">📦</span>
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white line-clamp-1 group-hover:text-red-600 transition-colors">{{ product.title }}</h4>
                            <p class="text-[11px] text-gray-400">
                                Por: <span class="font-medium text-gray-500 dark:text-gray-300">{{ product.shop?.name }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-3 mt-4 border-t border-gray-50 dark:border-gray-800">
                        <span class="text-lg font-black text-red-600 dark:text-red-500">{{ product.price }} BOB</span>
                        <button @click.prevent="addToCart(product.id)" class="btn-dark px-2.5 py-1 rounded-md text-[10px]">Adquirir</button>
                    </div>
                </Link>
            </div>
        </section>

        <!-- Llamada a artesanos -->
        <section class="rounded-3xl bg-red-700 px-6 py-12 text-center text-white dark:bg-red-900 shadow-xl relative overflow-hidden">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-red-600 rounded-full blur-2xl opacity-50"></div>
            <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-red-500 rounded-full blur-2xl opacity-50"></div>
            <div class="relative z-10 max-w-2xl mx-auto">
                <h2 class="text-3xl font-black tracking-tight sm:text-4xl">¿Eres artesano en La Paz?</h2>
                <p class="mx-auto mt-4 max-w-xl text-sm text-red-100 leading-relaxed">
                    Abre tu tienda digital en Punto Boliviano. Sube tus artesanías, define tus precios en BOB y coordina entregas directas por WhatsApp o recojos físicos desde tu propio puesto en el pasaje.
                </p>
                <div class="mt-8 flex justify-center gap-x-4">
                    <Link :href="route('shop.create')" class="btn-primary bg-white !text-red-700 hover:bg-red-50 px-5 py-3 text-sm font-bold shadow-sm">
                        Comenzar a Vender Digitalmente
                    </Link>
                </div>
            </div>
        </section>
    </main>
</template>