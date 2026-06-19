<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    orders: Array,
});
</script>

<template>
    <Head title="Mis Pedidos" />
    <div class="max-w-4xl mx-auto py-12 px-4">
        <h1 class="text-2xl font-bold mb-8">📦 Mis Pedidos</h1>

        <div v-if="orders.length === 0" class="card text-center py-8">
            <p class="text-gray-500">No tienes pedidos aún.</p>
            <Link :href="route('products.index')" class="btn-primary mt-4 inline-block">Ir al Mercado</Link>
        </div>

        <div v-else class="space-y-4">
            <div v-for="order in orders" :key="order.id" class="card space-y-2">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="text-xs font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">#{{ order.id }}</span>
                        <span class="ml-2 text-xs font-bold">{{ order.shop?.name }}</span>
                    </div>
                    <span :class="{
                        'badge-pending': order.status === 'pending',
                        'badge-success': order.status === 'delivered' || order.status === 'confirmed',
                    }">{{ order.status }}</span>
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
            </div>
        </div>
    </div>
</template>