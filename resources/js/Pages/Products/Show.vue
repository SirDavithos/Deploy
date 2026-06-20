<script setup>
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    product: Object,
    relatedProducts: Array, // productos similares
    canReview: Boolean,
});

// ==================== IMAGEN PRINCIPAL ====================
const images = computed(() => props.product.images || []);
const mainImage = ref(images.value[0] || null);

const selectImage = (img) => {
    mainImage.value = img;
};

// ==================== AÑADIR AL CARRITO ====================
const cartForm = useForm({
    product_id: props.product.id,
    quantity: 1,
});

const toastMessage = ref('');
const toastVisible = ref(false);

const addToCart = () => {
    cartForm.post(route('cart.store'), {
        preserveScroll: true,
        onSuccess: () => {
            toastMessage.value = '🛒 Producto añadido al carrito';
            toastVisible.value = true;
            setTimeout(() => { toastVisible.value = false; }, 3000);
        },
    });
};

const buyNow = () => {
    cartForm.post(route('cart.store'), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route('checkout.index'));
        },
    });
};

// ==================== RESEÑAS ====================
const reviewForm = useForm({
    rating: 0,
    comment: '',
});

const submitReview = () => {
    reviewForm.post(route('reviews.store', props.product.id), {
        preserveScroll: true,
        onSuccess: () => {
            reviewForm.reset();
        },
    });
};

const deleteReview = (reviewId) => {
    if (confirm('¿Eliminar esta reseña?')) {
        useForm({}).delete(route('reviews.destroy', reviewId), { preserveScroll: true });
    }
};

const userReview = computed(() => {
    const user = usePage().props.auth?.user;
    if (!user || !props.product.reviews) return null;
    return props.product.reviews.find(r => r.user_id === user.id);
});

const user = computed(() => usePage().props.auth?.user || null);
const userRoles = computed(() => usePage().props.auth?.userRoles || []);

// Añadir al carrito desde productos relacionados
const addRelatedToCart = (productId) => {
    const form = useForm({ product_id: productId, quantity: 1 });
    form.post(route('cart.store'), {
        preserveScroll: true,
        onSuccess: () => {
            toastMessage.value = '🛒 Producto añadido al carrito';
            toastVisible.value = true;
            setTimeout(() => { toastVisible.value = false; }, 3000);
        },
    });
};
</script>

