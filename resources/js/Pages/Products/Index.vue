<script setup>
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || '');
const sortOrder = ref(props.filters.sort || 'newest');
const priceMin = ref(Number(props.filters.price_min) || 0);
const priceMax = ref(Number(props.filters.price_max) || 1000);

// Debounce para los sliders
let priceTimeout = null;

function applyFilters() {
    router.get(route('products.index'), {
        search: search.value || null,
        category: selectedCategory.value || null,
        sort: sortOrder.value || 'newest',
        price_min: priceMin.value || null,
        price_max: priceMax.value || null,
    }, { preserveState: true, replace: true });
}

// Watchers para cambios en texto, categoría y ordenación
watch([search, selectedCategory, sortOrder], () => {
    applyFilters();
});

// Watchers específicos para sliders con debounce
watch([priceMin, priceMax], () => {
    if (priceTimeout) clearTimeout(priceTimeout);
    priceTimeout = setTimeout(() => {
        applyFilters();
    }, 400);
});

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

// Limpiar todos los filtros
function clearFilters() {
    search.value = '';
    selectedCategory.value = '';
    sortOrder.value = 'newest';
    priceMin.value = 0;
    priceMax.value = 1000;
}
</script>

<template>
    <Head title="Mercado - Punto Boliviano" />

    <!-- Toast de notificación -->
    <div
        v-if="toastVisible"
        class="fixed top-4 right-4 z-50 bg-green-600 text-white text-sm px-4 py-3 rounded-lg shadow-lg transition-all duration-300"
    >
        {{ toastMessage }}
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Mercado de Artesanías</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Explora productos auténticos de artesanos paceños.</p>
        </div>

        <div class="lg:grid lg:grid-cols-4 lg:gap-8">
            <!-- BARRA LATERAL DE FILTROS -->
            <aside class="lg:col-span-1">
                <div class="card space-y-6">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Filtros</h3>

                    <!-- Buscador -->
                    <div>
                        <label class="text-xs font-bold block mb-1">Buscar</label>
                        <input v-model="search" type="text" placeholder="Buscar..." class="input-field" />
                    </div>

                    <!-- Categoría -->
                    <div>
                        <label class="text-xs font-bold block mb-1">Categoría</label>
                        <select v-model="selectedCategory" class="input-field">
                            <option value="">Todas</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.slug">{{ cat.name }}</option>
                        </select>
                    </div>

                    <!-- Ordenación -->
                    <div>
                        <label class="text-xs font-bold block mb-1">Ordenar por</label>
                        <select v-model="sortOrder" class="input-field">
                            <option value="newest">Más recientes</option>
                            <option value="price_asc">Precio: más bajo</option>
                            <option value="price_desc">Precio: más alto</option>
                            <option value="alpha_asc">Alfabético A‑Z</option>
                            <option value="alpha_desc">Alfabético Z‑A</option>
                            <option value="rating">Mayor rating</option>
                        </select>
                    </div>

                    <!-- Rango de precios con sliders -->
                    <div>
                        <label class="text-xs font-bold block mb-1">Precio (BOB)</label>
                        <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                            <span>Mín: {{ priceMin }}</span>
                            <span>Máx: {{ priceMax }}</span>
                        </div>
                        <div class="space-y-2">
                            <input v-model.number="priceMin" type="range" min="0" max="1000" step="10" class="w-full h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700 accent-red-600" />
                            <input v-model.number="priceMax" type="range" min="0" max="1000" step="10" class="w-full h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700 accent-red-600" />
                        </div>
                    </div>

                    <!-- Botón limpiar filtros -->
                    <button @click="clearFilters" class="btn-outline text-xs w-full py-2">
                        Limpiar filtros
                    </button>
                </div>
            </aside>

            <!-- PRODUCTOS -->
            <div class="mt-8 lg:mt-0 lg:col-span-3">
                <div v-if="products.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    <Link
                        v-for="product in products.data"
                        :key="product.id"
                        :href="route('products.show', product.id)"
                        class="product-card group cursor-pointer"
                        :class="{ 'opacity-60': product.stock === 0 }"
                    >
                        <!-- Imagen y etiqueta agotado -->
                        <div class="relative w-full aspect-square bg-gray-50 dark:bg-gray-800 rounded-xl flex items-center justify-center mb-4 overflow-hidden">
                            <img v-if="product.images && product.images.length" :src="'/storage/' + product.images[0]" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                            <span v-else class="text-6xl">📦</span>
                            <span v-if="product.stock === 0" class="absolute top-2 right-2 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded">Agotado</span>
                        </div>
                        <div class="space-y-1 flex-1">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white line-clamp-1 group-hover:text-red-600 transition-colors">{{ product.title }}</h3>
                            <!-- Estrellas y conteo -->
                            <div class="flex items-center gap-1 mt-1">
                                <span class="text-[10px] text-yellow-500">
                                    {{ '★'.repeat(Math.round(product.average_rating)) }}{{ '☆'.repeat(5 - Math.round(product.average_rating)) }}
                                </span>
                                <span class="text-[10px] text-gray-400">({{ product.reviews_count || 0 }})</span>
                            </div>
                            <p class="text-[11px] text-gray-400">Por: <span class="font-medium text-gray-500 dark:text-gray-300">{{ product.shop?.name }}</span></p>
                            <p class="text-[10px] text-gray-400">📍 {{ product.shop?.city || 'La Paz' }}, {{ product.shop?.zone || '' }}</p>
                        </div>
                        <div class="flex items-center justify-between pt-3 mt-4 border-t border-gray-50 dark:border-gray-800">
                            <span class="text-lg font-black text-red-600 dark:text-red-400">{{ product.price }} BOB</span>
                            <button v-if="product.stock > 0" @click.prevent="addToCart(product.id)" class="btn-dark px-2.5 py-1 rounded-md text-[10px]">Adquirir</button>
                            <span v-else class="text-[10px] text-gray-400 font-bold">No disponible</span>
                        </div>
                    </Link>
                </div>
                <div v-else class="card text-center py-12">
                    <p class="text-gray-500">No se encontraron productos con esos filtros.</p>
                </div>

                <!-- PAGINACIÓN -->
                <div v-if="products.links && products.links.length > 3" class="mt-8 flex justify-center">
                    <nav class="flex gap-1">
                        <template v-for="link in products.links" :key="link.label">
                            <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-3 py-1.5 text-xs rounded-md" :class="link.active ? 'bg-red-600 text-white' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'" />
                            <span v-else v-html="link.label" class="px-3 py-1.5 text-xs text-gray-400"></span>
                        </template>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>