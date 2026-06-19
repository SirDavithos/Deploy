<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    auth: Object,
});

// --- ESTADO DE FILTROS ---
const searchQuery = ref('Aguayo'); // Término inicial de ejemplo
const selectedLocation = ref('Todos');
const maxPrice = ref(800);
const onlyInStock = ref(false);
const sortBy = ref('relevancia');

// Opciones de ubicación tradicionales
const locations = ['Todos', 'Pasaje Marina Núñez', 'Pérez Velasco', 'Calle Sagárnaga', 'El Alto'];

// --- BASE DE DATOS LOCAL (PRODUCTOS EXPANSIÓN) ---
const products = ref([
    { id: 1, name: 'Aguayo Antiguo Tejido a Mano', artisan: 'Doña Juana Quispe', location: 'Pasaje Marina Núñez', price: 350, rating: 4.9, stock: 3, image: '🧣', category: 'Textiles' },
    { id: 2, name: 'Anillo de Plata 950 con Aguamarina', artisan: 'Artesanías Mamani', location: 'Pérez Velasco', price: 180, rating: 4.7, stock: 5, image: '💍', category: 'Joyería' },
    { id: 3, name: 'Máscara de Diablo Tallada en Madera', artisan: 'Tallados Condori', location: 'Pasaje Marina Núñez', price: 650, rating: 5.0, stock: 2, image: '👹', category: 'Tallados' },
    { id: 4, name: 'Chompa de Alpaca con Diseños Tiwanakotas', artisan: 'Tejidos Illimani', location: 'Calle Sagárnaga', price: 240, rating: 4.6, stock: 8, image: '🧥', category: 'Textiles' },
    { id: 5, name: 'Aguayo Moderno de Lana de Oveja', artisan: 'Tejidos de la Faja', location: 'El Alto', price: 150, rating: 4.2, stock: 0, image: '🧣', category: 'Textiles' },
    { id: 6, name: 'Pendientes de Plata Filigrana', artisan: 'Joyas El Inca', location: 'Pérez Velasco', price: 290, rating: 4.8, stock: 4, image: '✨', category: 'Joyería' }
]);

// --- LÓGICA DE FILTRADO Y ORDENAMIENTO ---
const filteredProducts = computed(() => {
    let result = products.value.filter(product => {
        const matchesSearch = product.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                              product.artisan.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                              product.category.toLowerCase().includes(searchQuery.value.toLowerCase());
        
        const matchesLocation = selectedLocation.value === 'Todos' || product.location === selectedLocation.value;
        const matchesPrice = product.price <= maxPrice.value;
        const matchesStock = !onlyInStock.value || product.stock > 0;

        return matchesSearch && matchesLocation && matchesPrice && matchesStock;
    });

    // Ordenamiento
    if (sortBy.value === 'precio-bajo') result.sort((a, b) => a.price - b.price);
    if (sortBy.value === 'precio-alto') result.sort((a, b) => b.price - a.price);
    if (sortBy.value === 'reputacion') result.sort((a, b) => b.rating - a.rating);

    return result;
});

const addToCart = (productName) => {
    alert(`🛒 ¡${productName} añadido al carrito desde la búsqueda!`);
};

const safeRoute = (routeName) => {
    try { return route(routeName); } catch (e) { return '#'; }
};
</script>