<template>
    <Head :title="product.title + ' - Punto Boliviano'" />

    <!-- Toast de notificación -->
    <div
        v-if="toastVisible"
        class="fixed top-4 right-4 z-50 bg-green-600 text-white text-sm px-4 py-3 rounded-lg shadow-lg transition-all duration-300"
    >
        {{ toastMessage }}
    </div>

    <main class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <!-- Producto principal -->
        <div class="lg:grid lg:grid-cols-2 lg:gap-x-8 lg:items-start">
            <!-- Galería de imágenes -->
            <div class="flex flex-col gap-4">
                <div class="aspect-square w-full overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
                    <img v-if="mainImage" :src="'/storage/' + mainImage" alt="" class="h-full w-full object-cover object-center" />
                    <div v-else class="h-full w-full flex items-center justify-center text-6xl">📦</div>
                </div>
                <div v-if="images.length > 1" class="grid grid-cols-4 gap-2">
                    <button
                        v-for="(img, index) in images"
                        :key="index"
                        @click="selectImage(img)"
                        :class="{ 'ring-2 ring-red-500': mainImage === img }"
                        class="aspect-square overflow-hidden rounded-md bg-gray-100 dark:bg-gray-800"
                    >
                        <img :src="'/storage/' + img" class="h-full w-full object-cover object-center" />
                    </button>
                </div>
            </div>

            <!-- Información del producto (mejorada) -->
            <div class="mt-10 px-4 sm:px-0 sm:mt-16 lg:mt-0">
                <div class="space-y-4">
                    <!-- Categoría (tipo) -->
                    <p v-if="product.category" class="text-xs font-bold text-red-600 uppercase tracking-wider">
                        {{ product.category.name }}
                    </p>

                    <!-- Título -->
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                        {{ product.title }}
                    </h1>

                    <!-- Tienda (enlace a la vista pública) -->
                    <Link :href="route('shop.view', product.shop?.slug)" class="flex items-center gap-2 group">
                        <img v-if="product.shop?.avatar" :src="'/storage/' + product.shop.avatar" class="h-8 w-8 rounded-full object-cover" />
                        <div v-else class="h-8 w-8 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center text-xs font-bold text-red-800 dark:text-red-200">
                            {{ product.shop?.name?.charAt(0) }}
                        </div>
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400 group-hover:text-red-600 transition-colors">
                            {{ product.shop?.name }}
                        </span>
                    </Link>

                    <!-- Precio y stock -->
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-red-600 dark:text-red-400">
                            {{ product.price }} BOB
                        </span>
                        <span v-if="product.stock > 0" class="text-sm text-green-600 font-medium">
                            Stock: {{ product.stock }}
                        </span>
                        <span v-else class="text-sm text-red-500 font-medium">
                            Agotado
                        </span>
                    </div>

                    <!-- Descripción -->
                    <div class="border-t border-gray-200 dark:border-gray-800 pt-4">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-2">Descripción</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                            {{ product.description || 'Sin descripción disponible.' }}
                        </p>
                    </div>

                    <!-- Acciones -->
                    <div class="border-t border-gray-200 dark:border-gray-800 pt-6 flex flex-col sm:flex-row gap-3">
                        <button
                            v-if="product.stock > 0"
                            @click="addToCart"
                            :disabled="cartForm.processing"
                            class="btn-primary flex-1 py-3 text-sm"
                        >
                            🛒 Añadir al carrito
                        </button>
                        <button
                            v-if="product.stock > 0"
                            @click="buyNow"
                            :disabled="cartForm.processing"
                            class="btn-dark flex-1 py-3 text-sm"
                        >
                            ⚡ Comprar ahora
                        </button>
                        <button
                            v-else
                            disabled
                            class="btn-outline flex-1 py-3 text-sm opacity-50 cursor-not-allowed"
                        >
                            Agotado
                        </button>
                        <Link
                            :href="route('products.index')"
                            class="btn-outline py-3 px-4 text-sm"
                        >
                            ← Volver al mercado
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== RESEÑAS ==================== -->
        <div class="mt-16 border-t border-gray-200 dark:border-gray-800 pt-8">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Reseñas y Calificaciones</h2>

            <!-- Promedio -->
            <div class="flex items-center gap-2 mb-6">
                <span class="text-3xl font-black text-red-600">{{ product.average_rating || '0.0' }}</span>
                <div class="flex text-yellow-500 text-lg">
                    {{ '★'.repeat(Math.round(product.average_rating)) }}{{ '☆'.repeat(5 - Math.round(product.average_rating)) }}
                </div>
                <span class="text-sm text-gray-500">({{ product.reviews_count || 0 }} reseñas)</span>
            </div>

            <!-- Formulario para nueva reseña -->
            <div v-if="user && canReview && !userReview" class="card mb-6">
                <h3 class="text-sm font-bold mb-3">Dejar una reseña</h3>
                <form @submit.prevent="submitReview" class="space-y-4">
                    <div>
                        <label class="text-xs font-bold block mb-1">Calificación</label>
                        <div class="flex gap-1">
                            <button type="button" v-for="star in 5" :key="star" @click="reviewForm.rating = star"
                                class="text-2xl" :class="star <= reviewForm.rating ? 'text-yellow-500' : 'text-gray-300'">
                                ★
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold block mb-1">Comentario (opcional)</label>
                        <textarea v-model="reviewForm.comment" rows="3" class="input-field" maxlength="500"></textarea>
                    </div>
                    <button type="submit" :disabled="reviewForm.processing || !reviewForm.rating" class="btn-primary text-xs">
                        Publicar reseña
                    </button>
                </form>
            </div>
            <p v-else-if="!user" class="text-sm text-gray-500 mb-6">
                <Link :href="route('login')" class="text-red-600">Inicia sesión</Link> para dejar una reseña.
            </p>
            <p v-else-if="user && !canReview" class="text-sm text-gray-500 mb-6">
                Solo los compradores verificados pueden opinar.
            </p>

            <!-- Listado de reseñas -->
            <div v-if="product.reviews && product.reviews.length > 0" class="space-y-4">
                <div v-for="review in product.reviews" :key="review.id" class="card flex gap-4 items-start">
                    <div class="flex items-center gap-3">
                        <img v-if="review.user.avatar" :src="'/storage/' + review.user.avatar" class="h-10 w-10 rounded-full" />
                        <div v-else class="h-10 w-10 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center text-sm font-bold text-red-800">
                            {{ review.user.first_name?.charAt(0) }}
                        </div>
                        <div>
                            <span class="text-sm font-bold">{{ review.user.first_name }} {{ review.user.paternal_last_name }}</span>
                            <span class="text-xs text-gray-400 ml-2">{{ review.created_at }}</span>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="text-yellow-500 text-sm">★ {{ review.rating }}</div>
                        <p v-if="review.comment" class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ review.comment }}</p>
                    </div>
                    <button v-if="user && (user.id === review.user_id || userRoles.includes('admin'))" @click="deleteReview(review.id)" class="text-xs text-red-500">Eliminar</button>
                </div>
            </div>
            <p v-else class="text-sm text-gray-500">No hay reseñas todavía.</p>
        </div>

        <!-- ==================== PRODUCTOS RELACIONADOS ==================== -->
        <div v-if="relatedProducts && relatedProducts.length" class="mt-16 border-t border-gray-200 dark:border-gray-800 pt-8">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">También te gustará</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <Link
                    v-for="related in relatedProducts"
                    :key="related.id"
                    :href="route('products.show', related.id)"
                    class="product-card group cursor-pointer"
                >
                    <div class="w-full aspect-square bg-gray-50 dark:bg-gray-800 rounded-xl flex items-center justify-center mb-4 overflow-hidden">
                        <img v-if="related.images && related.images.length" :src="'/storage/' + related.images[0]" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                        <span v-else class="text-6xl">📦</span>
                    </div>
                    <div class="space-y-1 flex-1">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white line-clamp-1 group-hover:text-red-600 transition-colors">{{ related.title }}</h3>
                        <p class="text-[11px] text-gray-400">Por: <span class="font-medium text-gray-500 dark:text-gray-300">{{ related.shop?.name }}</span></p>
                    </div>
                    <div class="flex items-center justify-between pt-3 mt-4 border-t border-gray-50 dark:border-gray-800">
                        <span class="text-lg font-black text-red-600 dark:text-red-400">{{ related.price }} BOB</span>
                        <button @click.prevent="addRelatedToCart(related.id)" class="btn-dark px-2.5 py-1 rounded-md text-[10px]">Adquirir</button>
                    </div>
                </Link>
            </div>
        </div>
    </main>
</template>