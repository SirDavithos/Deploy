<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    auth: Object,
});

// Categorías del mercado artesanal
const categories = ['Todas', 'Textiles y Aguayos', 'Joyería y Plata', 'Tallados y Máscaras', 'Cerámica'];
const selectedCategory = ref('Todas');
const searchQuery = ref('');

// Listado de productos con datos de artesanos locales
const products = ref([
    {
        id: 1,
        name: 'Aguayo Antiguo Tejido a Mano',
        artisan: 'Doña Juana Quispe',
        location: 'Pasaje Marina Núñez',
        price: 350,
        category: 'Textiles y Aguayos',
        image: '🧣',
        stock: 3
    },
    {
        id: 2,
        name: 'Anillo de Plata 950 con Aguamarina',
        artisan: 'Artesanías Mamani',
        location: 'Pérez Velasco',
        price: 180,
        category: 'Joyería y Plata',
        image: '💍',
        stock: 5
    },
    {
        id: 3,
        name: 'Máscara de Diablo Tallada en Madera',
        artisan: 'Tallados Condori',
        location: 'Pasaje Marina Núñez',
        price: 650,
        category: 'Tallados y Máscaras',
        image: '👹',
        stock: 2
    },
    {
        id: 4,
        name: 'Chompa de Alpaca con Diseños Tiwanakotas',
        artisan: 'Tejidos Illimani',
        location: 'Calle Sagárnaga',
        price: 240,
        category: 'Textiles y Aguayos',
        image: '🧥',
        stock: 8
    },
    {
        id: 5,
        name: 'Jarrón Ceremonial de Arcilla Quemada',
        artisan: 'Cerámicas Wara',
        location: 'El Alto - Plaza 16 de Julio',
        price: 120,
        category: 'Cerámica',
        image: '🏺',
        stock: 4
    }
]);

// Lógica de filtrado combinada (Búsqueda + Categoría)
const filteredProducts = computed(() => {
    return products.value.filter(product => {
        const matchesCategory = selectedCategory.value === 'Todas' || product.category === selectedCategory.value;
        const matchesSearch = product.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                              product.artisan.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchesCategory && matchesSearch;
    });
});

// Simulación de añadir al carrito
const addToCart = (productName) => {
    alert(`🛒 ¡${productName} añadido al carrito de compras!`);
};

const safeRoute = (routeName) => {
    try { return route(routeName); } catch (e) { return '#'; }
};
</script>

<template>
    <Head title="Catálogo de Productos - Punto Boliviano" />

    <div class="min-h-screen bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100 transition-colors duration-200">
        <nav class="border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-800 dark:bg-gray-900 sticky top-0 z-50 shadow-sm">
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <Link :href="safeRoute('welcome')" class="flex items-center space-x-2">
                    <span class="text-2xl font-black tracking-wider text-red-600 dark:text-red-500">PUNTO</span>
                    <span class="bg-red-600 px-2 py-0.5 text-sm font-bold text-white rounded dark:bg-red-500">BOLIVIANO</span>
                </Link>

                <div class="flex items-center space-x-6">
                    <Link :href="safeRoute('dashboard')" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400">
                        Mi Panel
                    </Link>
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400" v-if="props.auth?.user">
                        Hola, <strong class="text-gray-900 dark:text-white">{{ props.auth.user.name }}</strong>
                    </span>
                </div>
            </div>
        </nav>

        <header class="bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl">Obras de Nuestros Artesanos</h1>
                <p class="mt-3 max-w-xl mx-auto text-base text-gray-500 dark:text-gray-400">Encuentra piezas únicas con identidad, directo desde los talleres y puestos tradicionales.</p>
            </div>
        </header>

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="w-full md:w-96">
                    <input 
                        v-model="searchQuery" 
                        type="text" 
                        placeholder="Buscar producto o artesano..." 
                        class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm shadow-sm focus:ring-red-500 focus:border-red-500"
                    />
                </div>

                <div class="flex flex-wrap gap-2">
                    <button 
                        v-for="cat in categories" 
                        :key="cat"
                        @click="selectedCategory = cat"
                        :class="[selectedCategory === cat ? 'bg-red-600 text-white' : 'bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-800 hover:bg-gray-50', 'px-3 py-1.5 text-xs font-bold rounded-lg transition-colors shadow-sm']"
                    >
                        {{ cat }}
                    </button>
                </div>
            </div>

            <div v-if="filteredProducts.length === 0" class="text-center py-20 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800">
                <span class="text-4xl">🔍</span>
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">No encontramos obras que coincidan con tu búsqueda.</p>
            </div>

            <div v-else class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:gap-x-8">
                <div v-for="product in filteredProducts" :key="product.id" class="group relative flex flex-col bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all">
                    
                    <div class="aspect-h-1 aspect-w-1 w-full bg-gray-100 dark:bg-gray-950 flex items-center justify-center text-7xl p-12 group-hover:opacity-90 transition-opacity">
                        {{ product.image }}
                    </div>

                    <div class="p-5 flex flex-col flex-1 justify-between space-y-4">
                        <div class="space-y-1">
                            <span class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase tracking-wider">{{ product.category }}</span>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                                {{ product.name }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Por: <span class="font-medium text-gray-700 dark:text-gray-300">{{ product.artisan }}</span> 
                                (<span class="italic">{{ product.location }}</span>)
                            </p>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-gray-50 dark:border-gray-800">
                            <div>
                                <p class="text-lg font-black text-gray-900 dark:text-white">{{ product.price }} BOB</p>
                                <p class="text-[10px] text-gray-400">{{ product.stock }} unidades disponibles</p>
                            </div>
                            <button 
                                @click="addToCart(product.name)"
                                class="rounded-xl bg-gray-900 text-white dark:bg-white dark:text-gray-900 px-3.5 py-2 text-xs font-bold hover:opacity-90 transition-opacity"
                            >
                                Adquirir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>