<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    orders: Object,   // paginado
    filters: Object,  // valores actuales de los filtros
});

// ==================== FILTROS (barra lateral) ====================
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const minTotal = ref(Number(props.filters.min_total) || 0);
const maxTotal = ref(Number(props.filters.max_total) || 5000); // ajusta el máximo según tus datos

// Aplicar filtros automáticamente (con debounce para sliders)
let timeout = null;
function applyFilters() {
    if (timeout) clearTimeout(timeout);
    timeout = setTimeout(() => {
        const query = {};
        if (search.value) query.search = search.value;
        if (status.value) query.status = status.value;
        if (dateFrom.value) query.date_from = dateFrom.value;
        if (dateTo.value) query.date_to = dateTo.value;
        if (minTotal.value > 0) query.min_total = minTotal.value;
        if (maxTotal.value < 5000) query.max_total = maxTotal.value; // ajusta si el máximo es menor que el tope

        Inertia.get(route('orders.index'), query, {
            preserveState: true,
            replace: true,
        });
    }, 400);
}

watch([search, status, dateFrom, dateTo, minTotal, maxTotal], applyFilters);

const clearFilters = () => {
    search.value = '';
    status.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    minTotal.value = 0;
    maxTotal.value = 5000;
};

// ==================== RESEÑAS POR PRODUCTO ====================
const reviewForms = ref({}); // { [orderId_productId]: { rating, comment, processing } }

const startReview = (orderId, productId) => {
    const key = `${orderId}_${productId}`;
    if (!reviewForms.value[key]) {
        reviewForms.value[key] = useForm({
            rating: 0,
            comment: '',
        });
    }
    // Abrir modal o mostrar inline
    reviewForms.value[key]._active = true;
};

const submitReview = (orderId, productId) => {
    const key = `${orderId}_${productId}`;
    const form = reviewForms.value[key];
    form.post(route('reviews.store', productId), {
        preserveScroll: true,
        onSuccess: () => {
            delete reviewForms.value[key];
        },
    });
};

// Descargar factura / recibo
const downloadInvoice = (orderId) => {
    window.open(route('orders.invoice', orderId), '_blank');
};
</script>

