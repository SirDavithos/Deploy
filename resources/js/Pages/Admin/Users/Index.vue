<script setup>
import { Head, Link, router } from '@inertiajs/vue3'; // ← importar router
import { ref, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    users: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const role = ref(props.filters.role || '');

watch([search, status, role], () => {
    const query = {};
    if (search.value) query.search = search.value;
    if (status.value) query.status = status.value;
    if (role.value) query.role = role.value;
    router.get(route('admin.users.index'), query, { preserveState: true, replace: true });
});

const exportPdf = () => {
    const params = new URLSearchParams();
    if (search.value) params.append('search', search.value);
    if (status.value) params.append('status', status.value);
    if (role.value) params.append('role', role.value);
    window.location.href = route('admin.users.export-pdf') + '?' + params.toString();
};
</script>

<template>
    <Head title="Admin - Usuarios" />
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Gestión de Usuarios</h1>
            <button @click="exportPdf" class="btn-primary flex items-center gap-2">
                <span>📄</span> Exportar PDF
            </button>
        </div>

        <!-- Filtros avanzados -->
        <div class="card mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="text-xs font-bold block mb-1">Buscar</label>
                    <input v-model="search" type="text" placeholder="Nombre, email, CI..." class="input-field" />
                </div>
                <div>
                    <label class="text-xs font-bold block mb-1">Estado</label>
                    <select v-model="status" class="input-field">
                        <option value="">Todos</option>
                        <option value="active">Activo</option>
                        <option value="inactive">Inactivo</option>
                        <option value="suspended">Suspendido</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold block mb-1">Rol</label>
                    <select v-model="role" class="input-field">
                        <option value="">Todos</option>
                        <option value="admin">Admin</option>
                        <option value="artisan">Artesano</option>
                        <option value="customer">Comprador</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button @click="search = ''; status = ''; role = ''" class="btn-outline text-xs px-3 py-2">Limpiar filtros</button>
                </div>
            </div>
        </div>

        <!-- Tabla de usuarios -->
        <div class="card overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b dark:border-gray-800">
                        <th class="p-2 text-left">ID</th>
                        <th class="p-2 text-left">Nombre</th>
                        <th class="p-2 text-left">Email</th>
                        <th class="p-2 text-left">CI</th>
                        <th class="p-2 text-left">Estado</th>
                        <th class="p-2 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users.data" :key="user.id" class="border-b dark:border-gray-800">
                        <td class="p-2">{{ user.id }}</td>
                        <td class="p-2">{{ user.first_name }} {{ user.paternal_last_name }}</td>
                        <td class="p-2">{{ user.email }}</td>
                        <td class="p-2">{{ user.ci_number }} {{ user.ci_extension }}</td>
                        <td class="p-2">
                            <span :class="user.status === 'active' ? 'badge-success' : 'badge-pending'">{{ user.status }}</span>
                        </td>
                        <td class="p-2">
                            <Link :href="route('admin.users.edit', user.id)" class="text-blue-600 font-bold hover:underline">Editar</Link>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div v-if="users.links" class="p-2 flex gap-2">
                <Link v-for="link in users.links" :key="link.label" :href="link.url || '#'" v-html="link.label" 
                      class="px-2 py-1 text-xs rounded" :class="{ 'bg-red-600 text-white': link.active, 'hover:bg-gray-100 dark:hover:bg-gray-800': !link.active }" />
            </div>
        </div>
    </div>
</template>