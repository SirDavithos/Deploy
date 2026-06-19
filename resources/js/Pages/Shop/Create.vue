<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    description: '',
    // Teléfono
    phone_number: '',
    phone_type: 'Principal',
    // Dirección
    city: 'La Paz',
    zone: '',
    street_address: '',
    reference: '',
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
            form.city = 'La Paz'; // mantener ciudad por defecto
            imagePreviews.value = [];
        },
    });
};
</script>

<template>
    <Head title="Abrir Tienda - Punto Boliviano" />
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 py-12">
        <div class="max-w-2xl mx-auto bg-white dark:bg-gray-900 p-8 rounded-xl shadow-sm ring-1 ring-gray-900/5 dark:ring-gray-800">
            <h2 class="text-2xl font-black text-red-600 dark:text-red-400 mb-2">Abrir mi Tienda</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">
                Completa el formulario para solicitar tu espacio de venta.
            </p>

            <form @submit.prevent="submit" class="space-y-5">
                <!-- Nombre -->
                <div>
                    <label class="text-xs font-bold block mb-1">Nombre de la tienda *</label>
                    <input v-model="form.name" type="text" class="input-field" required />
                    <p v-if="form.errors.name" class="text-[11px] text-red-500 mt-1">{{ form.errors.name }}</p>
                </div>

                <!-- Descripción -->
                <div>
                    <label class="text-xs font-bold block mb-1">Descripción</label>
                    <textarea v-model="form.description" rows="3" class="input-field"></textarea>
                </div>

                <!-- Teléfono -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="text-xs font-bold block mb-1">Número de teléfono *</label>
                        <input v-model="form.phone_number" type="text" placeholder="+591 7xxxxxxx" class="input-field" required />
                    </div>
                    <div>
                        <label class="text-xs font-bold block mb-1">Tipo</label>
                        <select v-model="form.phone_type" class="input-field">
                            <option>Principal</option>
                            <option>WhatsApp</option>
                            <option>Fijo</option>
                        </select>
                    </div>
                    <p v-if="form.errors.phone_number" class="text-[11px] text-red-500 col-span-3 mt-0">{{ form.errors.phone_number }}</p>
                </div>

                <!-- Dirección -->
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="text-xs font-bold block mb-1">Ciudad *</label>
                        <select v-model="form.city" class="input-field">
                            <option>La Paz</option>
                            <option>El Alto</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold block mb-1">Zona *</label>
                        <input v-model="form.zone" type="text" placeholder="Sopocachi, Centro..." class="input-field" required />
                        <p v-if="form.errors.zone" class="text-[11px] text-red-500 mt-1">{{ form.errors.zone }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-bold block mb-1">Calle / Nº *</label>
                        <input v-model="form.street_address" type="text" placeholder="Av. 6 de Agosto #123" class="input-field" required />
                        <p v-if="form.errors.street_address" class="text-[11px] text-red-500 mt-1">{{ form.errors.street_address }}</p>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold block mb-1">Referencia</label>
                    <input v-model="form.reference" type="text" placeholder="Cerca de..." class="input-field" />
                </div>

                <!-- Imágenes -->
                <div>
                    <label class="text-xs font-bold block mb-1">Imágenes (opcional)</label>
                    <input type="file" multiple accept="image/*" @change="selectImages" class="text-xs" />
                    <div v-if="imagePreviews.length" class="flex gap-2 mt-2">
                        <img v-for="(src, i) in imagePreviews" :key="i" :src="src" class="h-20 w-20 object-cover rounded-lg border" />
                    </div>
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