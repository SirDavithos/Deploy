<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    cartItems: Array,
});

const editingItem = ref(null);
const editForm = useForm({ quantity: 1 });

const startEdit = (item) => {
    editingItem.value = item.id;
    editForm.quantity = item.quantity;
};

const cancelEdit = () => {
    editingItem.value = null;
};

const updateItem = (itemId) => {
    editForm.patch(route('cart.update', itemId), {
        preserveScroll: true,
        onSuccess: () => {
            editingItem.value = null;
        },
    });
};

const removeItem = (itemId) => {
    if (confirm('¿Eliminar este producto del carrito?')) {
        useForm({}).delete(route('cart.destroy', itemId), { preserveScroll: true });
    }
};

// Calcular total
const total = props.cartItems.reduce((sum, item) => {
    return sum + (item.product.price * item.quantity);
}, 0);
</script>

<template>
    <Head title="Carrito de Compras" />
    <div class="max-w-4xl mx-auto py-12 px-4">
        <h1 class="text-2xl font-bold mb-8">🛒 Tu Carrito</h1>

        <div v-if="cartItems.length === 0" class="card text-center py-8">
            <p class="text-gray-500">Tu carrito está vacío.</p>
            <Link :href="route('products.index')" class="btn-primary mt-4 inline-block">Ir al Mercado</Link>
        </div>

        <div v-else class="space-y-6">
            <div class="card space-y-4">
                <div v-for="item in cartItems" :key="item.id" class="flex items-center justify-between border-b dark:border-gray-800 pb-4 last:border-0 last:pb-0">
                    <div class="flex items-center gap-4 flex-1">
                        <img v-if="item.product.images && item.product.images.length" :src="'/storage/' + item.product.images[0]" class="h-16 w-16 object-cover rounded" />
                        <div v-else class="h-16 w-16 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center text-2xl">📦</div>
                        <div>
                            <h3 class="font-bold text-sm">{{ item.product.title }}</h3>
                            <p class="text-xs text-gray-500">{{ item.product.shop?.name }}</p>
                            <p class="text-sm font-bold text-red-600 mt-1">{{ item.product.price }} BOB</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <!-- Modo edición de cantidad -->
                        <div v-if="editingItem === item.id" class="flex items-center gap-2">
                            <input v-model="editForm.quantity" type="number" min="1" class="input-field w-16 text-center text-xs" />
                            <button @click="updateItem(item.id)" :disabled="editForm.processing" class="btn-dark text-xs px-2 py-1">OK</button>
                            <button @click="cancelEdit" class="text-xs text-gray-500">Cancelar</button>
                        </div>
                        <!-- Modo vista -->
                        <template v-else>
                            <span class="text-xs font-mono bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">{{ item.quantity }} ud.</span>
                            <button @click="startEdit(item)" class="text-xs text-blue-600">Editar</button>
                            <button @click="removeItem(item.id)" class="text-xs text-red-500">Eliminar</button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Total y acción -->
            <div class="card flex justify-between items-center">
                <div>
                    <span class="text-sm font-bold">Total:</span>
                    <span class="text-2xl font-black text-red-600 ml-2">{{ total.toFixed(2) }} BOB</span>
                </div>
                <Link :href="route('checkout.index')" class="btn-primary text-sm px-6 py-2.5">
                    Proceder al Pago
                </Link>
            </div>
        </div>
    </div>
</template>