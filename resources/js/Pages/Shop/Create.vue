<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

// --- Fix para iconos de Leaflet ---
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: '/images/leaflet/marker-icon-2x.png',
    iconUrl: '/images/leaflet/marker-icon.png',
    shadowUrl: '/images/leaflet/marker-shadow.png',
});

const form = useForm({
    name: '',
    description: '',
    phone_number: '',
    phone_type: 'Principal',
    city: 'La Paz',
    zone: '',
    street_address: '',
    reference: '',
    latitude: null,
    longitude: null,
    images: [],
    avatar: null,
    banner: null,
    is_tax_registered: false,
    shop_nit_or_ci: '',
    shop_business_name: '',
    shop_tax_regimen: '',
});

const imagePreviews = ref([]);
const avatarPreview = ref(null);
const bannerPreview = ref(null);

// --- Mapa ---
const mapContainer = ref(null);
let map = null;
let marker = null;

onMounted(() => {
    if (mapContainer.value) {
        // Coordenadas iniciales: centro de La Paz
        const laPazCenter = [-16.5, -68.15];
        map = L.map(mapContainer.value).setView(laPazCenter, 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // Si ya hay coordenadas, mostrar marcador
        if (form.latitude && form.longitude) {
            marker = L.marker([form.latitude, form.longitude]).addTo(map);
            map.setView([form.latitude, form.longitude], 15);
        }

        // Evento de clic para colocar marcador
        map.on('click', (e) => {
            const { lat, lng } = e.latlng;
            form.latitude = lat.toFixed(6);
            form.longitude = lng.toFixed(6);

            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng]).addTo(map);
            }
        });
    }
});

// --- Funciones de archivo ---
const selectImages = (e) => {
    const files = Array.from(e.target.files);
    form.images = files;
    imagePreviews.value = files.map(file => URL.createObjectURL(file));
};

const selectAvatar = (e) => {
    form.avatar = e.target.files[0];
    avatarPreview.value = form.avatar ? URL.createObjectURL(form.avatar) : null;
};

const selectBanner = (e) => {
    form.banner = e.target.files[0];
    bannerPreview.value = form.banner ? URL.createObjectURL(form.banner) : null;
};

const submit = () => {
    form.post(route('shop.store'), {
        onSuccess: () => {
            form.reset();
            form.city = 'La Paz';
            imagePreviews.value = [];
            avatarPreview.value = null;
            bannerPreview.value = null;
            // No reseteamos el mapa, se reinicia al volver a montar
        },
    });
};
</script>