<template>
    <Head title="Mis Pedidos" />
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold mb-8">📦 Mis Pedidos</h1>

        <div class="lg:grid lg:grid-cols-4 lg:gap-8">
            <!-- BARRA LATERAL DE FILTROS -->
            <aside class="lg:col-span-1 mb-8 lg:mb-0">
                <div class="card space-y-4">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Filtros</h3>

                    <div>
                        <label class="text-xs font-bold block mb-1">Buscar</label>
                        <input v-model="search" type="text" placeholder="ID, tienda, producto..." class="input-field" />
                    </div>

                    <div>
                        <label class="text-xs font-bold block mb-1">Estado</label>
                        <select v-model="status" class="input-field">
                            <option value="">Todos</option>
                            <option value="pending">Pendiente</option>
                            <option value="confirmed">Confirmado</option>
                            <option value="shipped">Enviado</option>
                            <option value="delivered">Entregado</option>
                            <option value="cancelled">Cancelado</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold block mb-1">Desde</label>
                        <input v-model="dateFrom" type="date" class="input-field" />
                    </div>
                    <div>
                        <label class="text-xs font-bold block mb-1">Hasta</label>
                        <input v-model="dateTo" type="date" class="input-field" />
                    </div>

                    <div>
                        <label class="text-xs font-bold block mb-1">Monto (BOB)</label>
                        <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                            <span>Mín: {{ minTotal }}</span>
                            <span>Máx: {{ maxTotal }}</span>
                        </div>
                        <div class="space-y-2">
                            <input v-model.number="minTotal" type="range" min="0" max="5000" step="10" class="w-full h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700 accent-red-600" />
                            <input v-model.number="maxTotal" type="range" min="0" max="5000" step="10" class="w-full h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700 accent-red-600" />
                        </div>
                    </div>

                    <button @click="clearFilters" class="btn-outline text-xs w-full py-2">
                        Limpiar filtros
                    </button>
                </div>
            </aside>

            <!-- LISTA DE PEDIDOS -->
            <div class="lg:col-span-3 space-y-6">
                <div v-if="orders.data.length > 0" class="space-y-4">
                    <div v-for="order in orders.data" :key="order.id" class="card space-y-4">
                        <!-- Cabecera del pedido -->
                        <div class="flex justify-between items-start">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">#{{ order.id }}</span>
                                    <Link :href="route('shop.view', order.shop?.slug)" class="text-sm font-bold text-gray-900 dark:text-white hover:text-red-600 transition-colors">
                                        {{ order.shop?.name }}
                                    </Link>
                                </div>
                                <p class="text-xs text-gray-500">
                                    📅 {{ order.created_at }} &nbsp;|&nbsp; 🛍️ {{ order.items?.length }} producto(s)
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span v-if="order.tax_data" class="text-xs bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 px-2 py-0.5 rounded font-medium">
                                    🧾 Factura
                                </span>
                                <span v-else class="text-xs bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 px-2 py-0.5 rounded font-medium">
                                    📄 Recibo
                                </span>
                                <span :class="{
                                    'badge badge-pending': order.status === 'pending',
                                    'badge badge-info': order.status === 'confirmed',
                                    'badge badge-info': order.status === 'shipped',
                                    'badge badge-success': order.status === 'delivered',
                                    'badge badge-danger': order.status === 'cancelled',
                                }">
                                    {{ order.status }}
                                </span>
                            </div>
                        </div>

                        <!-- Productos del pedido -->
                        <ul class="text-xs space-y-3 border-t dark:border-gray-800 pt-3">
                            <li v-for="item in order.items" :key="item.id" class="flex justify-between items-center">
                                <span>{{ item.product?.title }} <span class="text-gray-400">x{{ item.quantity }}</span></span>
                                <div class="flex items-center gap-3">
                                    <span class="font-mono text-gray-600 dark:text-gray-400">{{ (item.price * item.quantity).toFixed(2) }} BOB</span>
                                    <!-- Sección de reseña solo para pedidos entregados -->
                                    <div v-if="order.status === 'delivered' && !reviewForms[`${order.id}_${item.product_id}`]?._active">
                                        <button @click="startReview(order.id, item.product_id)" class="text-xs text-blue-600 font-bold hover:underline">
                                            ⭐ Reseñar
                                        </button>
                                    </div>
                                    <div v-if="reviewForms[`${order.id}_${item.product_id}`]?._active" class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg flex items-center gap-3">
                                        <div class="flex gap-1">
                                            <button v-for="star in 5" :key="star" @click="reviewForms[`${order.id}_${item.product_id}`].rating = star"
                                                class="text-lg" :class="star <= reviewForms[`${order.id}_${item.product_id}`].rating ? 'text-yellow-500' : 'text-gray-300'">
                                                ★
                                            </button>
                                        </div>
                                        <input v-model="reviewForms[`${order.id}_${item.product_id}`].comment" type="text" placeholder="Comentario (opcional)" class="input-field text-xs w-32" />
                                        <button @click="submitReview(order.id, item.product_id)" :disabled="reviewForms[`${order.id}_${item.product_id}`].processing" class="btn-primary text-xs px-2 py-1">
                                            Enviar
                                        </button>
                                        <button @click="delete reviewForms[`${order.id}_${item.product_id}`]" class="text-xs text-gray-500">Cancelar</button>
                                    </div>
                                </div>
                            </li>
                        </ul>

                        <!-- Pie: total y acciones -->
                        <div class="flex items-center justify-between border-t dark:border-gray-800 pt-3">
                            <div class="flex items-center gap-4">
                                <span class="text-sm font-black text-red-600 dark:text-red-400">
                                    Total: {{ order.total }} BOB
                                </span>
                                <a v-if="order.shop?.phones?.[0]?.phone_number"
                                   :href="`https://wa.me/591${order.shop.phones[0].phone_number.replace(/[\s\-\(\)\+]/g, '')}`"
                                   target="_blank"
                                   class="btn-whatsapp text-xs px-2 py-1">
                                    💬 Contactar
                                </a>
                            </div>
                            <button @click="downloadInvoice(order.id)" class="btn-outline text-xs px-3 py-1.5">
                                📥 {{ order.tax_data ? 'Factura' : 'Recibo' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Vacío -->
                <div v-else class="card text-center py-8">
                    <p class="text-gray-500">No tienes pedidos aún.</p>
                    <Link :href="route('products.index')" class="btn-primary mt-4 inline-block">Ir al Mercado</Link>
                </div>

                <!-- PAGINACIÓN -->
                <div v-if="orders.links && orders.links.length > 3" class="flex justify-center">
                    <nav class="flex gap-1">
                        <template v-for="link in orders.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                class="px-3 py-1.5 text-xs rounded-md"
                                :class="link.active ? 'bg-red-600 text-white' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'"
                            />
                            <span v-else v-html="link.label" class="px-3 py-1.5 text-xs text-gray-400"></span>
                        </template>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>