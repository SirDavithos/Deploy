<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    product: Object,
    categories: Array,
});

// Formulario precargado con los datos actuales del producto
const form = useForm({
    title: props.product.title,
    description: props.product.description || '',
    price: props.product.price,
    stock: props.product.stock,
    category_id: props.product.category_id || '',
    status: props.product.status,
    images: [],           // nuevas imágenes a subir
    delete_images: [],    // índices de imágenes a eliminar
});

// Previsualización de imágenes existentes
const existingImages = ref(props.product.images || []);
const newImagePreviews = ref([]);

const selectNewImages = (e) => {
    const files = Array.from(e.target.files);
    form.images = files;
    newImagePreviews.value = files.map(f => URL.createObjectURL(f));
};

const removeExistingImage = (index) => {
    if (!form.delete_images.includes(index)) {
        form.delete_images.push(index);
    }
    // Ocultar visualmente la imagen marcada para borrar
    existingImages.value[index] = null;
};

const submit = () => {
    // Usamos la ruta 'products.update' con el id del producto
    form.patch(route('products.update', props.product.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('images', 'delete_images');
            newImagePreviews.value = [];
            // Refrescar las imágenes existentes (el backend devuelve el producto actualizado, 
            // pero podemos simplemente recargar la página o confiar en que Inertia actualiza las props)
        },
    });
};
</script>

<template>
    <Head title="Editar Producto" />
    <div class="max-w-2xl mx-auto py-12 px-4">
        <h2 class="text-2xl font-bold mb-6">Editar Producto</h2>
        <form @submit.prevent="submit" class="space-y-4 bg-white dark:bg-gray-900 p-6 rounded-xl shadow">
            <!-- Título -->
            <div>
                <label class="text-xs font-bold block mb-1">Título</label>
                <input v-model="form.title" type="text" class="input-field" required />
                <p v-if="form.errors.title" class="text-[11px] text-red-500 mt-1">{{ form.errors.title }}</p>
            </div>

            <!-- Descripción -->
            <div>
                <label class="text-xs font-bold block mb-1">Descripción</label>
                <textarea v-model="form.description" rows="3" class="input-field"></textarea>
            </div>

            <!-- Precio y Stock -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold block mb-1">Precio (BOB)</label>
                    <input v-model="form.price" type="number" step="0.01" min="0" class="input-field" required />
                    <p v-if="form.errors.price" class="text-[11px] text-red-500 mt-1">{{ form.errors.price }}</p>
                </div>
                <div>
                    <label class="text-xs font-bold block mb-1">Stock</label>
                    <input v-model="form.stock" type="number" min="1" class="input-field" required />
                    <p v-if="form.errors.stock" class="text-[11px] text-red-500 mt-1">{{ form.errors.stock }}</p>
                </div>
            </div>

            <!-- Categoría -->
            <div>
                <label class="text-xs font-bold block mb-1">Categoría</label>
                <select v-model="form.category_id" class="input-field">
                    <option value="">Sin categoría</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
            </div>

            <!-- Estado -->
            <div>
                <label class="text-xs font-bold block mb-1">Estado</label>
                <select v-model="form.status" class="input-field">
                    <option value="published">Publicado</option>
                    <option value="draft">Borrador</option>
                    <option value="sold_out">Agotado</option>
                </select>
            </div>

            <!-- Imágenes existentes -->
            <div v-if="existingImages.length">
                <label class="text-xs font-bold block mb-1">Imágenes actuales</label>
                <div class="flex flex-wrap gap-2">
                    <div v-for="(img, index) in existingImages" :key="index" v-show="img !== null" class="relative">
                        <img :src="'/storage/' + img" class="h-20 w-20 object-cover rounded border" />
                        <button type="button" @click="removeExistingImage(index)" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 text-xs flex items-center justify-center">✕</button>
                    </div>
                </div>
            </div>

            <!-- Nuevas imágenes -->
            <div>
                <label class="text-xs font-bold block mb-1">Añadir más imágenes</label>
                <input type="file" multiple accept="image/*" @change="selectNewImages" class="text-xs" />
                <div class="flex flex-wrap gap-2 mt-2">
                    <img v-for="(src, i) in newImagePreviews" :key="i" :src="src" class="h-20 w-20 object-cover rounded" />
                </div>
                <p v-if="form.errors.images" class="text-[11px] text-red-500 mt-1">{{ form.errors.images }}</p>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t dark:border-gray-800">
                <Link :href="route('shop.show', product.shop_id)" class="btn-outline text-xs px-3 py-1.5">Cancelar</Link>
                <button type="submit" :disabled="form.processing" class="btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</template>