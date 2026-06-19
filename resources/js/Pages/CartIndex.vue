<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    auth: Object,
});

// --- ESTADO DE DATOS (Sincronizado) ---
const cartItems = ref([]);
const savedAddresses = ref([]);
const billingData = ref({ nit: '', razonSocial: '' });

// Estados de selección para la orden
const selectedAddressId = ref(null);
const deliveryMethod = ref('delivery'); // 'delivery' (A domicilio) o 'pickup' (Recojo en puesto del pasaje)

// --- CARGA DE DATOS DESDE EL SISTEMA DE PERSISTENCIA ---
onMounted(() => {
    // 1. Cargar Direcciones previamente guardadas por el cliente
    savedAddresses.value = localStorage.getItem('pb_addresses')
        ? JSON.parse(localStorage.getItem('pb_addresses'))
        : [
            { id: 1, title: 'Casa Sopocachi', city: 'La Paz', zone: 'Sopocachi', street: 'Av. 20 de Octubre #2412', extra: 'Edificio Los Andes' },
            { id: 2, title: 'Oficina Centro', city: 'La Paz', zone: 'Centro', street: 'Calle Potosí #450', extra: 'Frente al BCB' }
          ];
    
    if (savedAddresses.value.length > 0) {
        selectedAddressId.value = savedAddresses.value[0].id;
    }

    // 2. Cargar Datos Fiscales
    if (localStorage.getItem('pb_billing')) {
        const billing = JSON.parse(localStorage.getItem('pb_billing'));
        billingData.value.nit = billing.nit || '';
        billingData.value.razonSocial = billing.razonSocial || '';
    }

    // 3. Inicializar el carrito con las muestras de los pasos anteriores
    cartItems.value = [
        { id: 1, name: 'Aguayo Antiguo Tejido a Mano', artisan: 'Doña Juana Quispe', price: 350, quantity: 1, image: '🧣' },
        { id: 2, name: 'Anillo de Plata 950 con Aguamarina', artisan: 'Artesanías Mamani', price: 180, quantity: 2, image: '💍' }
    ];
});

// --- ACCIONES DEL CARRITO ---
const updateQuantity = (id, change) => {
    cartItems.value = cartItems.value.map(item => {
        if (item.id === id) {
            const newQty = item.quantity + change;
            return newQty > 0 ? { ...item, quantity: newQty } : item;
        }
        return item;
    });
};

const removeItem = (id) => {
    cartItems.value = cartItems.value.filter(item => item.id !== id);
};

// --- CÁLCULOS FINALES ---
const subtotal = computed(() => {
    return cartItems.value.reduce((acc, item) => acc + (item.price * item.quantity), 0);
});

const deliveryCost = computed(() => {
    if (deliveryMethod.value === 'pickup') return 0;
    // Tarifa estándar La Paz/El Alto
    return 15; 
});

const total = computed(() => {
    return subtotal.value + deliveryCost.value;
});

// --- DIRECCIÓN SELECCIONADA ACTUALMENTE ---
const currentAddress = computed(() => {
    return savedAddresses.value.find(a => a.id === selectedAddressId.value);
});

// --- PROCESAR / FINALIZAR LA COMPRA ---
const checkout = () => {
    if (cartItems.value.length === 0) return;
    
    if (deliveryMethod.value === 'delivery' && !selectedAddressId.value) {
        alert('Por favor, selecciona una dirección para el envío.');
        return;
    }

    // Simulación de empaquetado de la nueva orden para el historial
    const existingOrders = localStorage.getItem('pb_orders') ? JSON.parse(localStorage.getItem('pb_orders')) : [];
    
    cartItems.value.forEach(item => {
        existingOrders.unshift({
            id: `PB-${Math.floor(1000 + Math.random() * 9000)}`,
            artisan: item.artisan,
            product: item.name,
            price: `${item.price * item.quantity} BOB`,
            date: '11 Jun, 2026',
            status: 'Pendiente',
            phone: '59171234567'
        });
    });

    // Guardar en el historial global del usuario y vaciar carrito
    localStorage.setItem('pb_orders', JSON.stringify(existingOrders));
    cartItems.value = [];
    
    alert('🎉 ¡Orden de compra procesada con éxito!\n\nTus pedidos han sido enviados a los respectivos artesanos. Ya puedes coordinar la entrega desde tu panel.');
};

