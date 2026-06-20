<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    auth: Object,
    phones: Array,
    addresses: Array,
    taxData: Array,
    orders: Object,
    filters: Object,
    roles: Array,
    userShop: Object|null,
});

// --- CONTROL DE PESTAÑAS ---
const activeTab = ref('compras');

// --- USUARIO ---
const page = usePage();
const user = computed(() => page.props.auth?.user || null);

// ==================== COMPRAS ====================
const downloadInvoice = (orderId) => {
    window.open(route('orders.invoice', orderId), '_blank');
};

// Determinar tipo de documento (Factura/Recibo)
const getDocType = (order) => {
    const shopTax = order.shop?.tax_data?.[0];
    if (shopTax?.tax_regimen === 'Régimen General') return { label: '🧾 Factura', class: 'text-green-700 bg-green-100' };
    if (shopTax?.tax_regimen === 'Régimen Tributario Simplificado') return { label: '📄 Recibo (RTS)', class: 'text-amber-700 bg-amber-100' };
    return { label: '📄 Recibo', class: 'text-gray-700 bg-gray-100' };
};

// Reseñas desde el dashboard
const reviewForms = ref({});
const startReview = (orderId, productId) => {
    const key = `${orderId}_${productId}`;
    if (!reviewForms.value[key]) {
        reviewForms.value[key] = useForm({ rating: 0, comment: '' });
    }
    reviewForms.value[key]._active = true;
};
const submitReview = (orderId, productId) => {
    const key = `${orderId}_${productId}`;
    const form = reviewForms.value[key];
    form.post(route('reviews.store', productId), {
        preserveScroll: true,
        onSuccess: () => { delete reviewForms.value[key]; },
    });
};

// ==================== TELÉFONOS ====================
const phoneForm = useForm({ phone_number: '', type: 'Principal (WhatsApp)', is_default: false });
const editingPhone = ref(null);

const addPhone = () => {
    phoneForm.post(route('user.phones.store'), {
        preserveScroll: true,
        onSuccess: () => phoneForm.reset(),
    });
};
const startEditPhone = (phone) => {
    editingPhone.value = phone.id;
    phoneForm.phone_number = phone.phone_number;
    phoneForm.type = phone.type;
    phoneForm.is_default = phone.is_default;
};
const cancelEditPhone = () => {
    editingPhone.value = null;
    phoneForm.reset();
};
const updatePhone = (phoneId) => {
    phoneForm.patch(route('user.phones.update', phoneId), {
        preserveScroll: true,
        onSuccess: () => { editingPhone.value = null; phoneForm.reset(); },
    });
};
const deletePhone = (id) => {
    if (confirm('¿Eliminar este teléfono?')) {
        useForm({}).delete(route('user.phones.destroy', id), { preserveScroll: true });
    }
};

// ==================== DIRECCIONES ====================
const addressForm = useForm({ city: 'La Paz', zone: '', street_address: '', reference: '', is_default: false });
const editingAddress = ref(null);

const addAddress = () => {
    addressForm.post(route('user.addresses.store'), {
        preserveScroll: true,
        onSuccess: () => { addressForm.reset(); addressForm.city = 'La Paz'; },
    });
};
const startEditAddress = (addr) => {
    editingAddress.value = addr.id;
    addressForm.city = addr.city;
    addressForm.zone = addr.zone;
    addressForm.street_address = addr.street_address;
    addressForm.reference = addr.reference || '';
    addressForm.is_default = addr.is_default;
};
const cancelEditAddress = () => {
    editingAddress.value = null;
    addressForm.reset();
    addressForm.city = 'La Paz';
};
const updateAddress = (addrId) => {
    addressForm.patch(route('user.addresses.update', addrId), {
        preserveScroll: true,
        onSuccess: () => { editingAddress.value = null; addressForm.reset(); addressForm.city = 'La Paz'; },
    });
};
const deleteAddress = (id) => {
    if (confirm('¿Eliminar esta dirección?')) {
        useForm({}).delete(route('user.addresses.destroy', id), { preserveScroll: true });
    }
};

// ==================== NIT / FACTURACIÓN ====================
const taxForm = useForm({ nit_or_ci: '', business_name: '', tax_regimen: '', alias: '', is_default: false });
const editingTax = ref(null);