<template>
    <Head title="Abrir Tienda - Punto Boliviano" />
    <div class="max-w-2xl mx-auto py-8 px-4">
        <div class="bg-white dark:bg-gray-900 p-8 rounded-xl shadow-sm ring-1 ring-gray-900/5 dark:ring-gray-800">
            <h2 class="text-2xl font-black text-red-600 dark:text-red-400 mb-2">Abrir mi Tienda</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">
                Completa el formulario para solicitar tu espacio de venta en Punto Boliviano.
            </p>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- ========== INFORMACIÓN GENERAL ========== -->
                <div>
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">Información general</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs font-bold block mb-1">Nombre de la tienda *</label>
                            <input v-model="form.name" type="text" class="input-field" required />
                            <p v-if="form.errors.name" class="input-error">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-bold block mb-1">Descripción</label>
                            <textarea v-model="form.description" rows="3" class="input-field" placeholder="Cuéntanos sobre tu tienda..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- ========== IMAGEN DE PERFIL Y PORTADA ========== -->
                <div>
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">Imagen y portada</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold block mb-1">Avatar (foto de perfil)</label>
                            <input type="file" accept="image/*" @change="selectAvatar" class="text-xs" />
                            <div class="mt-2 w-20 h-20 rounded-full overflow-hidden border dark:border-gray-700 bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                <img v-if="avatarPreview" :src="avatarPreview" class="w-full h-full object-cover" />
                                <span v-else class="text-2xl">🏪</span>
                            </div>
                            <p v-if="form.errors.avatar" class="input-error">{{ form.errors.avatar }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-bold block mb-1">Banner (portada)</label>
                            <input type="file" accept="image/*" @change="selectBanner" class="text-xs" />
                            <div class="mt-2 h-24 rounded-lg overflow-hidden border dark:border-gray-700 bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                <img v-if="bannerPreview" :src="bannerPreview" class="w-full h-full object-cover" />
                                <span v-else class="text-sm text-gray-400">Previsualización</span>
                            </div>
                            <p v-if="form.errors.banner" class="input-error">{{ form.errors.banner }}</p>
                        </div>
                    </div>
                </div>

                <!-- ========== CONTACTO ========== -->
                <div>
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">Contacto</h3>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="text-xs font-bold block mb-1">Número de teléfono *</label>
                            <input v-model="form.phone_number" type="text" placeholder="+591 7xxxxxxx" class="input-field" required />
                            <p v-if="form.errors.phone_number" class="input-error">{{ form.errors.phone_number }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-bold block mb-1">Tipo</label>
                            <select v-model="form.phone_type" class="input-field">
                                <option>Principal</option>
                                <option>WhatsApp</option>
                                <option>Fijo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ========== DIRECCIÓN Y UBICACIÓN GPS ========== -->
                <div>
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">Dirección y ubicación GPS</h3>
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
                            <p v-if="form.errors.zone" class="input-error">{{ form.errors.zone }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-bold block mb-1">Calle / Nº *</label>
                            <input v-model="form.street_address" type="text" placeholder="Av. 6 de Agosto #123" class="input-field" required />
                            <p v-if="form.errors.street_address" class="input-error">{{ form.errors.street_address }}</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="text-xs font-bold block mb-1">Referencia</label>
                        <input v-model="form.reference" type="text" placeholder="Cerca de..." class="input-field" />
                    </div>

                    <!-- Mapa -->
                    <div class="mt-4">
                        <label class="text-xs font-bold block mb-1">Ubicación exacta (GPS)</label>
                        <p class="text-xs text-gray-500 mb-2">Haz clic en el mapa para marcar la ubicación de tu tienda.</p>
                        <div ref="mapContainer" class="w-full h-64 rounded-lg border dark:border-gray-700 z-10"></div>
                        <div class="flex gap-4 mt-2 text-xs text-gray-600">
                            <span v-if="form.latitude">Lat: {{ form.latitude }}</span>
                            <span v-if="form.longitude">Lon: {{ form.longitude }}</span>
                        </div>
                        <p v-if="form.errors.latitude" class="input-error">{{ form.errors.latitude }}</p>
                    </div>
                </div>

                <!-- Información fiscal -->
                <div>
                    <h4 class="text-xs font-bold text-gray-500 uppercase mb-3">Información fiscal</h4>
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs font-bold block mb-1">Régimen fiscal</label>
                            <select v-model="editForm.tax_regimen" class="input-field">
                                <option value="">No registrado (emitirá recibo simple)</option>
                                <option value="Régimen General">Régimen General (emite factura)</option>
                                <option value="Régimen Tributario Simplificado">RTS (emite recibo con NIT)</option>
                            </select>
                        </div>

                        <div v-if="editForm.tax_regimen" class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                            <div>
                                <label class="text-xs font-bold block mb-1">NIT *</label>
                                <input v-model="editForm.shop_nit_or_ci" type="text" class="input-field" required />
                                <p v-if="editForm.errors.shop_nit_or_ci" class="input-error">{{ editForm.errors.shop_nit_or_ci }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-bold block mb-1">Razón Social *</label>
                                <input v-model="editForm.shop_business_name" type="text" class="input-field" required />
                                <p v-if="editForm.errors.shop_business_name" class="input-error">{{ editForm.errors.shop_business_name }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========== OTRAS IMÁGENES ========== -->
                <div>
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">Otras imágenes (opcional)</h3>
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