<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    cartItems: Array,
    addresses: Array,
    taxData: Array,
});

const form = useForm({
    address_id: props.addresses.length ? props.addresses.find(a => a.is_default)?.id : '',
    tax_data_id: props.taxData.length ? props.taxData.find(t => t.is_default)?.id : '',
});

const total = props.cartItems.reduce((sum, item) => {
    return sum + (item.product.price * item.quantity);
}, 0);

const submitOrder = () => {
    form.post(route('orders.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Confirmar Pedido" />
    <div class="max-w-3xl mx-auto py-12 px-4">
        <h1 class="text-2xl font-bold mb-8">📋 Confirmar Pedido</h1>

        <div class="space-y-6">
            <div class="card">
                <h3 class="section-title mb-3">Productos ({{ cartItems.length }})</h3>
                <ul class="space-y-2">
                    <li v-for="item in cartItems" :key="item.id" class="flex justify-between text-xs border-b dark:border-gray-800 pb-2">
                        <span>{{ item.product.title }} (x{{ item.quantity }})</span>
                        <span class="font-bold">{{ (item.product.price * item.quantity).toFixed(2) }} BOB</span>
                    </li>
                </ul>
                <div class="flex justify-between font-bold text-sm pt-4 mt-2 border-t dark:border-gray-800">
                    <span>Total</span>
                    <span class="text-red-600">{{ total.toFixed(2) }} BOB</span>
                </div>
            </div>

            <div class="card">
                <h3 class="section-title mb-3">📍 Dirección de Entrega</h3>
                <div v-if="addresses.length" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label v-for="addr in addresses" :key="addr.id" :class="form.address_id === addr.id ? 'border-red-500 bg-red-50 dark:bg-red-950/20' : 'border-gray-200 dark:border-gray-800'" class="border rounded-lg p-3 cursor-pointer flex items-start gap-2">
                        <input v-model="form.address_id" :value="addr.id" type="radio" class="mt-1" />
                        <div class="text-xs">
                            <p class="font-bold">{{ addr.street_address }}</p>
                            <p class="text-gray-500">{{ addr.city }}, {{ addr.zone }}</p>
                            <p v-if="addr.reference" class="text-gray-400">Ref: {{ addr.reference }}</p>
                        </div>
                    </label>
                </div>
                <p v-else class="text-xs text-gray-500">No tienes direcciones registradas. <Link :href="route('dashboard')" class="text-blue-600">Agrega una</Link></p>
                <p v-if="form.errors.address_id" class="text-red-500 text-[11px] mt-2">{{ form.errors.address_id }}</p>
            </div>

            <div class="card">
                <h3 class="section-title mb-3">💼 Datos de Facturación</h3>
                <div v-if="taxData.length" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label v-for="tax in taxData" :key="tax.id" :class="form.tax_data_id === tax.id ? 'border-red-500 bg-red-50 dark:bg-red-950/20' : 'border-gray-200 dark:border-gray-800'" class="border rounded-lg p-3 cursor-pointer flex items-start gap-2">
                        <input v-model="form.tax_data_id" :value="tax.id" type="radio" class="mt-1" />
                        <div class="text-xs">
                            <p class="font-bold">{{ tax.nit_or_ci }}</p>
                            <p class="text-gray-500">{{ tax.business_name }}</p>
                            <p v-if="tax.alias" class="text-gray-400">{{ tax.alias }}</p>
                        </div>
                    </label>
                </div>
                <p v-else class="text-xs text-gray-500">No tienes datos fiscales registrados. <Link :href="route('dashboard')" class="text-blue-600">Agrega uno</Link></p>
                <p v-if="form.errors.tax_data_id" class="text-red-500 text-[11px] mt-2">{{ form.errors.tax_data_id }}</p>
            </div>

            <div class="flex justify-end">
                <button @click="submitOrder" :disabled="form.processing" class="btn-primary text-sm px-8 py-3">
                    Confirmar Pedido
                </button>
            </div>
        </div>
    </div>
</template>