const addTaxData = () => {
    taxForm.post(route('user.tax-data.store'), {
        preserveScroll: true,
        onSuccess: () => taxForm.reset(),
    });
};
const startEditTax = (tax) => {
    editingTax.value = tax.id;
    taxForm.nit_or_ci = tax.nit_or_ci;
    taxForm.business_name = tax.business_name;
    taxForm.tax_regimen = tax.tax_regimen || '';
    taxForm.alias = tax.alias || '';
    taxForm.is_default = tax.is_default;
};
const cancelEditTax = () => {
    editingTax.value = null;
    taxForm.reset();
};
const updateTax = (taxId) => {
    taxForm.patch(route('user.tax-data.update', taxId), {
        preserveScroll: true,
        onSuccess: () => { editingTax.value = null; taxForm.reset(); },
    });
};
const deleteTaxData = (id) => {
    if (confirm('¿Eliminar este dato fiscal?')) {
        useForm({}).delete(route('user.tax-data.destroy', id), { preserveScroll: true });
    }
};

// ==================== PERFIL ====================
const profileForm = useForm({
    first_name: user.value?.first_name || '',
    paternal_last_name: user.value?.paternal_last_name || '',
    maternal_last_name: user.value?.maternal_last_name || '',
    ci_number: user.value?.ci_number || '',
    ci_extension: user.value?.ci_extension || '',
    birth_date: user.value?.birth_date || '',
    email: user.value?.email || '',
    avatar: null,
});
const updateProfile = () => {
    profileForm.post(route('profile.update'), {
        _method: 'PATCH',
        forceFormData: true,   // necesario para archivos
        preserveScroll: true,
        onSuccess: () => profileForm.reset('avatar'),
    });
};
</script>

