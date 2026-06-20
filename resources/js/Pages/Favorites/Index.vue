<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    favoriteShops: Array,
    favoriteProducts: Array,
    followedShops: Array,
    filters: Object,
});

const activeTab = ref('shops'); // 'shops' | 'products' | 'following'

// Filtros para productos favoritos
const search = ref(props.filters?.search || '');
const sortOrder = ref(props.filters?.sort || 'newest');

const filteredProducts = computed(() => {
    let items = props.favoriteProducts || [];
    if (search.value.trim()) {
        const q = search.value.toLowerCase();
        items = items.filter(p => p.title.toLowerCase().includes(q));
    }
    const sorted = [...items];
    switch (sortOrder.value) {
        case 'price_asc':
            sorted.sort((a, b) => a.price - b.price);
            break;
        case 'price_desc':
            sorted.sort((a, b) => b.price - a.price);
            break;
        case 'alpha_asc':
            sorted.sort((a, b) => a.title.localeCompare(b.title));
            break;
        case 'alpha_desc':
            sorted.sort((a, b) => b.title.localeCompare(a.title));
            break;
    }
    return sorted;
});

// Acciones
const removeShopFav = (shopId) => useForm({}).post(route('favorites.shop.toggle', shopId), { preserveScroll: true });
const removeProductFav = (productId) => useForm({}).post(route('favorites.product.toggle', productId), { preserveScroll: true });
const unfollowShop = (shopId) => useForm({}).post(route('shop.follow', shopId), { preserveScroll: true });
</script>

<template>
    <Head title="Favoritos y Siguiendo" />
    <div class="max-w-5xl mx-auto py-12 px-4">
        <h1 class="text-2xl font-bold mb-6">❤️ Favoritos y Siguiendo</h1>

        <!-- Pestañas -->
        <div class="flex gap-2 mb-6 border-b border-gray-200 dark:border-gray-800 pb-2">
            <button @click="activeTab = 'shops'" :class="activeTab === 'shops' ? 'tab-active' : 'tab-inactive'" class="px-4 py-2 text-sm rounded-md">
                🏪 Tiendas ({{ favoriteShops.length }})
            </button>
            <button @click="activeTab = 'products'" :class="activeTab === 'products' ? 'tab-active' : 'tab-inactive'" class="px-4 py-2 text-sm rounded-md">
                📦 Productos ({{ favoriteProducts.length }})
            </button>
            <button @click="activeTab = 'following'" :class="activeTab === 'following' ? 'tab-active' : 'tab-inactive'" class="px-4 py-2 text-sm rounded-md">
                👥 Siguiendo ({{ followedShops.length }})
            </button>
        </div>

        <!-- PESTAÑA: TIENDAS FAVORITAS -->
        <div v-if="activeTab === 'shops'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="shop in favoriteShops" :key="shop.id" class="card flex items-center justify-between">
                <Link :href="route('shop.view', shop.slug)" class="flex items-center gap-3">
                    <img v-if="shop.avatar" :src="'/storage/' + shop.avatar" class="h-12 w-12 rounded-full object-cover" />
                    <div v-else class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center font-bold text-red-800">{{ shop.name?.charAt(0) }}</div>
                    <div>
                        <h3 class="font-bold text-sm">{{ shop.name }}</h3>
                        <p class="text-xs text-gray-500">{{ shop.owner?.first_name }} {{ shop.owner?.paternal_last_name }}</p>
                    </div>
                </Link>
                <button @click="removeShopFav(shop.id)" class="text-red-500 text-xl">❤️</button>
            </div>
            <p v-if="favoriteShops.length === 0" class="text-gray-500 col-span-full text-center py-8">No tienes tiendas favoritas.</p>
        </div>

        <!-- PESTAÑA: PRODUCTOS FAVORITOS (con filtros laterales) -->
        <div v-if="activeTab === 'products'">
            <div class="lg:grid lg:grid-cols-4 lg:gap-8">
                <!-- Sidebar de filtros -->
                <aside class="lg:col-span-1 mb-6 lg:mb-0">
                    <div class="card space-y-4">
                        <h3 class="text-sm font-bold">Filtros</h3>
                        <div>
                            <label class="text-xs font-bold block mb-1">Buscar</label>
                            <input v-model="search" type="text" placeholder="Buscar..." class="input-field" />
                        </div>
                        <div>
                            <label class="text-xs font-bold block mb-1">Ordenar por</label>
                            <select v-model="sortOrder" class="input-field">
                                <option value="newest">Más recientes</option>
                                <option value="price_asc">Precio: más bajo</option>
                                <option value="price_desc">Precio: más alto</option>
                                <option value="alpha_asc">Alfabético A‑Z</option>
                                <option value="alpha_desc">Alfabético Z‑A</option>
                            </select>
                        </div>
                        <button @click="search = ''; sortOrder = 'newest'" class="btn-outline text-xs w-full py-2">Limpiar filtros</button>
                    </div>
                </aside>

                <!-- Lista de productos -->
                <div class="lg:col-span-3">
                    <div v-if="filteredProducts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div v-for="product in filteredProducts" :key="product.id" class="card flex items-center justify-between">
                            <Link :href="route('products.show', product.id)" class="flex items-center gap-3 flex-1">
                                <img v-if="product.images?.length" :src="'/storage/' + product.images[0]" class="h-12 w-12 object-cover rounded" />
                                <div v-else class="h-12 w-12 bg-gray-200 rounded flex items-center justify-center text-xl">📦</div>
                                <div>
                                    <h3 class="font-bold text-sm">{{ product.title }}</h3>
                                    <p class="text-xs text-gray-500">{{ product.shop?.name }}</p>
                                    <p class="text-sm font-bold text-red-600">{{ product.price }} BOB</p>
                                </div>
                            </Link>
                            <button @click="removeProductFav(product.id)" class="text-red-500 text-xl ml-3">❤️</button>
                        </div>
                    </div>
                    <p v-else class="text-gray-500 text-center py-8">No se encontraron productos.</p>
                </div>
            </div>
        </div>

        <!-- PESTAÑA: SIGUIENDO -->
        <div v-if="activeTab === 'following'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="shop in followedShops" :key="shop.id" class="card flex items-center justify-between">
                <Link :href="route('shop.view', shop.slug)" class="flex items-center gap-3">
                    <img v-if="shop.avatar" :src="'/storage/' + shop.avatar" class="h-12 w-12 rounded-full object-cover" />
                    <div v-else class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center font-bold text-red-800">{{ shop.name?.charAt(0) }}</div>
                    <div>
                        <h3 class="font-bold text-sm">{{ shop.name }}</h3>
                        <p class="text-xs text-gray-500">{{ shop.owner?.first_name }} {{ shop.owner?.paternal_last_name }}</p>
                    </div>
                </Link>
                <button @click="unfollowShop(shop.id)" class="btn-outline text-xs px-2 py-1">Dejar de seguir</button>
            </div>
            <p v-if="followedShops.length === 0" class="text-gray-500 col-span-full text-center py-8">No sigues ninguna tienda.</p>
        </div>
    </div>
</template>