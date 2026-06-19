<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    categories: Array,
    shop: Object,
});

const form = useForm({
    title: '',
    description: '',
    price: '',
    stock: 1,
    category_id: '',
    images: [],
});

const imagePreviews = ref([]);

const selectImages = (e) => {
    const files = Array.from(e.target.files);
    form.images = files;
    imagePreviews.value = files.map(f => URL.createObjectURL(f));
};

const submit = () => {
    form.post(route('products.store'), {
        onSuccess: () => {
            form.reset();
            imagePreviews.value = [];
        },
    });
};
</script>

<template>
    <Head title="Publicar Producto" />
    <div class="max-w-2xl mx-auto py-12 px-4">
        <h2 class="text-2xl font-bold mb-6">Nuevo Producto para {{ shop.name }}</h2>
        <form @submit.prevent="submit" class="space-y-4 bg-white dark:bg-gray-900 p-6 rounded-xl shadow">
            <div>
                <label class="text-xs font-bold">Título</label>
                <input v-model="form.title" type="text" class="input-field" required />
            </div>
            <div>
                <label class="text-xs font-bold">Descripción</label>
                <textarea v-model="form.description" rows="3" class="input-field"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold">Precio (BOB)</label>
                    <input v-model="form.price" type="number" step="0.01" min="0" class="input-field" required />
                </div>
                <div>
                    <label class="text-xs font-bold">Stock</label>
                    <input v-model="form.stock" type="number" min="1" class="input-field" required />
                </div>
            </div>
            <div>
                <label class="text-xs font-bold">Categoría</label>
                <select v-model="form.category_id" class="input-field">
                    <option value="">Sin categoría</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-bold">Imágenes</label>
                <input type="file" multiple accept="image/*" @change="selectImages" class="text-xs" />
                <div class="flex gap-2 mt-2">
                    <img v-for="(src, i) in imagePreviews" :key="i" :src="src" class="h-20 w-20 object-cover rounded" />
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing" class="btn-primary">Publicar Producto</button>
            </div>
        </form>
    </div>
</template>