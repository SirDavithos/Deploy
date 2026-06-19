<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

defineProps({ shop: Object });
</script>

<template>
    <Head title="Admin - Tienda" />
    <div class="space-y-6">
        <h1 class="text-2xl font-bold">{{ shop.name }}</h1>
        <div class="card">
            <p><strong>Dueño:</strong> {{ shop.owner?.first_name }} {{ shop.owner?.paternal_last_name }}</p>
            <p><strong>Estado:</strong> {{ shop.status }}</p>
            <p><strong>Teléfono:</strong> {{ shop.phones?.[0]?.phone_number ?? 'No registrado' }}</p>
            <p><strong>Dirección:</strong> {{ shop.addresses?.[0]?.street_address }}, {{ shop.addresses?.[0]?.zone }}, {{ shop.addresses?.[0]?.city }}</p>
            <p><strong>Productos:</strong> {{ shop.products?.length ?? 0 }}</p>
            <p><strong>Pedidos:</strong> {{ shop.orders?.length ?? 0 }}</p>
        </div>
    </div>
</template>