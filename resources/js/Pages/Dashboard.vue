<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    auth: Object,
    phones: Array,
    addresses: Array,
    taxData: Array,
    orders: Array,
    roles: Array,          // slugs de los roles del usuario
    userShop: Object|null, // tienda del usuario si es artesano
});

// --- CONTROL DE PESTAÑAS ---
const activeTab = ref('compras');

// ==================== TELÉFONOS ====================
const phoneForm = useForm({ phone_number: '', type: 'Principal (WhatsApp)' });
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
};

const cancelEditPhone = () => {
    editingPhone.value = null;
    phoneForm.reset();
};

const updatePhone = (phoneId) => {
    phoneForm.patch(route('user.phones.update', phoneId), {
        preserveScroll: true,
        onSuccess: () => {
            editingPhone.value = null;
            phoneForm.reset();
        },
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
        onSuccess: () => {
            addressForm.reset();
            addressForm.city = 'La Paz';
        },
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
        onSuccess: () => {
            editingAddress.value = null;
            addressForm.reset();
            addressForm.city = 'La Paz';
        },
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
        onSuccess: () => {
            editingTax.value = null;
            taxForm.reset();
        },
    });
};

const deleteTaxData = (id) => {
    if (confirm('¿Eliminar este dato fiscal?')) {
        useForm({}).delete(route('user.tax-data.destroy', id), { preserveScroll: true });
    }
};

// ==================== PERFIL ====================
const user = usePage().props.auth.user;
const profileForm = useForm({
    first_name: user.first_name || '',
    paternal_last_name: user.paternal_last_name || '',
    maternal_last_name: user.maternal_last_name || '',
    ci_number: user.ci_number || '',
    ci_extension: user.ci_extension || '',
    birth_date: user.birth_date || '',
    email: user.email || '',
    avatar: null,
});

const updateProfile = () => {
    profileForm.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => profileForm.reset('avatar'),
    });
};

const safeRoute = (name) => { try { return route(name); } catch (e) { return '#'; } };
</script>

<template>
    <Head title="Mi Panel - Punto Boliviano" />
    <div class="min-h-screen bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-gray-100 transition-colors duration-200">
        <!-- Barra de navegación con accesos rápidos según rol -->
        <nav class="border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-800 dark:bg-gray-900 sticky top-0 z-50 shadow-sm">
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <div class="flex items-center space-x-6">
                    <Link :href="safeRoute('welcome')" class="flex items-center space-x-2">
                        <span class="text-2xl font-black tracking-wider text-red-600 dark:text-red-500">PUNTO</span>
                        <span class="bg-red-600 px-2 py-0.5 text-sm font-bold text-white rounded dark:bg-red-500">BOLIVIANO</span>
                    </Link>
                    <!-- Botones dinámicos según rol -->
                    <div class="flex items-center gap-2">
                        <Link v-if="roles && roles.includes('admin')" :href="route('admin.users.index')"
                            class="btn-outline text-xs px-3 py-1.5">
                            ⚙️ Panel Admin
                        </Link>
                        <template v-if="roles && roles.includes('artisan')">
                            <Link v-if="userShop" :href="route('shop.show', userShop.id)"
                                class="btn-outline text-xs px-3 py-1.5">
                                🏪 Mi Tienda
                            </Link>
                            <Link v-else :href="route('shop.create')"
                                class="btn-outline text-xs px-3 py-1.5">
                                🏪 Abrir Tienda
                            </Link>
                        </template>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        Hola, <strong class="text-gray-900 dark:text-white">{{ user.first_name }}</strong>
                    </span>
                    <Link :href="safeRoute('logout')" method="post" as="button" class="text-xs font-semibold text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400">
                        Cerrar Sesión
                    </Link>
                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-12 lg:gap-x-8">
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

                        <div class="card-section">
                            <div class="p-6 border-b dark:border-gray-800">
                                <h3 class="section-title">Historial de Pedidos</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Últimas compras realizadas.</p>
                            </div>
                            <div class="divide-y dark:divide-gray-800">
                                <div v-if="orders && orders.length > 0">
                                    <div v-for="order in orders" :key="order.id" class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        <div class="space-y-1">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-[10px] font-mono bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded text-gray-400">#{{ order.id }}</span>
                                                <span class="text-xs font-bold">{{ order.shop?.name }}</span>
                                            </div>
                                            <p class="text-[11px] text-gray-500">Total: {{ order.total }} BOB • {{ order.status }}</p>
                                            <ul class="text-[10px] text-gray-500">
                                                <li v-for="item in order.items" :key="item.id">
                                                    {{ item.product?.title }} (x{{ item.quantity }})
                                                </li>
                                            </ul>
                                            <!-- Datos de facturación asociados al pedido -->
                                            <p v-if="order.tax_data" class="text-[10px] text-gray-400 mt-1">
                                                🧾 Facturado a: {{ order.tax_data.nit_or_ci }} – {{ order.tax_data.business_name }}
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span :class="{
                                                'badge-pending': order.status === 'pending',
                                                'badge-success': order.status === 'delivered' || order.status === 'confirmed',
                                            }">{{ order.status }}</span>
                                            <!-- Enlace de factura desactivado hasta tener la ruta -->
                                            <Link v-if="order.tax_data" :href="route('orders.invoice', order.id)" class="text-xs text-blue-600 hover:underline ml-2">📄 Factura</Link>                                        </div>
                                    </div>
                                </div>
                                <div v-else class="p-6 text-center text-gray-400 text-sm">
                                    No tienes pedidos aún. ¡Compra algo en el mercado!
                                </div>
                            </div>
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
                                    <button @click="updatePhone(phone.id)" :disabled="phoneForm.processing" class="btn-dark text-xs px-2 py-1">Guardar</button>
                                    <button @click="cancelEditPhone" class="text-xs text-gray-500 hover:text-gray-700">Cancelar</button>
                                </div>
                                <template v-else>
                                    <span class="text-xs font-mono font-bold">{{ phone.phone_number }} ({{ phone.type }})</span>
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
                                        <button @click="cancelEditAddress" class="text-xs text-gray-500 hover:text-gray-700">Cancelar</button>
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
                                        <button @click="cancelEditTax" class="text-xs text-gray-500 hover:text-gray-700">Cancelar</button>
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
        </main>
    </div>
</template>