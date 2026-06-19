<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    shops: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

watch([search, status], () => {
    router.get(route('admin.shops.index'), {
        search: search.value || null,
        status: status.value || null,
    }, { preserveState: true, replace: true });
});

const approve = (shopId) => {
    if (confirm('¿Aprobar esta tienda?')) {
        router.patch(route('admin.shops.approve', shopId), {}, { preserveScroll: true });
    }
};

const reject = (shopId) => {
    if (confirm('¿Rechazar esta tienda?')) {
        router.patch(route('admin.shops.reject', shopId), {}, { preserveScroll: true });
    }
};

// Exportar PDF con los filtros actuales
const exportPdf = () => {
    const params = new URLSearchParams();
    if (search.value) params.append('search', search.value);
    if (status.value) params.append('status', status.value);
    window.location.href = route('admin.shops.export-pdf') + '?' + params.toString();
};
</script>

<template>
    <Head title="Admin - Tiendas" />
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestión de Tiendas</h1>
            <button @click="exportPdf" class="btn-primary flex items-center gap-2">
                <span>📄</span> Exportar PDF
            </button>
        </div>

        <!-- Filtros -->
        <div class="card">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs font-bold block mb-1">Buscar</label>
                    <input v-model="search" type="text" placeholder="Nombre, dueño..." class="input-field" />
                </div>
                <div>
                    <label class="text-xs font-bold block mb-1">Estado</label>
                    <select v-model="status" class="input-field">
                        <option value="">Todos</option>
                        <option value="pending">Pendiente</option>
                        <option value="approved">Aprobada</option>
                        <option value="rejected">Rechazada</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button @click="search = ''; status = ''" class="btn-outline text-xs px-3 py-2">Limpiar filtros</button>
                </div>
            </div>
        </div>

        <!-- Tabla de tiendas -->
        <div class="card overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b dark:border-gray-800">
                        <th class="p-2 text-left">ID</th>
                        <th class="p-2 text-left">Nombre</th>
                        <th class="p-2 text-left">Dueño</th>
                        <th class="p-2 text-left">Estado</th>
                        <th class="p-2 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="shop in shops.data" :key="shop.id" class="border-b dark:border-gray-800">
                        <td class="p-2">{{ shop.id }}</td>
                        <td class="p-2">{{ shop.name }}</td>
                        <td class="p-2">{{ shop.owner?.first_name }} {{ shop.owner?.paternal_last_name }}</td>
                        <td class="p-2">
                            <span :class="{
                                'badge-pending': shop.status === 'pending',
                                'badge-success': shop.status === 'approved',
                                'bg-red-100 text-red-800': shop.status === 'rejected',
                            }" class="badge">{{ shop.status }}</span>
                        </td>
                        <td class="p-2 flex gap-2">
                            <Link :href="route('admin.shops.show', shop.id)" class="text-blue-600 font-bold hover:underline">Ver</Link>
                            <button v-if="shop.status === 'pending'" @click="approve(shop.id)" class="text-green-600 font-bold hover:underline">Aprobar</button>
                            <button v-if="shop.status === 'pending'" @click="reject(shop.id)" class="text-red-600 font-bold hover:underline">Rechazar</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Paginación -->
            <div v-if="shops.links" class="p-2 flex gap-2">
                <Link v-for="link in shops.links" :key="link.label" :href="link.url || '#'" v-html="link.label"
                      class="px-2 py-1 text-xs rounded"
                      :class="{ 'bg-red-600 text-white': link.active, 'hover:bg-gray-100 dark:hover:bg-gray-800': !link.active }" />
            </div>
        </div>
    </div>
</template>