<template>
    <Head :title="`Buscar: ${searchQuery} - Punto Boliviano`" />

    <div class="min-h-screen bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100 transition-colors duration-200">
        <nav class="border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-800 dark:bg-gray-900 sticky top-0 z-50 shadow-sm">
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <Link :href="safeRoute('welcome')" class="flex items-center space-x-2">
                    <span class="text-2xl font-black tracking-wider text-red-600 dark:text-red-500">PUNTO</span>
                    <span class="bg-red-600 px-2 py-0.5 text-sm font-bold text-white rounded dark:bg-red-500">BOLIVIANO</span>
                </Link>

                <div class="flex items-center space-x-4 w-1/3">
                    <input 
                        v-model="searchQuery" 
                        type="text" 
                        placeholder="Buscar artesanía, taller..." 
                        class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-950 text-xs shadow-sm focus:ring-red-500"
                    />
                </div>

                <div class="flex items-center space-x-4">
                    <Link :href="safeRoute('products.index')" class="text-xs font-bold text-gray-600 dark:text-gray-400 hover:text-red-600">Catálogo</Link>
                    <Link :href="safeRoute('dashboard')" class="text-xs font-bold text-gray-600 dark:text-gray-400 hover:text-red-600">Mi Panel</Link>
                </div>
            </div>
        </nav>

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-4 lg:gap-x-8">
                
                <aside class="hidden lg:block space-y-6 bg-white dark:bg-gray-900 p-6 rounded-2xl border border-gray-200 dark:border-gray-800 h-fit">
                    <h2 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Filtrar por</h2>
                    
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase">Precio Máximo: <span class="text-red-600 dark:text-red-400">{{ maxPrice }} BOB</span></label>
                        <input type="range" min="100" max="1000" step="50" v-model="maxPrice" class="w-full accent-red-600" />
                        <div class="flex justify-between text-[10px] text-gray-400">
                            <span>100 BOB</span>
                            <span>1000 BOB</span>
                        </div>
                    </div>

                    <hr class="border-gray-100 dark:border-gray-800" />

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase">Ubicación de Feria / Taller</label>
                        <select v-model="selectedLocation" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-950 text-xs focus:ring-red-500">
                            <option v-for="loc in locations" :key="loc" :value="loc">{{ loc }}</option>
                        </select>
                    </div>

                    <hr class="border-gray-100 dark:border-gray-800" />

                    <div class="flex items-center space-x-3">
                        <input id="stock-checkbox" type="checkbox" v-model="onlyInStock" class="rounded border-gray-300 text-red-600 focus:ring-red-500 h-4 w-4" />
                        <label for="stock-checkbox" class="text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer">Mostrar solo con stock disponible</label>
                    </div>
                </aside>

                <main class="lg:col-span-3 space-y-4">
                    <div class="bg-white dark:bg-gray-900 p-4 rounded-2xl border border-gray-200 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 shadow-sm">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                            Se encontraron <strong class="text-gray-900 dark:text-white">{{ filteredProducts.length }}</strong> resultados para "<span class="italic font-semibold">{{ searchQuery || 'todo' }}</span>"
                        </p>

                        <div class="flex items-center space-x-2">
                            <label class="text-xs text-gray-400 whitespace-nowrap">Ordenar por:</label>
                            <select v-model="sortBy" class="rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-950 text-xs p-1.5 focus:ring-red-500">
                                <option value="relevancia">Mejor calificados ⭐</option>
                                <option value="precio-bajo">Menor precio</option>
                                <option value="precio-alto">Mayor precio</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="filteredProducts.length === 0" class="bg-white dark:bg-gray-900 text-center py-24 rounded-2xl border border-gray-200 dark:border-gray-800">
                        <span class="text-4xl">🏜️</span>
                        <h3 class="mt-4 text-sm font-bold text-gray-900 dark:text-white">Sin coincidencias exactas</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Prueba ajustando los filtros laterales o cambiando el término de búsqueda.</p>
                    </div>

                    <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div v-for="product in filteredProducts" :key="product.id" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 flex gap-4 hover:shadow-sm transition-shadow relative overflow-hidden">
                            
                            <div class="w-24 h-24 bg-gray-100 dark:bg-gray-950 rounded-xl flex items-center justify-center text-4xl flex-shrink-0">
                                {{ product.image }}
                            </div>

                            <div class="flex flex-col justify-between flex-1 min-w-0">
                                <div>
                                    <div class="flex items-center justify-between gap-2">
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ product.name }}</h4>
                                        <span class="text-xs font-bold text-amber-600 whitespace-nowrap">⭐ {{ product.rating }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Por: {{ product.artisan }}</p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">📍 {{ product.location }}</p>
                                </div>

                                <div class="flex items-center justify-between pt-2 border-t border-gray-50 dark:border-gray-800">
                                    <span class="text-base font-black text-red-600 dark:text-red-500">{{ product.price }} BOB</span>
                                    
                                    <button 
                                        @click="addToCart(product.name)"
                                        :disabled="product.stock === 0"
                                        :class="[product.stock === 0 ? 'bg-gray-100 text-gray-400 dark:bg-gray-800 cursor-not-allowed' : 'bg-gray-900 text-white dark:bg-white dark:text-gray-900 hover:opacity-90', 'px-3 py-1.5 rounded-lg text-xs font-bold transition-all']"
                                    >
                                        {{ product.stock === 0 ? 'Agotado' : 'Adquirir' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
</template>