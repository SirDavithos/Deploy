<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    auth: Object,
});

// Mock del producto seleccionado
const product = ref({
    id: 1,
    name: 'Aguayo Antiguo Tejido a Mano',
    category: 'Textiles y Aguayos',
    price: 350,
    stock: 3,
    image: '🧣',
    rating: 4.9,
    reviews_count: 18,
    artisan: {
        name: 'Doña Juana Quispe',
        specialty: 'Maestra Tejedora en Lana de Alpaca',
        location: 'Pasaje Marina Núñez del Prado, Puesto #12',
        city: 'La Paz',
        phone: '59171234567',
        avatar: '👵🏽'
    },
    description: 'Este aguayo ha sido tejido utilizando la técnica ancestral del telar de cintura (awayo). Cada motivo iconográfico (pallay) cuenta la historia y cosmovisión de la comunidad de Orinoca. Hilos teñidos de forma natural con plantas nativas y cochinilla. Pieza única de alta durabilidad, ideal para uso ceremonial, decorativo o de colección.',
    details: [
        { label: 'Material', value: '100% Lana de Alpaca Seleccionada' },
        { label: 'Dimensiones', value: '120 cm x 115 cm' },
        { label: 'Tiempo de elaboración', value: '2 meses y medio' },
        { label: 'Origen', value: 'Ayllu Sikuya, Oruro / Taller en La Paz' }
    ]
});

// Galería emulada (Iconos alternativos decorativos de la misma temática)
const gallery = ['🧣', '🧵', '🐑'];
const activeImageIndex = ref(0);

// Control de cantidad
const quantity = ref(1);

const incrementQuantity = () => {
    if (quantity.value < product.value.stock) quantity.value++;
};

const decrementQuantity = () => {
    if (quantity.value > 1) quantity.value--;
};

// Cálculo dinámico de subtotal
const totalPrice = computed(() => {
    return product.value.price * quantity.value;
});

// Acción de añadir al carrito
const addToCart = () => {
    alert(`🛒 ¡Añadido al carrito con éxito!\nProducto: ${product.value.name}\nCantidad: ${quantity.value} unidades\nTotal: ${totalPrice.value} BOB`);
};

const safeRoute = (routeName) => {
    try { return route(routeName); } catch (e) { return '#'; }
};
</script>

<template>
    <Head :title="`${product.name} - Punto Boliviano`" />

    <div class="min-h-screen bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100 transition-colors duration-200">
        <nav class="border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-800 dark:bg-gray-900 sticky top-0 z-50 shadow-sm">
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <Link :href="safeRoute('welcome')" class="flex items-center space-x-2">
                    <span class="text-2xl font-black tracking-wider text-red-600 dark:text-red-500">PUNTO</span>
                    <span class="bg-red-600 px-2 py-0.5 text-sm font-bold text-white rounded dark:bg-red-500">BOLIVIANO</span>
                </Link>

                <div class="flex items-center space-x-4">
                    <Link :href="safeRoute('products.index')" class="text-xs font-bold text-gray-600 dark:text-gray-400 hover:text-red-600">Volver al Catálogo</Link>
                    <Link :href="safeRoute('dashboard')" class="text-xs font-bold text-gray-600 dark:text-gray-400 hover:text-red-600">Mi Panel</Link>
                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-2 lg:gap-x-12">
                
                <div class="space-y-4">
                    <div class="aspect-h-1 aspect-w-1 w-full bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 flex items-center justify-center text-9xl p-16 shadow-sm">
                        {{ gallery[activeImageIndex] }}
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <button 
                            v-for="(img, index) in gallery" 
                            :key="index"
                            @click="activeImageIndex = index"
                            :class="[activeImageIndex === index ? 'border-red-600 ring-2 ring-red-600/20' : 'border-gray-200 dark:border-gray-800', 'aspect-h-1 aspect-w-1 bg-white dark:bg-gray-900 rounded-xl border flex items-center justify-center text-4xl p-4 transition-all shadow-sm']"
                        >
                            {{ img }}
                        </button>
                    </div>
                </div>

                <div class="mt-8 lg:mt-0 space-y-6">
                    <div>
                        <span class="text-xs font-extrabold text-red-600 dark:text-red-400 uppercase tracking-widest">{{ product.category }}</span>
                        <h1 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white sm:text-3xl mt-1">{{ product.name }}</h1>
                        
                        <div class="flex items-center space-x-2 mt-2">
                            <span class="text-sm font-bold text-amber-500">⭐ {{ product.rating }}</span>
                            <span class="text-xs text-gray-400">({{ product.reviews_count }} valoraciones de compradores)</span>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 flex items-center justify-between shadow-sm">
                        <div>
                            <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Precio Unitario</p>
                            <p class="text-3xl font-black text-red-600 dark:text-red-500">{{ product.price }} BOB</p>
                        </div>
                        <span :class="[product.stock > 0 ? 'bg-green-50 text-green-700 dark:bg-green-950/40 dark:text-green-400' : 'bg-red-50 text-red-700', 'inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold ring-1 ring-inset ring-current']">
                            {{ product.stock > 0 ? `${product.stock} disponibles` : 'Agotado' }}
                        </span>
                    </div>

                    <div class="space-y-2">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-wider">Reseña de la Obra</h3>
                        <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300">{{ product.description }}</p>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-800 pt-4 space-y-2">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-wider">Detalles Técnicos</h3>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2">
                            <div v-for="detail in product.details" :key="detail.label" class="border-b border-gray-100 dark:border-gray-900 py-2 flex justify-between sm:block">
                                <dt class="text-xs font-medium text-gray-400">{{ detail.label }}</dt>
                                <dd class="text-xs font-bold text-gray-900 dark:text-white mt-0.5">{{ detail.value }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="bg-gray-100 dark:bg-gray-900 rounded-2xl p-4 flex items-start space-x-4 border border-gray-200/40 dark:border-gray-800">
                        <span class="text-4xl p-2 bg-white dark:bg-gray-950 rounded-xl shadow-sm">{{ product.artisan.avatar }}</span>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Creado por</h4>
                            <p class="text-sm font-bold text-gray-900 dark:text-white mt-0.5">{{ product.artisan.name }}</p>
                            <p class="text-xs text-red-600 dark:text-red-400 font-medium">{{ product.artisan.specialty }}</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">📍 {{ product.artisan.location }} ({{ product.artisan.city }})</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-800 pt-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-950 overflow-hidden shadow-sm">
                                <button type="button" @click="decrementQuantity" class="px-3 py-2 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-900 font-black">-</button>
                                <span class="px-4 text-sm font-bold w-12 text-center">{{ quantity }}</span>
                                <button type="button" @click="incrementQuantity" class="px-3 py-2 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-900 font-black">+</button>
                            </div>

                            <div class="text-right">
                                <span class="text-xs text-gray-400 block font-medium">Subtotal Estimado</span>
                                <span class="text-2xl font-black text-gray-900 dark:text-white">{{ totalPrice }} BOB</span>
                            </div>
                        </div>

                        <button 
                            @click="addToCart"
                            :disabled="product.stock === 0"
                            class="w-full rounded-2xl bg-red-600 text-white font-bold py-3.5 text-sm hover:bg-red-500 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ product.stock > 0 ? 'Añadir a mi Orden de Compra' : 'Agotado de Momento' }}
                        </button>
                    </div>

                </div>
            </div>
        </main>
    </div>
</template>