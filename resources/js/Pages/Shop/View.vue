<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    shop: Object,
});

// ==================== PESTAÑAS ====================
const activeTab = ref('products'); // 'products' | 'reviews' | 'info'

// ==================== ESTADÍSTICAS ====================
const shopRating = computed(() => {
    return props.shop.products_avg_rating
        ? parseFloat(props.shop.products_avg_rating).toFixed(1)
        : '0.0';
});

const totalReviews = computed(() => props.shop.products_reviews_count || 0);
const totalSales = computed(() => props.shop.sales_count || 0);

// ==================== FILTROS (barra lateral) ====================
const searchQuery = ref('');
const sortOrder = ref('newest');

const filteredProducts = computed(() => {
    let items = props.shop.products || [];

    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        items = items.filter(p =>
            p.title.toLowerCase().includes(q) ||
            (p.description && p.description.toLowerCase().includes(q))
        );
    }

    const sorted = [...items];
    switch (sortOrder.value) {
        case 'price_asc':
            sorted.sort((a, b) => a.price - b.price);
            break;
        case 'price_desc':
            sorted.sort((a, b) => b.price - a.price);
            break;
        case 'rating':
            sorted.sort((a, b) => (b.reviews_avg_rating || 0) - (a.reviews_avg_rating || 0));
            break;
        case 'alpha_asc':
            sorted.sort((a, b) => a.title.localeCompare(b.title));
            break;
        case 'alpha_desc':
            sorted.sort((a, b) => b.title.localeCompare(a.title));
            break;
        default:
            sorted.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    }
    return sorted;
});

// ==================== AÑADIR AL CARRITO ====================
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

// ==================== SEGUIR TIENDA (real) ====================
const isFollowing = ref(props.shop.is_followed_by_user || false);

const toggleFollow = () => {
    const form = useForm({});
    form.post(route('shop.follow', props.shop.id), {
        preserveScroll: true,
        onSuccess: () => {
            isFollowing.value = !isFollowing.value;
        },
    });
};

// ==================== WHATSAPP / DIRECCIÓN ====================
const primaryPhone = computed(() => props.shop.phones?.[0]?.phone_number || null);

// Limpia el número para WhatsApp (elimina espacios, guiones, etc.)
const waNumber = computed(() => {
    const raw = primaryPhone.value;
    if (!raw) return null;
    const cleaned = raw.replace(/[\s\-\(\)\+]/g, '');
    // Si el número empieza con '591' ya tiene código de país, si no asumimos Bolivia
    const withCode = cleaned.startsWith('591') ? cleaned : '591' + cleaned;
    return withCode;
});

const whatsappLink = computed(() => {
    return `https://wa.me/${waNumber.value}`;
});

const primaryAddress = computed(() => props.shop.addresses?.[0] || null);
const hasGps = computed(() => primaryAddress.value?.latitude && primaryAddress.value?.longitude);
const staticMapUrl = computed(() => {
    if (!hasGps.value) return null;
    const lat = primaryAddress.value.latitude;
    const lon = primaryAddress.value.longitude;
    return `https://www.openstreetmap.org/export/embed.html?bbox=${lon-0.005},${lat-0.005},${lon+0.005},${lat+0.005}&layer=mapnik&marker=${lat},${lon}`;
});
</script>

