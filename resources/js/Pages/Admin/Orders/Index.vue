<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    orders: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

watch([search, status], () => {
    router.get(route('admin.orders.index'), {
        search: search.value || null,
        status: status.value || null,
    }, { preserveState: true, replace: true });
});

// Exportar PDF con los filtros actuales
const exportPdf = () => {
    const params = new URLSearchParams();
    if (search.value) params.append('search', search.value);
    if (status.value) params.append('status', status.value);
    window.location.href = route('admin.orders.export-pdf') + '?' + params.toString();
};
</script>

<template>
    <Head title="Admin - Pedidos" />
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestión de Pedidos</h1>
            <button @click="exportPdf" class="btn-primary flex items-center gap-2">
                <span>📄</span> Exportar PDF
            </button>
        </div>

        <!-- Filtros -->
        <div class="card">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs font-bold block mb-1">Buscar</label>
                    <input v-model="search" type="text" placeholder="ID, tienda, comprador..." class="input-field" />
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
                <div class="flex items-end">
                    <button @click="search = ''; status = ''" class="btn-outline text-xs px-3 py-2">Limpiar filtros</button>
                </div>
            </div>
        </div>

        <!-- Tabla de pedidos -->
        <div class="card overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b dark:border-gray-800">
                        <th class="p-2 text-left">ID</th>
                        <th class="p-2 text-left">Tienda</th>
                        <th class="p-2 text-left">Comprador</th>
                        <th class="p-2 text-left">Total</th>
                        <th class="p-2 text-left">Estado</th>
                        <th class="p-2 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="order in orders.data" :key="order.id" class="border-b dark:border-gray-800">
                        <td class="p-2">{{ order.id }}</td>
                        <td class="p-2">{{ order.shop?.name }}</td>
                        <td class="p-2">{{ order.buyer?.first_name }} {{ order.buyer?.paternal_last_name }}</td>
                        <td class="p-2">{{ order.total }} BOB</td>
                        <td class="p-2">
                            <span :class="{
                                'badge-pending': order.status === 'pending',
                                'badge-success': order.status === 'delivered' || order.status === 'confirmed'
                            }" class="badge">{{ order.status }}</span>
                        </td>
                        <td class="p-2">
                            <Link :href="route('admin.orders.show', order.id)" class="text-blue-600 font-bold hover:underline">Ver</Link>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Paginación -->
            <div v-if="orders.links" class="p-2 flex gap-2">
                <Link v-for="link in orders.links" :key="link.label" :href="link.url || '#'" v-html="link.label"
                      class="px-2 py-1 text-xs rounded"
                      :class="{ 'bg-red-600 text-white': link.active, 'hover:bg-gray-100 dark:hover:bg-gray-800': !link.active }" />
            </div>
        </div>
    </div>
</template>