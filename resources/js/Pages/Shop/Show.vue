<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({ shop: Object });

const activeTab = ref('info'); // info | products | orders

// Datos del usuario autenticado
const page = usePage();
const user = computed(() => page.props.auth?.user || null);
const userRoles = computed(() => page.props.auth?.userRoles || []);
const userShop = computed(() => page.props.auth?.userShop || null);

// ¿Es el dueño o admin? (para permitir edición)
const isOwnerOrAdmin = computed(() => {
    if (!user.value) return false;
    return user.value.id === props.shop.user_id || userRoles.value.includes('admin');
});

// Valoración general de la tienda (promedio de reseñas de sus productos)
const shopRating = computed(() => {
    const reviews = props.shop.products?.flatMap(p => p.reviews || []) || [];
    if (reviews.length === 0) return 0;
    const total = reviews.reduce((sum, r) => sum + r.rating, 0);
    return (total / reviews.length).toFixed(1);
});

const totalReviews = computed(() => {
    return props.shop.products?.reduce((count, p) => count + (p.reviews?.length || 0), 0) || 0;
});

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
    editForm.post(route('shop.update', props.shop.id), {
        _method: 'PATCH',      // simula PATCH a través de POST
        forceFormData: true,   // necesario para manejar archivos
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

    <!-- Banner y Avatar -->
    <div class="relative">
        <div v-if="shop.banner" class="w-full h-48 md:h-64 overflow-hidden">
            <img :src="'/storage/' + shop.banner" class="w-full h-full object-cover" />
        </div>
        <div v-else class="w-full h-32 md:h-48 bg-gradient-to-r from-red-100 to-red-200 dark:from-gray-800 dark:to-gray-700"></div>

        <!-- Botón volver al inicio -->
        <Link :href="route('home')" class="absolute top-4 left-4 bg-white dark:bg-gray-900 px-4 py-2 rounded-lg shadow text-xs font-bold text-gray-600 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 transition-colors">
            ← Volver al inicio
        </Link>

        <div class="max-w-5xl mx-auto px-4 relative z-10 -mt-12 flex items-end gap-4">
            <img v-if="shop.avatar" :src="'/storage/' + shop.avatar" class="h-24 w-24 rounded-full border-4 border-white dark:border-gray-950 shadow object-cover" />
            <div v-else class="h-24 w-24 rounded-full bg-gray-300 dark:bg-gray-600 border-4 border-white dark:border-gray-950 shadow flex items-center justify-center text-3xl">🏪</div>
            <div class="pb-2">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ shop.name }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">Por: {{ shop.owner?.first_name }} {{ shop.owner?.paternal_last_name }}</p>
                <div class="flex items-center gap-2 mt-1">
                    <span :class="{
                        'badge badge-pending': shop.status === 'pending',
                        'badge badge-success': shop.status === 'approved',
                        'badge badge-danger': shop.status === 'rejected'
                    }">
                        {{ shop.status === 'pending' ? 'Pendiente' : shop.status === 'approved' ? 'Aprobada' : 'Rechazada' }}
                    </span>
                    <!-- Valoración general de la tienda -->
                    <span v-if="totalReviews > 0" class="text-xs text-yellow-600">
                        ⭐ {{ shopRating }} ({{ totalReviews }} reseñas)
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="max-w-5xl mx-auto px-4 py-8">
        <!-- Pestañas -->
        <div class="flex gap-2 mb-6 border-b border-gray-200 dark:border-gray-800 pb-2">
            <button @click="activeTab = 'info'"
                :class="activeTab === 'info' ? 'tab-active' : 'tab-inactive'"
                class="px-4 py-2 text-sm rounded-md transition-colors">
                📋 Información
            </button>
            <button @click="activeTab = 'products'"
                :class="activeTab === 'products' ? 'tab-active' : 'tab-inactive'"
                class="px-4 py-2 text-sm rounded-md transition-colors">
                📦 Productos
                <span v-if="shop.products?.length" class="ml-1 text-xs">({{ shop.products.length }})</span>
            </button>
            <button @click="activeTab = 'orders'"
                :class="activeTab === 'orders' ? 'tab-active' : 'tab-inactive'"
                class="px-4 py-2 text-sm rounded-md transition-colors">
                🛒 Pedidos
                <span v-if="shop.orders?.length" class="ml-1 text-xs">({{ shop.orders.length }})</span>
            </button>
        </div>

        <!-- Contenido de pestañas -->
        <div class="card">
            <!-- Información de la tienda -->
            <div v-if="activeTab === 'info'" class="space-y-6">
                <h3 class="section-title">Información de la tienda</h3>
                <div v-if="shop.description" class="text-sm text-gray-600 dark:text-gray-400">
                    {{ shop.description }}
                </div>
                <div v-else class="text-sm text-gray-400 italic">Sin descripción.</div>

                <!-- Formulario de edición (solo dueño o admin) -->
                <div v-if="isOwnerOrAdmin" class="border-t pt-6 mt-6">
                    <h3 class="section-title mb-4">Editar Información</h3>
                    <form @submit.prevent="updateShop" class="space-y-5">
                        <div>
                            <label class="text-xs font-bold block mb-1">Nombre de la tienda</label>
                            <input v-model="editForm.name" type="text" class="input-field" required />
                            <p v-if="editForm.errors.name" class="input-error">{{ editForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-bold block mb-1">Descripción</label>
                            <textarea v-model="editForm.description" rows="4" class="input-field"></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                        </div>
                        <div class="flex justify-end pt-4 border-t dark:border-gray-800">
                            <button type="submit" :disabled="editForm.processing" class="btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Productos (cuadrícula estilo mercado) -->
            <div v-if="activeTab === 'products'" class="space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="section-title">Todos los productos</h3>
                    <Link v-if="isOwnerOrAdmin" :href="route('products.create')" class="btn-primary text-xs">+ Nuevo Producto</Link>
                </div>
                <div v-if="shop.products && shop.products.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Link v-for="product in shop.products" :key="product.id" :href="route('products.show', product.id)"
                        class="product-card group cursor-pointer">
                        <div class="relative w-full aspect-square bg-gray-50 dark:bg-gray-800 rounded-xl flex items-center justify-center mb-4 overflow-hidden">
                            <img v-if="product.images.length" :src="'/storage/' + product.images[0]" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                            <span v-else class="text-4xl">📦</span>
                        </div>
                        <div class="space-y-1 flex-1">
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white line-clamp-1 group-hover:text-red-600 transition-colors">{{ product.title }}</h4>
                            <p class="text-xs text-gray-500">{{ product.price }} BOB – Stock: {{ product.stock }}</p>
                            <!-- Acciones de dueño -->
                            <div v-if="isOwnerOrAdmin" class="flex gap-2 mt-2">
                                <button @click.prevent="router.get(route('products.edit', product.id))" class="text-xs text-blue-600 font-bold hover:underline">Editar</button>
                                <button @click.prevent="deleteProduct(product.id)" class="text-xs text-red-500 font-bold hover:underline">Eliminar</button>
                            </div>
                        </div>
                    </Link>
                </div>
                <p v-else class="text-sm text-gray-500">No hay productos aún.</p>
            </div>

            <!-- Pedidos (solo visible para dueño/admin) -->
            <div v-if="activeTab === 'orders' && isOwnerOrAdmin" class="space-y-6">
                <h3 class="section-title">📦 Pedidos Recibidos</h3>
                <div v-if="shop.orders && shop.orders.length > 0" class="space-y-4">
                    <div v-for="order in shop.orders" :key="order.id" class="border dark:border-gray-800 rounded-lg p-4 space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-xs font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">#{{ order.id }}</span>
                                <span class="ml-2 text-xs font-bold">
                                    {{ order.buyer?.first_name }} {{ order.buyer?.paternal_last_name }}
                                </span>
                            </div>
                            <span :class="{
                                'badge badge-pending': order.status === 'pending',
                                'badge badge-success': order.status === 'delivered' || order.status === 'confirmed' || order.status === 'shipped',
                                'badge badge-danger': order.status === 'cancelled'
                            }">
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
                        <div class="flex flex-wrap gap-2 mt-2">
                            <button v-if="order.status === 'pending'" @click="updateOrderStatus(order.id, 'confirmed')" class="btn-outline text-xs">Confirmar</button>
                            <button v-if="order.status === 'confirmed'" @click="updateOrderStatus(order.id, 'shipped')" class="btn-outline text-xs">Marcar Enviado</button>
                            <button v-if="order.status === 'shipped'" @click="updateOrderStatus(order.id, 'delivered')" class="btn-outline text-xs">Marcar Entregado</button>
                            <button v-if="order.status !== 'cancelled' && order.status !== 'delivered'" @click="updateOrderStatus(order.id, 'cancelled')" class="btn-outline text-xs text-red-500">Cancelar</button>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-gray-500">No has recibido pedidos aún.</p>
            </div>
            <div v-else-if="activeTab === 'orders' && !isOwnerOrAdmin" class="text-sm text-gray-500">
                No tienes permisos para ver los pedidos.
            </div>
        </div>
    </div>
</template>