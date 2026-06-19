<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ shop: Object });

const activeTab = ref('info'); // info | products | orders

// ==================== EDITAR TIENDA ====================
const editForm = useForm({
    name: props.shop.name,
    description: props.shop.description || '',
    avatar: null,
    banner: null,
});

const avatarPreview = ref(props.shop.avatar ? `/storage/${props.shop.avatar}` : null);
const bannerPreview = ref(props.shop.banner ? `/storage/${props.shop.banner}` : null);

const selectAvatar = (e) => {
    editForm.avatar = e.target.files[0];
    if (editForm.avatar) avatarPreview.value = URL.createObjectURL(editForm.avatar);
};
const selectBanner = (e) => {
    editForm.banner = e.target.files[0];
    if (editForm.banner) bannerPreview.value = URL.createObjectURL(editForm.banner);
};

const updateShop = () => {
    editForm.patch(route('shop.update', props.shop.id), {
        preserveScroll: true,
        onSuccess: () => editForm.reset('avatar', 'banner'),
    });
};

// ==================== PRODUCTOS ====================
const deleteProduct = (id) => {
    if (confirm('¿Eliminar este producto?')) {
        useForm({}).delete(route('products.destroy', id), { preserveScroll: true });
    }
};

// ==================== PEDIDOS ====================
const updateOrderStatus = (orderId, newStatus) => {
    if (confirm(`¿Cambiar estado del pedido a "${newStatus}"?`)) {
        useForm({ status: newStatus }).patch(route('orders.update', orderId), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head :title="'Mi Tienda - ' + shop.name" />
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950">
        <!-- Banner y Avatar -->
        <div class="relative">
            <div v-if="shop.banner" class="w-full h-48 md:h-64 overflow-hidden">
                <img :src="'/storage/' + shop.banner" class="w-full h-full object-cover" />
            </div>
            <div v-else class="w-full h-32 md:h-48 bg-gradient-to-r from-red-100 to-red-200 dark:from-gray-800 dark:to-gray-700"></div>
            <div class="max-w-5xl mx-auto px-4 relative z-10 -mt-12 flex items-end gap-4">
                <img v-if="shop.avatar" :src="'/storage/' + shop.avatar" class="h-24 w-24 rounded-full border-4 border-white dark:border-gray-950 shadow object-cover" />
                <div v-else class="h-24 w-24 rounded-full bg-gray-300 dark:bg-gray-600 border-4 border-white dark:border-gray-950 shadow flex items-center justify-center text-3xl">🏪</div>
                <div class="pb-2">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ shop.name }}</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Por: {{ shop.owner?.first_name }} {{ shop.owner?.paternal_last_name }}</p>
                    <span :class="{
                        'bg-yellow-200 text-yellow-800': shop.status === 'pending',
                        'bg-green-200 text-green-800': shop.status === 'approved',
                        'bg-red-200 text-red-800': shop.status === 'rejected'
                    }" class="inline-block mt-1 text-xs px-2 py-1 rounded font-medium">
                        {{ shop.status === 'pending' ? 'Pendiente' : shop.status === 'approved' ? 'Aprobada' : 'Rechazada' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="max-w-5xl mx-auto px-4 py-8">
            <!-- Pestañas -->
            <div class="flex gap-4 mb-6">
                <button @click="activeTab = 'info'" :class="activeTab === 'info' ? 'tab-active' : 'tab-inactive'" class="px-4 py-2 text-sm rounded-md transition-colors">📋 Información</button>
                <button @click="activeTab = 'products'" :class="activeTab === 'products' ? 'tab-active' : 'tab-inactive'" class="px-4 py-2 text-sm rounded-md transition-colors">📦 Productos</button>
                <button @click="activeTab = 'orders'" :class="activeTab === 'orders' ? 'tab-active' : 'tab-inactive'" class="px-4 py-2 text-sm rounded-md transition-colors">🛒 Pedidos</button>
            </div>

            <!-- Contenido de pestañas -->
            <div class="card">
                <!-- Información de la tienda -->
                <div v-if="activeTab === 'info'" class="space-y-6">
                    <h3 class="section-title">Editar Información</h3>
                    <form @submit.prevent="updateShop" class="space-y-4">
                        <div>
                            <label class="text-xs font-bold block mb-1">Nombre de la tienda</label>
                            <input v-model="editForm.name" type="text" class="input-field" required />
                            <p v-if="editForm.errors.name" class="text-[11px] text-red-500 mt-1">{{ editForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-bold block mb-1">Descripción</label>
                            <textarea v-model="editForm.description" rows="4" class="input-field"></textarea>
                        </div>
                        <div>
                            <label class="text-xs font-bold block mb-1">Foto de perfil (Avatar)</label>
                            <input type="file" accept="image/*" @change="selectAvatar" class="text-xs" />
                            <div class="mt-2 flex items-center gap-4">
                                <img v-if="avatarPreview" :src="avatarPreview" class="h-20 w-20 rounded-full object-cover border" />
                                <span v-else class="text-xs text-gray-400">Sin avatar</span>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-bold block mb-1">Banner (Portada)</label>
                            <input type="file" accept="image/*" @change="selectBanner" class="text-xs" />
                            <div class="mt-2">
                                <img v-if="bannerPreview" :src="bannerPreview" class="w-full h-32 object-cover rounded-lg border" />
                                <span v-else class="text-xs text-gray-400">Sin banner</span>
                            </div>
                        </div>
                        <div class="flex justify-end pt-4 border-t dark:border-gray-800">
                            <button type="submit" :disabled="editForm.processing" class="btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>

                <!-- Productos -->
                <div v-if="activeTab === 'products'" class="space-y-4">
                    <div class="flex justify-between items-center">
                        <h3 class="section-title">Tus Productos</h3>
                        <Link :href="route('products.create')" class="btn-primary text-xs">+ Nuevo Producto</Link>
                    </div>
                    <div v-if="shop.products && shop.products.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div v-for="product in shop.products" :key="product.id" class="border dark:border-gray-800 rounded-lg p-4 flex gap-4">
                            <img v-if="product.images.length" :src="'/storage/' + product.images[0]" class="h-20 w-20 object-cover rounded" />
                            <div v-else class="h-20 w-20 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center text-2xl">📦</div>
                            <div class="flex-1">
                                <h4 class="font-bold text-sm">{{ product.title }}</h4>
                                <p class="text-xs text-gray-500">{{ product.price }} BOB – Stock: {{ product.stock }}</p>
                                <div class="flex gap-2 mt-2">
                                    <Link :href="route('products.edit', product.id)" class="text-xs text-blue-600">Editar</Link>
                                    <button @click="deleteProduct(product.id)" class="text-xs text-red-500">Eliminar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-gray-500">No tienes productos aún.</p>
                </div>

                <!-- Pedidos (REAL) -->
                <div v-if="activeTab === 'orders'" class="space-y-6">
                    <h3 class="section-title">📦 Pedidos Recibidos</h3>
                    <div v-if="shop.orders && shop.orders.length > 0">
                        <div v-for="order in shop.orders" :key="order.id" class="border dark:border-gray-800 rounded-lg p-4 space-y-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-xs font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">#{{ order.id }}</span>
                                    <span class="ml-2 text-xs font-bold">
                                        {{ order.buyer?.first_name }} {{ order.buyer?.paternal_last_name }}
                                    </span>
                                </div>
                                <span :class="{
                                    'badge-pending': order.status === 'pending',
                                    'badge-success': order.status === 'delivered' || order.status === 'confirmed' || order.status === 'shipped'
                                }" class="badge">
                                    {{ order.status }}
                                </span>
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
                            <!-- Cambiar estado -->
                            <div class="flex gap-2 mt-2">
                                <button v-if="order.status === 'pending'" @click="updateOrderStatus(order.id, 'confirmed')" class="btn-outline text-xs px-2 py-1">Confirmar</button>
                                <button v-if="order.status === 'confirmed'" @click="updateOrderStatus(order.id, 'shipped')" class="btn-outline text-xs px-2 py-1">Marcar Enviado</button>
                                <button v-if="order.status === 'shipped'" @click="updateOrderStatus(order.id, 'delivered')" class="btn-outline text-xs px-2 py-1">Marcar Entregado</button>
                                <button v-if="order.status !== 'cancelled' && order.status !== 'delivered'" @click="updateOrderStatus(order.id, 'cancelled')" class="btn-outline text-xs px-2 py-1 text-red-500">Cancelar</button>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-500">No has recibido pedidos aún.</p>
                </div>
            </div>
        </div>
    </div>
</template>