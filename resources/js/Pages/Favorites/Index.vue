<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    favoriteShops: Array,
    favoriteProducts: Array,
});

const activeTab = ref('shops');

// Eliminar favorito
const removeShopFav = (shopId) => {
    useForm({}).post(route('favorites.shop.toggle', shopId), { preserveScroll: true });
};
const removeProductFav = (productId) => {
    useForm({}).post(route('favorites.product.toggle', productId), { preserveScroll: true });
};
</script>

<template>
    <Head title="Favoritos" />
    <div class="max-w-5xl mx-auto py-12 px-4">
        <h1 class="text-2xl font-bold mb-6">❤️ Favoritos</h1>

        <div class="flex gap-4 mb-6">
            <button @click="activeTab = 'shops'" :class="activeTab === 'shops' ? 'tab-active' : 'tab-inactive'" class="px-4 py-2 text-sm rounded-md">
                🏪 Tiendas ({{ favoriteShops.length }})
            </button>
            <button @click="activeTab = 'products'" :class="activeTab === 'products' ? 'tab-active' : 'tab-inactive'" class="px-4 py-2 text-sm rounded-md">
                📦 Productos ({{ favoriteProducts.length }})
            </button>
        </div>

        <!-- Tiendas favoritas -->
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
            <p v-if="favoriteShops.length === 0" class="text-gray-500 col-span-full text-center">No tienes tiendas favoritas.</p>
        </div>

        <!-- Productos favoritos -->
        <div v-if="activeTab === 'products'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="product in favoriteProducts" :key="product.id" class="card flex items-center justify-between">
                <Link :href="route('products.show', product.id)" class="flex items-center gap-3">
                    <img v-if="product.images?.length" :src="'/storage/' + product.images[0]" class="h-12 w-12 object-cover rounded" />
                    <div v-else class="h-12 w-12 bg-gray-200 rounded flex items-center justify-center text-xl">📦</div>
                    <div>
                        <h3 class="font-bold text-sm">{{ product.title }}</h3>
                        <p class="text-xs text-gray-500">{{ product.shop?.name }}</p>
                        <p class="text-sm font-bold text-red-600">{{ product.price }} BOB</p>
                    </div>
                </Link>
                <button @click="removeProductFav(product.id)" class="text-red-500 text-xl">❤️</button>
            </div>
            <p v-if="favoriteProducts.length === 0" class="text-gray-500 col-span-full text-center">No tienes productos favoritos.</p>
        </div>
    </div>
</template>