const safeRoute = (routeName) => {
    try { return route(routeName); } catch (e) { return '#'; }
};
</script>

<template>
    <Head title="Mi Carrito - Punto Boliviano" />

    <div class="min-h-screen bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100 transition-colors duration-200">
        <nav class="border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-800 dark:bg-gray-900 sticky top-0 z-50 shadow-sm">
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <Link :href="safeRoute('welcome')" class="flex items-center space-x-2">
                    <span class="text-2xl font-black tracking-wider text-red-600 dark:text-red-500">PUNTO</span>
                    <span class="bg-red-600 px-2 py-0.5 text-sm font-bold text-white rounded dark:bg-red-500">BOLIVIANO</span>
                </Link>

                <div class="flex items-center space-x-4">
                    <Link :href="safeRoute('products.index')" class="text-xs font-bold text-gray-600 dark:text-gray-400 hover:text-red-600">Catálogo</Link>
                    <Link :href="safeRoute('dashboard')" class="text-xs font-bold text-gray-600 dark:text-gray-400 hover:text-red-600">Mi Panel</Link>
                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white mb-8">Bolsa de Adquisiciones</h1>

            <div v-if="cartItems.length === 0" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-16 text-center">
                <span class="text-5xl">🛒</span>
                <h2 class="text-base font-bold mt-4">Tu bolsa está vacía</h2>
                <p class="text-xs text-gray-400 mt-1 mb-6">Explora el mercado artesanal para añadir piezas únicas.</p>
                <Link :href="safeRoute('products.index')" class="inline-flex items-center rounded-xl bg-red-600 px-4 py-2 text-xs font-bold text-white hover:bg-red-500">Volver al Catálogo</Link>
            </div>

            <div v-else class="grid grid-cols-1 lg:grid-cols-12 lg:gap-x-8 lg:items-start">
                
                <section class="lg:col-span-7 space-y-6">
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden">
                        <ul role="list" class="divide-y divide-gray-100 dark:divide-gray-800">
                            <li v-for="item in cartItems" :key="item.id" class="p-6 flex items-start gap-4">
                                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-950 rounded-xl flex items-center justify-center text-3xl flex-shrink-0 shadow-sm">
                                    {{ item.image }}
                                </div>
                                
                                <div class="flex-1 min-w-0 space-y-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ item.name }}</h3>
                                        <button @click="removeItem(item.id)" class="text-xs text-gray-400 hover:text-red-500 font-semibold">Quitar</button>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Artesano: {{ item.artisan }}</p>
                                    <p class="text-sm font-black text-gray-900 dark:text-white pt-1">{{ item.price }} BOB</p>
                                </div>

                                <div class="flex items-center border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-950">
                                    <button @click="updateQuantity(item.id, -1)" class="px-2.5 py-1 text-gray-500 hover:bg-gray-100 font-bold text-xs">-</button>
                                    <span class="px-2 text-xs font-bold w-8 text-center">{{ item.quantity }}</span>
                                    <button @click="updateQuantity(item.id, 1)" class="px-2.5 py-1 text-gray-500 hover:bg-gray-100 font-bold text-xs">+</button>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm space-y-4">
                        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Forma de Entrega</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label :class="[deliveryMethod === 'delivery' ? 'border-red-600 ring-2 ring-red-600/10 bg-red-50/10' : 'border-gray-200 dark:border-gray-800', 'border rounded-xl p-4 flex items-start gap-3 cursor-pointer transition-all shadow-sm']">
                                <input type="radio" v-model="deliveryMethod" value="delivery" class="text-red-600 focus:ring-red-500 mt-0.5" />
                                <div>
                                    <span class="text-xs font-bold block">Envío a Domicilio</span>
                                    <span class="text-[11px] text-gray-400">Entrega en La Paz / El Alto (+15 BOB)</span>
                                </div>
                            </label>
                            <label :class="[deliveryMethod === 'pickup' ? 'border-red-600 ring-2 ring-red-600/10 bg-red-50/10' : 'border-gray-200 dark:border-gray-800', 'border rounded-xl p-4 flex items-start gap-3 cursor-pointer transition-all shadow-sm']">
                                <input type="radio" v-model="deliveryMethod" value="pickup" class="text-red-600 focus:ring-red-500 mt-0.5" />
                                <div>
                                    <span class="text-xs font-bold block">Recojo del Pasaje</span>
                                    <span class="text-[11px] text-gray-400">Coordinar retiro del puesto directo (Gratis)</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div v-if="deliveryMethod === 'delivery'" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Dirección Seleccionada</h3>
                            <Link :href="safeRoute('dashboard')" class="text-xs text-red-600 font-bold hover:underline">Gestionar direcciones</Link>
                        </div>
                        
                        <div v-if="savedAddresses.length === 0" class="text-xs text-amber-600 bg-amber-50 dark:bg-amber-950/20 p-3 rounded-xl border border-amber-200/40">
                            No tienes direcciones registradas en tu panel. Ve a tu cuenta para añadir una.
                        </div>
                        <div v-else class="grid grid-cols-1 gap-2">
                            <select v-model="selectedAddressId" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-950 text-xs focus:ring-red-500 mb-2">
                                <option v-for="addr in savedAddresses" :key="addr.id" :value="addr.id">{{ addr.title }} ({{ addr.zone }})</option>
                            </select>
                            
                            <div v-if="currentAddress" class="p-4 bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-900 rounded-xl text-xs space-y-1">
                                <p class="font-bold text-gray-800 dark:text-gray-200">{{ currentAddress.street }}</p>
                                <p class="text-gray-500">Ciudad: {{ currentAddress.city }} — Zona: {{ currentAddress.zone }}</p>
                                <p v-if="currentAddress.extra" class="text-gray-400 italic">Ref: {{ currentAddress.extra }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="lg:col-span-5 mt-6 lg:mt-0">
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm space-y-6 sticky top-28">
                        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Resumen del Pedido</h3>
                        
                        <div class="space-y-3 text-xs border-b border-gray-100 dark:border-gray-800 pb-4">
                            <div class="flex justify-between text-gray-500">
                                <span>Subtotal de artículos</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ subtotal }} BOB</span>
                            </div>
                            <div class="flex justify-between text-gray-500">
                                <span>Logística de entrega</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ deliveryCost === 0 ? 'Gratis' : `${deliveryCost} BOB` }}</span>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-950 p-4 rounded-xl border border-gray-100 dark:border-gray-900 space-y-1 text-xs">
                            <h4 class="font-bold text-[11px] uppercase tracking-wider text-gray-400 mb-1">Datos para Factura</h4>
                            <p class="text-gray-600 dark:text-gray-300">NIT/CI: <span class="font-bold font-mono">{{ billingData.nit || 'Sin NIT (S/N)' }}</span></p>
                            <p class="text-gray-600 dark:text-gray-300">Razón Social: <span class="font-bold">{{ billingData.razonSocial || 'Particular' }}</span></p>
                        </div>

                        <div class="flex justify-between items-baseline">
                            <span class="text-sm font-bold">Total a liquidar:</span>
                            <span class="text-3xl font-black text-red-600 dark:text-red-500">{{ total }} BOB</span>
                        </div>

                        <button 
                            @click="checkout"
                            class="w-full rounded-2xl bg-gray-900 text-white dark:bg-white dark:text-gray-900 font-bold py-4 text-sm hover:opacity-90 shadow-sm transition-all"
                        >
                            Confirmar y Enviar a los Artesanos
                        </button>
                    </div>
                </section>

            </div>
        </main>
    </div>
</template>