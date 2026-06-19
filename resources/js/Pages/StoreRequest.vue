<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    description: '',
    phone: '',
    city: 'La Paz',
    zone: '',
    street_address: '',
    images: [],
});

const imagePreviews = ref([]);

const selectImages = (e) => {
    const files = Array.from(e.target.files);
    form.images = files;
    imagePreviews.value = files.map(file => URL.createObjectURL(file));
};

const submit = () => {
    form.post(route('shop.store'), {
        onSuccess: () => {
            form.reset();
            imagePreviews.value = [];
        },
    });
};
</script>

<template>
    <Head title="Solicitar Tienda - Punto Boliviano" />
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 py-12">
        <div class="max-w-2xl mx-auto bg-white dark:bg-gray-900 p-8 rounded-xl shadow-sm ring-1 ring-gray-900/5 dark:ring-gray-800">
            <h2 class="text-2xl font-black text-red-600 dark:text-red-400 mb-2">Abrir mi Tienda</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">
                Completa el formulario para solicitar tu espacio de venta en Punto Boliviano. Tu tienda será revisada por nuestro equipo.
            </p>

            <form @submit.prevent="submit" class="space-y-5">
                <!-- Nombre -->
                <div>
                    <label class="text-xs font-bold block mb-1">Nombre de la tienda</label>
                    <input v-model="form.name" type="text" class="input-field" required />
                    <p v-if="form.errors.name" class="text-[11px] text-red-500 mt-1">{{ form.errors.name }}</p>
                </div>

                <!-- Descripción -->
                <div>
                    <label class="text-xs font-bold block mb-1">Descripción</label>
                    <textarea v-model="form.description" rows="3" class="input-field"></textarea>
                    <p v-if="form.errors.description" class="text-[11px] text-red-500 mt-1">{{ form.errors.description }}</p>
                </div>

                <!-- Teléfono de contacto -->
                <div>
                    <label class="text-xs font-bold block mb-1">Teléfono</label>
                    <input v-model="form.phone" type="text" placeholder="+591 7xxxxxxx" class="input-field" />
                    <p v-if="form.errors.phone" class="text-[11px] text-red-500 mt-1">{{ form.errors.phone }}</p>
                </div>

                <!-- Dirección normalizada -->
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="text-xs font-bold block mb-1">Ciudad</label>
                        <select v-model="form.city" class="input-field">
                            <option>La Paz</option>
                            <option>El Alto</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold block mb-1">Zona</label>
                        <input v-model="form.zone" type="text" placeholder="Sopocachi, Centro..." class="input-field" required />
                        <p v-if="form.errors.zone" class="text-[11px] text-red-500 mt-1">{{ form.errors.zone }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-bold block mb-1">Calle / Nº</label>
                        <input v-model="form.street_address" type="text" placeholder="Av. 6 de Agosto #123" class="input-field" required />
                        <p v-if="form.errors.street_address" class="text-[11px] text-red-500 mt-1">{{ form.errors.street_address }}</p>
                    </div>
                </div>

                <!-- Imágenes -->
                <div>
                    <label class="text-xs font-bold block mb-1">Imágenes (opcional)</label>
                    <input type="file" multiple accept="image/*" @change="selectImages" class="text-xs" />
                    <div v-if="imagePreviews.length" class="flex gap-2 mt-2">
                        <img v-for="(src, i) in imagePreviews" :key="i" :src="src" class="h-20 w-20 object-cover rounded-lg border" />
                    </div>
                    <p v-if="form.errors.images" class="text-[11px] text-red-500 mt-1">{{ form.errors.images }}</p>
                </div>

                <div class="flex justify-end pt-4 border-t dark:border-gray-800">
                    <button type="submit" :disabled="form.processing" class="btn-primary">
                        Enviar solicitud
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>