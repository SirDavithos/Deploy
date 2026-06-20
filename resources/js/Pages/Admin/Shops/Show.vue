<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    shop: Object,
});

const activeTab = ref('info'); // info | products | orders

// --- Cambiar estado de la tienda ---
const updateStatus = (newStatus) => {
    if (confirm(`¿Cambiar estado de la tienda a "${newStatus}"?`)) {
        router.patch(route('admin.shops.approve', props.shop.id), {}, { preserveScroll: true });
    }
};

const rejectShop = () => {
    if (confirm('¿Rechazar esta tienda?')) {
        router.patch(route('admin.shops.reject', props.shop.id), {}, { preserveScroll: true });
    }
};
</script>

<template>
    <Head :title="'Admin - ' + shop.name" />
    <div class="space-y-6">
        <!-- Cabecera -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ shop.name }}</h1>
                <span :class="{
                    'badge-pending': shop.status === 'pending',
                    'badge-success': shop.status === 'approved',
                    'bg-red-100 text-red-800': shop.status === 'rejected',
                }" class="badge mt-1">
                    {{ shop.status === 'pending' ? 'Pendiente' : shop.status === 'approved' ? 'Aprobada' : 'Rechazada' }}
                </span>
            </div>
            <div class="flex gap-2">
                <button v-if="shop.status === 'pending'"
                        @click="updateStatus('approved')"
                        class="btn-outline text-xs px-3 py-1.5 bg-green-50 hover:bg-green-100 border-green-300 text-green-700">
                    ✅ Aprobar
                </button>
                <button v-if="shop.status === 'pending'"
                        @click="rejectShop"
                        class="btn-outline text-xs px-3 py-1.5 bg-red-50 hover:bg-red-100 border-red-300 text-red-700">
                    ❌ Rechazar
                </button>
                <Link :href="route('admin.shops.index')" class="btn-outline text-xs px-3 py-1.5">
                    ← Volver
                </Link>
            </div>
        </div>

        <!-- Pestañas -->
        <div class="flex gap-4 mb-4">
            <button @click="activeTab = 'info'" :class="activeTab === 'info' ? 'tab-active' : 'tab-inactive'"
                    class="px-4 py-2 text-sm rounded-md transition-colors">
                📋 Información
            </button>
            <button @click="activeTab = 'products'" :class="activeTab === 'products' ? 'tab-active' : 'tab-inactive'"
                    class="px-4 py-2 text-sm rounded-md transition-colors">
                📦 Productos ({{ shop.products?.length ?? 0 }})
            </button>
            <button @click="activeTab = 'orders'" :class="activeTab === 'orders' ? 'tab-active' : 'tab-inactive'"
                    class="px-4 py-2 text-sm rounded-md transition-colors">
                🛒 Pedidos ({{ shop.orders?.length ?? 0 }})
            </button>
        </div>

        <!-- Contenido de pestañas -->
        <div class="card">
            <!-- Información general -->
            <div v-if="activeTab === 'info'" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-xs font-bold text-gray-500 uppercase mb-2">Datos de la tienda</h3>
                        <div class="space-y-2 text-sm">
                            <p><strong>Dueño:</strong> {{ shop.owner?.first_name }} {{ shop.owner?.paternal_last_name }}</p>
                            <p><strong>Email:</strong> {{ shop.owner?.email }}</p>
                            <p><strong>CI:</strong> {{ shop.owner?.ci_number }} {{ shop.owner?.ci_extension }}</p>
                            <p><strong>Descripción:</strong> {{ shop.description || 'Sin descripción' }}</p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-gray-500 uppercase mb-2">Contacto</h3>
                        <div class="space-y-2 text-sm">
                            <p><strong>Teléfono:</strong> {{ shop.phones?.[0]?.phone_number ?? 'No registrado' }}</p>
                            <p><strong>Dirección:</strong> {{ shop.addresses?.[0]?.street_address }}</p>
                            <p><strong>Zona:</strong> {{ shop.addresses?.[0]?.zone }} – {{ shop.addresses?.[0]?.city }}</p>
                            <p v-if="shop.addresses?.[0]?.reference"><strong>Referencia:</strong> {{ shop.addresses[0].reference }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Productos -->
            <div v-if="activeTab === 'products'" class="space-y-4">
                <div v-if="shop.products && shop.products.length > 0">
                    <div v-for="product in shop.products" :key="product.id" class="border dark:border-gray-800 rounded-lg p-4 flex gap-4">
                        <img v-if="product.images?.length" :src="'/storage/' + product.images[0]" class="h-16 w-16 object-cover rounded" />
                        <div v-else class="h-16 w-16 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center text-xl">📦</div>
                        <div class="flex-1">
                            <h4 class="font-bold text-sm">{{ product.title }}</h4>
                            <p class="text-xs text-gray-500">{{ product.price }} BOB – Stock: {{ product.stock }}</p>
                            <Link :href="route('products.show', product.id)" class="text-xs text-blue-600">Ver producto</Link>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-gray-500">Esta tienda no tiene productos.</p>
            </div>

            <!-- Pedidos -->
            <div v-if="activeTab === 'orders'" class="space-y-4">
                <div v-if="shop.orders && shop.orders.length > 0">
                    <div v-for="order in shop.orders" :key="order.id" class="border dark:border-gray-800 rounded-lg p-4 space-y-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-xs font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">#{{ order.id }}</span>
                                <span class="ml-2 text-xs font-bold">{{ order.buyer?.first_name }} {{ order.buyer?.paternal_last_name }}</span>
                            </div>
                            <span :class="{
                                'badge-pending': order.status === 'pending',
                                'badge-success': order.status === 'delivered' || order.status === 'confirmed'
                            }" class="badge">{{ order.status }}</span>
                        </div>
                        <ul class="text-xs space-y-1">
                            <li v-for="item in order.items" :key="item.id" class="flex justify-between border-b dark:border-gray-800 pb-1">
                                <span>{{ item.product?.title }} (x{{ item.quantity }})</span>
                                <span class="font-mono">{{ item.price }} BOB</span>
                            </li>
                        </ul>
                        <div class="flex justify-between font-bold text-sm pt-2">
                            <span>Total</span>
                            <span class="text-red-600">{{ order.total }} BOB</span>
                        </div>
                        <Link :href="route('admin.orders.show', order.id)" class="text-xs text-blue-600">Ver pedido</Link>
                    </div>
                </div>
                <p v-else class="text-sm text-gray-500">No hay pedidos para esta tienda.</p>
            </div>
        </div>
    </div>
</template>