<template>
    <Head title="Mi Panel - Punto Boliviano" />
    <div class="max-w-7xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-12 lg:gap-x-8">
            <!-- Menú lateral -->
            <aside class="py-6 px-2 sm:px-6 lg:col-span-3 lg:py-0 lg:px-0">
                <nav class="space-y-1">
                    <button @click="activeTab = 'compras'" :class="[activeTab === 'compras' ? 'tab-active' : 'tab-inactive', 'group flex items-center w-full rounded-md px-3 py-2 text-sm font-medium transition-colors']">
                        <span class="mr-3 text-lg">🛍️</span> Mis Pedidos y Mercado
                    </button>
                    <Link :href="route('cart.index')" class="tab-inactive group flex items-center w-full rounded-md px-3 py-2 text-sm font-medium transition-colors">
                        <span class="mr-3 text-lg">🛒</span> Carrito
                    </Link>
                    <button @click="activeTab = 'telefonos'" :class="[activeTab === 'telefonos' ? 'tab-active' : 'tab-inactive', 'group flex items-center w-full rounded-md px-3 py-2 text-sm font-medium transition-colors']">
                        <span class="mr-3 text-lg">📞</span> Teléfonos
                    </button>
                    <button @click="activeTab = 'direcciones'" :class="[activeTab === 'direcciones' ? 'tab-active' : 'tab-inactive', 'group flex items-center w-full rounded-md px-3 py-2 text-sm font-medium transition-colors']">
                        <span class="mr-3 text-lg">📍</span> Direcciones
                    </button>
                    <button @click="activeTab = 'nit'" :class="[activeTab === 'nit' ? 'tab-active' : 'tab-inactive', 'group flex items-center w-full rounded-md px-3 py-2 text-sm font-medium transition-colors']">
                        <span class="mr-3 text-lg">💼</span> NIT / Facturación
                    </button>
                    <button @click="activeTab = 'perfil'" :class="[activeTab === 'perfil' ? 'tab-active' : 'tab-inactive', 'group flex items-center w-full rounded-md px-3 py-2 text-sm font-medium transition-colors']">
                        <span class="mr-3 text-lg">👤</span> Mi Perfil
                    </button>
                </nav>
            </aside>

            <div class="space-y-8 lg:col-span-9">
                
                <!-- ==================== COMPRAS ==================== -->
                <div v-if="activeTab === 'compras'" class="space-y-8">
                    <div class="card">
                        <h2 class="text-lg font-bold mb-2">🛍️ Bienvenido a tu Panel</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                            Gestiona tus compras, revisa tus pedidos y explora el mercado.
                        </p>
                        <div class="flex gap-4">
                            <Link :href="route('products.index')" class="btn-primary text-xs px-4 py-2 inline-block">Ir al Mercado</Link>
                            <Link :href="route('cart.index')" class="btn-dark text-xs px-4 py-2 inline-block">Ver Carrito</Link>
                        </div>
                    </div>

                    <div v-if="orders?.data?.length > 0" class="space-y-4">
                        <h3 class="section-title">Historial de Pedidos</h3>
                        <div v-for="order in orders.data" :key="order.id" class="card space-y-3">
                            <div class="flex justify-between items-start">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">#{{ order.id }}</span>
                                        <Link :href="route('shop.view', order.shop?.slug)" class="text-sm font-bold text-gray-900 dark:text-white hover:text-red-600 transition-colors">
                                            {{ order.shop?.name }}
                                        </Link>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        📅 {{ order.created_at }} &nbsp;|&nbsp; 🛍️ {{ order.items?.length }} producto(s)
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span :class="[getDocType(order).class, 'text-xs px-2 py-0.5 rounded font-medium']">
                                        {{ getDocType(order).label }}
                                    </span>
                                    <span :class="{
                                        'badge badge-pending': order.status === 'pending',
                                        'badge badge-info': order.status === 'confirmed' || order.status === 'shipped',
                                        'badge badge-success': order.status === 'delivered',
                                        'badge badge-danger': order.status === 'cancelled',
                                    }">
                                        {{ order.status }}
                                    </span>
                                </div>
                            </div>

                            <ul class="text-xs space-y-3 border-t dark:border-gray-800 pt-3">
                                <li v-for="item in order.items" :key="item.id" class="flex justify-between items-center">
                                    <span>{{ item.product?.title }} <span class="text-gray-400">x{{ item.quantity }}</span></span>
                                    <div class="flex items-center gap-3">
                                        <span class="font-mono text-gray-600 dark:text-gray-400">{{ (item.price * item.quantity).toFixed(2) }} BOB</span>
                                        <div v-if="order.status === 'delivered' && !reviewForms[`${order.id}_${item.product_id}`]?._active">
                                            <button @click="startReview(order.id, item.product_id)" class="text-xs text-blue-600 font-bold hover:underline">⭐ Reseñar</button>
                                        </div>
                                        <div v-if="reviewForms[`${order.id}_${item.product_id}`]?._active" class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg flex items-center gap-3">
                                            <div class="flex gap-1">
                                                <button v-for="star in 5" :key="star" @click="reviewForms[`${order.id}_${item.product_id}`].rating = star"
                                                    class="text-lg" :class="star <= reviewForms[`${order.id}_${item.product_id}`].rating ? 'text-yellow-500' : 'text-gray-300'">
                                                    ★
                                                </button>
                                            </div>
                                            <input v-model="reviewForms[`${order.id}_${item.product_id}`].comment" type="text" placeholder="Comentario" class="input-field text-xs w-32" />
                                            <button @click="submitReview(order.id, item.product_id)" :disabled="reviewForms[`${order.id}_${item.product_id}`].processing" class="btn-primary text-xs px-2 py-1">Enviar</button>
                                            <button @click="delete reviewForms[`${order.id}_${item.product_id}`]" class="text-xs text-gray-500">Cancelar</button>
                                        </div>
                                    </div>
                                </li>
                            </ul>

                            <div class="flex items-center justify-between border-t dark:border-gray-800 pt-3">
                                <div class="flex items-center gap-4">
                                    <span class="text-sm font-black text-red-600 dark:text-red-400">Total: {{ order.total }} BOB</span>
                                    <a v-if="order.shop?.phones?.[0]?.phone_number" :href="`https://wa.me/591${order.shop.phones[0].phone_number.replace(/[\s\-\(\)\+]/g, '')}`" target="_blank" class="btn-whatsapp text-xs px-2 py-1">💬 Contactar</a>
                                </div>
                                <button @click="downloadInvoice(order.id)" class="btn-outline text-xs px-3 py-1.5">
                                    📥 {{ getDocType(order).label.replace(/[🧾📄]/g, '').trim() }}
                                </button>
                            </div>
                        </div>
                        <!-- Paginación -->
                        <div v-if="orders.links && orders.links.length > 3" class="flex justify-center mt-4">
                            <nav class="flex gap-1">
                                <template v-for="link in orders.links" :key="link.label">
                                    <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-3 py-1.5 text-xs rounded-md" :class="link.active ? 'bg-red-600 text-white' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'" />
                                    <span v-else v-html="link.label" class="px-3 py-1.5 text-xs text-gray-400"></span>
                                </template>
                            </nav>
                        </div>
                    </div>
                    <div v-else class="card text-center py-8">
                        <p class="text-gray-500">No tienes pedidos aún. ¡Compra algo en el mercado!</p>
                        <Link :href="route('products.index')" class="btn-primary mt-4 inline-block">Ir al Mercado</Link>
                    </div>
                </div>

                <!-- ==================== TELÉFONOS ==================== -->
                <div v-if="activeTab === 'telefonos'" class="card space-y-4">
                    <h3 class="section-title mb-2">📞 Números de Contacto</h3>
                    <form @submit.prevent="addPhone" class="flex flex-col sm:flex-row gap-2">
                        <input v-model="phoneForm.phone_number" type="text" placeholder="Número de Celular" class="input-field" required />
                        <select v-model="phoneForm.type" class="input-field">
                            <option>Principal (WhatsApp)</option>
                            <option>Fijo Domicilio</option>
                            <option>Trabajo</option>
                        </select>
                        <label class="flex items-center gap-1 text-xs">
                            <input v-model="phoneForm.is_default" type="checkbox" class="rounded" />
                            Predeterminado
                        </label>
                        <button type="submit" :disabled="phoneForm.processing" class="btn-dark">Añadir</button>
                    </form>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <div v-for="phone in phones" :key="phone.id" class="flex justify-between items-center p-2.5 bg-gray-50 dark:bg-gray-950 rounded-lg border dark:border-gray-800">
                            <div v-if="editingPhone === phone.id" class="flex flex-1 gap-2 items-center">
                                <input v-model="phoneForm.phone_number" type="text" class="input-field flex-1" />
                                <select v-model="phoneForm.type" class="input-field">
                                    <option>Principal (WhatsApp)</option>
                                    <option>Fijo Domicilio</option>
                                    <option>Trabajo</option>
                                </select>
                                <label class="flex items-center gap-1 text-xs">
                                    <input v-model="phoneForm.is_default" type="checkbox" class="rounded" />
                                    Predeterminado
                                </label>
                                <button @click="updatePhone(phone.id)" :disabled="phoneForm.processing" class="btn-dark text-xs px-2 py-1">Guardar</button>
                                <button @click="cancelEditPhone" class="text-xs text-gray-500">Cancelar</button>
                            </div>
                            <template v-else>
                                <span class="text-xs font-mono font-bold">
                                    {{ phone.phone_number }} ({{ phone.type }})
                                    <span v-if="phone.is_default" class="text-yellow-500 ml-1">⭐</span>
                                </span>
                                <div class="flex gap-2">
                                    <button @click="startEditPhone(phone)" class="text-[11px] text-blue-600 font-bold">Editar</button>
                                    <button @click="deletePhone(phone.id)" class="text-[11px] text-red-500 font-bold">Eliminar</button>
                                </div>
                            </template>
                        </div>
                        <p v-if="phones.length === 0" class="text-xs text-gray-400 col-span-2">No tienes teléfonos registrados.</p>
                    </div>
                </div>

                <!-- ==================== DIRECCIONES ==================== -->
                <div v-if="activeTab === 'direcciones'" class="card space-y-4">
                    <h3 class="section-title mb-2">📍 Direcciones de Entrega</h3>
                    <form @submit.prevent="addAddress" class="bg-gray-50 dark:bg-gray-950 p-4 rounded-xl border dark:border-gray-800 space-y-2">
                        <div class="grid grid-cols-3 gap-2">
                            <input v-model="addressForm.zone" type="text" placeholder="Zona/Barrio" class="input-field rounded" required />
                            <select v-model="addressForm.city" class="input-field rounded">
                                <option>La Paz</option><option>El Alto</option><option>Cochabamba</option><option>Santa Cruz</option>
                            </select>
                            <label class="flex items-center text-xs gap-1">
                                <input v-model="addressForm.is_default" type="checkbox" class="rounded" />
                                Predeterminada
                            </label>
                        </div>
                        <input v-model="addressForm.street_address" type="text" placeholder="Calle y Número de puerta" class="input-field rounded" required />
                        <input v-model="addressForm.reference" type="text" placeholder="Referencia (opcional)" class="input-field rounded" />
                        <div class="flex justify-end">
                            <button type="submit" :disabled="addressForm.processing" class="btn-dark px-3 py-1.5">Agregar Dirección</button>
                        </div>
                    </form>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <div v-for="addr in addresses" :key="addr.id" class="p-3 bg-gray-50 dark:bg-gray-950 border dark:border-gray-800 rounded-lg">
                            <div v-if="editingAddress === addr.id" class="space-y-2">
                                <div class="grid grid-cols-3 gap-2">
                                    <input v-model="addressForm.zone" type="text" placeholder="Zona/Barrio" class="input-field rounded" required />
                                    <select v-model="addressForm.city" class="input-field rounded">
                                        <option>La Paz</option><option>El Alto</option><option>Cochabamba</option><option>Santa Cruz</option>
                                    </select>
                                    <label class="flex items-center text-xs gap-1">
                                        <input v-model="addressForm.is_default" type="checkbox" class="rounded" />
                                        Predeterminada
                                    </label>
                                </div>
                                <input v-model="addressForm.street_address" type="text" placeholder="Calle y Número de puerta" class="input-field rounded" required />
                                <input v-model="addressForm.reference" type="text" placeholder="Referencia (opcional)" class="input-field rounded" />
                                <div class="flex gap-2 justify-end">
                                    <button @click="updateAddress(addr.id)" :disabled="addressForm.processing" class="btn-dark text-xs px-2 py-1">Guardar</button>
                                    <button @click="cancelEditAddress" class="text-xs text-gray-500">Cancelar</button>
                                </div>
                            </div>
                            <template v-else>
                                <span class="text-[10px] bg-red-100 text-red-800 font-bold px-1.5 py-0.5 rounded">
                                    {{ addr.is_default ? '⭐ Principal' : '📍 Dirección' }}
                                </span>
                                <p class="text-xs font-bold mt-1">{{ addr.street_address }}</p>
                                <p class="text-[11px] text-gray-400">Zona {{ addr.zone }} ({{ addr.city }})</p>
                                <p v-if="addr.reference" class="text-[11px] text-gray-400">Ref: {{ addr.reference }}</p>
                                <div class="flex gap-2 mt-2">
                                    <button @click="startEditAddress(addr)" class="text-[11px] text-blue-600 font-bold">Editar</button>
                                    <button @click="deleteAddress(addr.id)" class="text-[11px] text-red-500 font-bold">Eliminar</button>
                                </div>
                            </template>
                        </div>
                        <p v-if="addresses.length === 0" class="text-xs text-gray-400 col-span-2">No tienes direcciones registradas.</p>
                    </div>
                </div>

                <!-- ==================== NIT / FACTURACIÓN ==================== -->
                <div v-if="activeTab === 'nit'" class="card space-y-4">
                    <h3 class="section-title mb-2">💼 Datos de Facturación</h3>
                    <form @submit.prevent="addTaxData" class="bg-gray-50 dark:bg-gray-950 p-4 rounded-xl border dark:border-gray-800 space-y-2">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <input v-model="taxForm.nit_or_ci" type="text" placeholder="NIT o C.I." class="input-field rounded" required />
                            <input v-model="taxForm.business_name" type="text" placeholder="Razón Social" class="input-field rounded" required />
                            <input v-model="taxForm.alias" type="text" placeholder="Alias (opcional)" class="input-field rounded" />
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <input v-model="taxForm.tax_regimen" type="text" placeholder="Régimen Tributario" class="input-field rounded" />
                            <label class="flex items-center text-xs gap-1">
                                <input v-model="taxForm.is_default" type="checkbox" class="rounded" />
                                Predeterminado
                            </label>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" :disabled="taxForm.processing" class="btn-dark px-3 py-1.5">Agregar NIT</button>
                        </div>
                    </form>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <div v-for="tax in taxData" :key="tax.id" class="p-3 bg-gray-50 dark:bg-gray-950 border dark:border-gray-800 rounded-lg">
                            <div v-if="editingTax === tax.id" class="space-y-2">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <input v-model="taxForm.nit_or_ci" type="text" placeholder="NIT o C.I." class="input-field rounded" required />
                                    <input v-model="taxForm.business_name" type="text" placeholder="Razón Social" class="input-field rounded" required />
                                    <input v-model="taxForm.alias" type="text" placeholder="Alias (opcional)" class="input-field rounded" />
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <input v-model="taxForm.tax_regimen" type="text" placeholder="Régimen Tributario" class="input-field rounded" />
                                    <label class="flex items-center text-xs gap-1">
                                        <input v-model="taxForm.is_default" type="checkbox" class="rounded" />
                                        Predeterminado
                                    </label>
                                </div>
                                <div class="flex gap-2 justify-end">
                                    <button @click="updateTax(tax.id)" :disabled="taxForm.processing" class="btn-dark text-xs px-2 py-1">Guardar</button>
                                    <button @click="cancelEditTax" class="text-xs text-gray-500">Cancelar</button>
                                </div>
                            </div>
                            <template v-else>
                                <span class="text-[10px] bg-red-100 text-red-800 font-bold px-1.5 py-0.5 rounded">
                                    {{ tax.is_default ? '⭐ Principal' : '📄 Facturación' }}
                                </span>
                                <p class="text-xs font-bold mt-1">{{ tax.nit_or_ci }}</p>
                                <p class="text-xs text-gray-700 dark:text-gray-300">{{ tax.business_name }}</p>
                                <p class="text-[11px] text-gray-400" v-if="tax.tax_regimen">Régimen: {{ tax.tax_regimen }}</p>
                                <p class="text-[11px] text-gray-400" v-if="tax.alias">Alias: {{ tax.alias }}</p>
                                <div class="flex gap-2 mt-2">
                                    <button @click="startEditTax(tax)" class="text-[11px] text-blue-600 font-bold">Editar</button>
                                    <button @click="deleteTaxData(tax.id)" class="text-[11px] text-red-500 font-bold">Eliminar</button>
                                </div>
                            </template>
                        </div>
                        <p v-if="taxData.length === 0" class="text-xs text-gray-400 col-span-2">No tienes datos fiscales registrados.</p>
                    </div>
                </div>

                <!-- ==================== PERFIL ==================== -->
                <div v-if="activeTab === 'perfil'" class="card space-y-6">
                    <h3 class="section-title mb-4">👤 Mi Perfil</h3>
                    <form @submit.prevent="updateProfile" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold">Nombres</label>
                                <input v-model="profileForm.first_name" type="text" class="input-field" required />
                            </div>
                            <div>
                                <label class="text-xs font-bold">Apellido Paterno</label>
                                <input v-model="profileForm.paternal_last_name" type="text" class="input-field" required />
                            </div>
                            <div>
                                <label class="text-xs font-bold">Apellido Materno</label>
                                <input v-model="profileForm.maternal_last_name" type="text" class="input-field" />
                            </div>
                            <div>
                                <label class="text-xs font-bold">CI</label>
                                <input v-model="profileForm.ci_number" type="text" class="input-field" required />
                            </div>
                            <div>
                                <label class="text-xs font-bold">Extensión</label>
                                <input v-model="profileForm.ci_extension" type="text" maxlength="2" class="input-field" required />
                            </div>
                            <div>
                                <label class="text-xs font-bold">Fecha de Nacimiento</label>
                                <input v-model="profileForm.birth_date" type="date" class="input-field" required />
                            </div>
                            <div>
                                <label class="text-xs font-bold">Correo Electrónico</label>
                                <input v-model="profileForm.email" type="email" class="input-field" required />
                            </div>
                            <div>
                                <label class="text-xs font-bold">Avatar</label>
                                <input type="file" @input="profileForm.avatar = $event.target.files[0]" class="text-xs" />
                            </div>
                        </div>
                        <div class="flex justify-end pt-4 border-t dark:border-gray-800">
                            <button type="submit" :disabled="profileForm.processing" class="btn-primary">Actualizar Perfil</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</template>