<template>
    <Head :title="shop.name + ' - Punto Boliviano'" />

    <!-- Toast -->
    <div
        v-if="toastVisible"
        class="fixed top-4 right-4 z-50 bg-green-600 text-white text-sm px-4 py-3 rounded-lg shadow-lg transition-all duration-300"
    >
        {{ toastMessage }}
    </div>

    <!-- Banner y Avatar -->
    <div class="relative">
        <div v-if="shop.banner" class="w-full h-48 md:h-64 overflow-hidden">
            <img :src="'/storage/' + shop.banner" class="w-full h-full object-cover" />
        </div>
        <div v-else class="w-full h-32 md:h-48 bg-gradient-to-r from-red-100 to-red-200 dark:from-gray-800 dark:to-gray-700"></div>

        <div class="max-w-5xl mx-auto px-4 relative z-10 -mt-12 flex items-end gap-4">
            <img v-if="shop.avatar" :src="'/storage/' + shop.avatar" class="h-24 w-24 rounded-full border-4 border-white dark:border-gray-950 shadow object-cover" />
            <div v-else class="h-24 w-24 rounded-full bg-gray-300 dark:bg-gray-600 border-4 border-white dark:border-gray-950 shadow flex items-center justify-center text-3xl">🏪</div>
            <div class="pb-2 flex-1">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ shop.name }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">Por: {{ shop.owner?.first_name }} {{ shop.owner?.paternal_last_name }}</p>
                <div class="flex items-center gap-3 mt-1 flex-wrap">
                    <span v-if="totalReviews > 0" class="text-sm text-yellow-600">
                        ⭐ {{ shopRating }} ({{ totalReviews }} reseñas)
                    </span>
                    <span v-else class="text-xs text-gray-400">Sin reseñas aún</span>
                    <span class="text-xs text-gray-500">|</span>
                    <span class="text-xs text-gray-600">{{ totalSales }} ventas</span>
                    <!-- WhatsApp -->
                    <span v-if="primaryPhone" class="text-xs text-gray-500">|</span>
                    <a v-if="primaryPhone" :href="whatsappLink" target="_blank" class="text-xs text-green-600 font-bold hover:underline">
                        💬 {{ primaryPhone }}
                    </a>
                </div>
            </div>
            <button @click="toggleFollow" class="btn-outline text-xs px-3 py-1.5" :class="{ 'bg-red-50 text-red-700 border-red-300': isFollowing }">
                {{ isFollowing ? '✓ Siguiendo' : '+ Seguir tienda' }}
            </button>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="max-w-5xl mx-auto px-4 py-8">
        <!-- Pestañas -->
        <div class="flex gap-2 mb-6 border-b border-gray-200 dark:border-gray-800 pb-2">
            <button @click="activeTab = 'products'"
                :class="activeTab === 'products' ? 'tab-active' : 'tab-inactive'"
                class="px-4 py-2 text-sm rounded-md transition-colors">
                📦 Productos ({{ shop.products?.length || 0 }})
            </button>
            <button @click="activeTab = 'reviews'"
                :class="activeTab === 'reviews' ? 'tab-active' : 'tab-inactive'"
                class="px-4 py-2 text-sm rounded-md transition-colors">
                ⭐ Reseñas ({{ totalReviews }})
            </button>
            <button @click="activeTab = 'info'"
                :class="activeTab === 'info' ? 'tab-active' : 'tab-inactive'"
                class="px-4 py-2 text-sm rounded-md transition-colors">
                📍 Información
            </button>
        </div>

        <!-- Contenido de pestañas -->
        <div class="card">
            <!-- PESTAÑA PRODUCTOS (con sidebar de filtros) -->
            <div v-if="activeTab === 'products'">
                <div class="lg:grid lg:grid-cols-4 lg:gap-8">
                    <aside class="lg:col-span-1 mb-6 lg:mb-0">
                        <div class="card space-y-4">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Filtros</h3>
                            <div>
                                <label class="text-xs font-bold block mb-1">Buscar</label>
                                <input v-model="searchQuery" type="text" placeholder="Buscar..." class="input-field" />
                            </div>
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
                            <button @click="searchQuery = ''; sortOrder = 'newest'" class="btn-outline text-xs w-full py-2">
                                Limpiar filtros
                            </button>
                        </div>
                    </aside>

                    <div class="lg:col-span-3">
                        <div v-if="filteredProducts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                            <Link v-for="product in filteredProducts" :key="product.id" :href="route('products.show', product.id)"
                                class="product-card group cursor-pointer">
                                <div class="relative w-full aspect-square bg-gray-50 dark:bg-gray-800 rounded-xl flex items-center justify-center mb-4 overflow-hidden">
                                    <img v-if="product.images && product.images.length" :src="'/storage/' + product.images[0]" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                    <span v-else class="text-4xl">📦</span>
                                </div>
                                <div class="space-y-1 flex-1">
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white line-clamp-1 group-hover:text-red-600 transition-colors">{{ product.title }}</h3>
                                    <div class="flex items-center gap-1 mt-1">
                                        <span class="text-[10px] text-yellow-500">
                                            {{ '★'.repeat(Math.round(product.reviews_avg_rating || 0)) }}{{ '☆'.repeat(5 - Math.round(product.reviews_avg_rating || 0)) }}
                                        </span>
                                        <span class="text-[10px] text-gray-400">({{ product.reviews_count || 0 }})</span>
                                    </div>
                                    <p class="text-sm font-bold text-red-600 dark:text-red-400">{{ product.price }} BOB</p>
                                </div>
                                <button @click.prevent="addToCart(product.id)" class="btn-dark px-2.5 py-1 rounded-md text-[10px] mt-2">Adquirir</button>
                            </Link>
                        </div>
                        <p v-else class="text-sm text-gray-500">No se encontraron productos.</p>
                    </div>
                </div>
            </div>

            <!-- PESTAÑA RESEÑAS -->
            <div v-if="activeTab === 'reviews'" class="space-y-4">
                <div v-if="shop.all_reviews && shop.all_reviews.length > 0">
                    <div v-for="review in shop.all_reviews" :key="review.id" class="border-b dark:border-gray-800 pb-4 mb-4 last:border-0 last:mb-0">
                        <div class="flex items-center gap-3 mb-2">
                            <img v-if="review.user.avatar" :src="'/storage/' + review.user.avatar" class="h-8 w-8 rounded-full" />
                            <div v-else class="h-8 w-8 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center text-xs font-bold text-red-800">
                                {{ review.user.first_name?.charAt(0) }}
                            </div>
                            <div>
                                <span class="text-sm font-bold">{{ review.user.first_name }} {{ review.user.paternal_last_name }}</span>
                                <span class="text-xs text-gray-400 ml-2">{{ review.created_at }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-yellow-500 text-sm">★ {{ review.rating }}</span>
                            <Link :href="route('products.show', review.product_id)" class="text-xs text-gray-500 hover:text-red-600">
                                sobre {{ review.product?.title || 'producto' }}
                            </Link>
                        </div>
                        <p v-if="review.comment" class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ review.comment }}</p>
                    </div>
                </div>
                <p v-else class="text-sm text-gray-500">Esta tienda no tiene reseñas aún.</p>
            </div>

            <!-- PESTAÑA INFORMACIÓN (Dirección y GPS) -->
            <div v-if="activeTab === 'info'" class="space-y-6">
                <h3 class="section-title">📍 Ubicación</h3>
                <div v-if="primaryAddress" class="space-y-2">
                    <p class="text-sm">{{ primaryAddress.street_address }}</p>
                    <p class="text-sm text-gray-500">{{ primaryAddress.zone }}, {{ primaryAddress.city }}</p>
                    <p v-if="primaryAddress.reference" class="text-xs text-gray-400">Ref: {{ primaryAddress.reference }}</p>
                    <div v-if="hasGps" class="text-xs text-gray-500">
                        🛰️ {{ primaryAddress.latitude }}, {{ primaryAddress.longitude }}
                    </div>
                    <!-- Mapa estático (iframe) -->
                    <div v-if="staticMapUrl" class="w-full h-64 rounded-lg overflow-hidden border dark:border-gray-700 mt-4">
                        <iframe :src="staticMapUrl" width="100%" height="100%" frameborder="0" scrolling="no"></iframe>
                    </div>
                </div>
                <div v-else class="text-sm text-gray-500">Esta tienda no ha registrado una dirección.</div>

                <div class="border-t pt-4 mt-4">
                    <h3 class="section-title mb-2">📞 Contacto</h3>
                    <div v-if="primaryPhone">
                        <a :href="whatsappLink" target="_blank" class="btn-whatsapp inline-flex items-center gap-2">
                            💬 Enviar WhatsApp
                        </a>
                    </div>
                    <p v-else class="text-sm text-gray-500">No hay teléfono de contacto.</p>
                </div>
            </div>
        </div>
    </div>
</template>