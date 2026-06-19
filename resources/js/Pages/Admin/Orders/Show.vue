<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    order: Object,
});

const updateStatus = (newStatus) => {
    if (confirm(`¿Cambiar el estado del pedido a "${newStatus}"?`)) {
        router.patch(route('admin.orders.update', props.order.id), {
            status: newStatus,
        }, {
            preserveScroll: true,
        });
    }
};

const deleteOrder = () => {
    if (confirm('¿Eliminar este pedido? Esta acción no se puede deshacer.')) {
        router.delete(route('admin.orders.destroy', props.order.id), {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route('admin.orders.index'));
            },
        });
    }
};
</script>

<template>
    <Head title="Admin - Pedido #{{ order.id }}" />
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pedido #{{ order.id }}</h1>
            <div class="flex gap-2">
                <Link :href="route('admin.orders.index')" class="btn-outline text-xs px-3 py-1.5">
                    ← Volver
                </Link>
                <button @click="deleteOrder" class="btn-outline text-xs px-3 py-1.5 text-red-500 border-red-300 hover:bg-red-50">
                    🗑️ Eliminar
                </button>
            </div>
        </div>

        <!-- Datos generales -->
        <div class="card grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase mb-2">Información del Pedido</p>
                <div class="space-y-1 text-sm">
                    <p><strong>ID:</strong> {{ order.id }}</p>
                    <p><strong>Estado:</strong>
                        <span :class="{
                            'badge-pending': order.status === 'pending',
                            'badge-success': order.status === 'delivered' || order.status === 'confirmed'
                        }" class="badge">{{ order.status }}</span>
                    </p>
                    <p><strong>Total:</strong> <span class="text-red-600 font-bold">{{ order.total }} BOB</span></p>
                    <p><strong>Fecha:</strong> {{ order.created_at }}</p>
                </div>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase mb-2">Detalles de la Compra</p>
                <div class="space-y-1 text-sm">
                    <p><strong>Tienda:</strong> {{ order.shop?.name }}</p>
                    <p><strong>Comprador:</strong> {{ order.buyer?.first_name }} {{ order.buyer?.paternal_last_name }}</p>
                    <p v-if="order.tax_data"><strong>Facturación:</strong> {{ order.tax_data.nit_or_ci }} – {{ order.tax_data.business_name }}</p>
                    <p v-if="order.address"><strong>Dirección:</strong> {{ order.address.street_address }}, {{ order.address.zone }}, {{ order.address.city }}</p>
                </div>
            </div>
        </div>

        <!-- Cambiar estado -->
        <div class="card">
            <h3 class="text-sm font-bold mb-3">⚡ Cambiar Estado del Pedido</h3>
            <div class="flex flex-wrap gap-2">
                <button v-if="order.status !== 'confirmed'" @click="updateStatus('confirmed')"
                        class="btn-outline text-xs px-3 py-1.5 bg-green-50 hover:bg-green-100 border-green-300 text-green-700">
                    ✅ Confirmar
                </button>
                <button v-if="order.status !== 'shipped'" @click="updateStatus('shipped')"
                        class="btn-outline text-xs px-3 py-1.5 bg-blue-50 hover:bg-blue-100 border-blue-300 text-blue-700">
                    🚚 Marcar Enviado
                </button>
                <button v-if="order.status !== 'delivered'" @click="updateStatus('delivered')"
                        class="btn-outline text-xs px-3 py-1.5 bg-purple-50 hover:bg-purple-100 border-purple-300 text-purple-700">
                    📦 Marcar Entregado
                </button>
                <button v-if="order.status !== 'cancelled'" @click="updateStatus('cancelled')"
                        class="btn-outline text-xs px-3 py-1.5 bg-red-50 hover:bg-red-100 border-red-300 text-red-700">
                    ❌ Cancelar
                </button>
                <button v-if="order.status !== 'pending'" @click="updateStatus('pending')"
                        class="btn-outline text-xs px-3 py-1.5 bg-yellow-50 hover:bg-yellow-100 border-yellow-300 text-yellow-700">
                    ⏳ Volver a Pendiente
                </button>
            </div>
        </div>

        <!-- Productos del pedido -->
        <div class="card">
            <h3 class="text-sm font-bold mb-3">🛒 Productos</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b dark:border-gray-800">
                            <th class="p-2 text-left">Producto</th>
                            <th class="p-2 text-left">Cantidad</th>
                            <th class="p-2 text-left">Precio Unit.</th>
                            <th class="p-2 text-left">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in order.items" :key="item.id" class="border-b dark:border-gray-800">
                            <td class="p-2">{{ item.product?.title || 'Producto eliminado' }}</td>
                            <td class="p-2">{{ item.quantity }}</td>
                            <td class="p-2">{{ item.price }} BOB</td>
                            <td class="p-2">{{ (item.quantity * item.price).toFixed(2) }} BOB</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="text-right mt-2 font-bold text-lg text-red-600">
                TOTAL: {{ order.total }} BOB
            </div>
        </div>

        <!-- Factura -->
        <div v-if="order.tax_data" class="card text-center">
            <Link :href="route('orders.invoice', order.id)" class="btn-primary text-xs px-4 py-2">
                📄 Descargar Factura
            </Link>
        </div>
    </div>
</template>