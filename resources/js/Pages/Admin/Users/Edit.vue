<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
defineOptions({ layout: AdminLayout });
const props = defineProps({ editUser: Object });

const activeTab = ref('profile');

// ==================== Perfil ====================
const profileForm = useForm({
    first_name: props.editUser.first_name,
    paternal_last_name: props.editUser.paternal_last_name,
    maternal_last_name: props.editUser.maternal_last_name || '',
    ci_number: props.editUser.ci_number,
    ci_extension: props.editUser.ci_extension,
    birth_date: props.editUser.birth_date,
    email: props.editUser.email,
    status: props.editUser.status,
    password: '',
    password_confirmation: '',
});

const updateProfile = () => {
    profileForm.patch(route('admin.users.update', props.editUser.id), {
        preserveScroll: true,
        onSuccess: () => profileForm.reset('password', 'password_confirmation'),
    });
};

// ==================== Teléfonos ====================
const phones = ref(props.editUser.phones);
const phoneForm = useForm({ phone_number: '', type: 'Principal (WhatsApp)' });
const editingPhone = ref(null);

const addPhone = () => {
    phoneForm.post(route('admin.users.phones.store', props.editUser.id), {
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
    phoneForm.patch(route('admin.users.phones.update', phoneId), {
        preserveScroll: true,
        onSuccess: () => { editingPhone.value = null; phoneForm.reset(); },
    });
};
const deletePhone = (id) => {
    if (confirm('¿Eliminar este teléfono?')) {
        useForm({}).delete(route('admin.users.phones.destroy', id), { preserveScroll: true });
    }
};

// ==================== Direcciones ====================
const addresses = ref(props.editUser.addresses);
const addressForm = useForm({ city: 'La Paz', zone: '', street_address: '', reference: '', is_default: false });
const editingAddress = ref(null);

const addAddress = () => {
    addressForm.post(route('admin.users.addresses.store', props.editUser.id), {
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
    addressForm.patch(route('admin.users.addresses.update', addrId), {
        preserveScroll: true,
        onSuccess: () => { editingAddress.value = null; addressForm.reset(); addressForm.city = 'La Paz'; },
    });
};
const deleteAddress = (id) => {
    if (confirm('¿Eliminar esta dirección?')) {
        useForm({}).delete(route('admin.users.addresses.destroy', id), { preserveScroll: true });
    }
};

// ==================== NIT ====================
const taxData = ref(props.editUser.tax_data || []);
const taxForm = useForm({ nit_or_ci: '', business_name: '', tax_regimen: '', alias: '', is_default: false });
const editingTax = ref(null);

const addTaxData = () => {
    taxForm.post(route('admin.users.tax-data.store', props.editUser.id), {
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
    taxForm.patch(route('admin.users.tax-data.update', taxId), {
        preserveScroll: true,
        onSuccess: () => { editingTax.value = null; taxForm.reset(); },
    });
};
const deleteTaxData = (id) => {
    if (confirm('¿Eliminar este dato fiscal?')) {
        useForm({}).delete(route('admin.users.tax-data.destroy', id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Editar Usuario" />
    <div class="p-8">
        <h1 class="text-xl font-bold mb-4">Editando a {{ editUser.first_name }} {{ editUser.paternal_last_name }}</h1>

        <!-- Pestañas -->
        <div class="flex gap-4 mb-6">
            <button @click="activeTab = 'profile'" :class="activeTab === 'profile' ? 'tab-active' : 'tab-inactive'">Perfil</button>
            <button @click="activeTab = 'phones'" :class="activeTab === 'phones' ? 'tab-active' : 'tab-inactive'">Teléfonos ({{ phones.length }})</button>
            <button @click="activeTab = 'addresses'" :class="activeTab === 'addresses' ? 'tab-active' : 'tab-inactive'">Direcciones ({{ addresses.length }})</button>
            <button @click="activeTab = 'tax'" :class="activeTab === 'tax' ? 'tab-active' : 'tab-inactive'">NIT ({{ taxData.length }})</button>
        </div>

        <!-- Contenido de pestañas -->
        <div class="card">
            <!-- Perfil -->
            <div v-if="activeTab === 'profile'" class="space-y-4">
                <form @submit.prevent="updateProfile" class="grid grid-cols-2 gap-4">
                    <div><label class="text-xs font-bold">Nombres</label><input v-model="profileForm.first_name" class="input-field" required /></div>
                    <div><label class="text-xs font-bold">Ap. Paterno</label><input v-model="profileForm.paternal_last_name" class="input-field" required /></div>
                    <div><label class="text-xs font-bold">Ap. Materno</label><input v-model="profileForm.maternal_last_name" class="input-field" /></div>
                    <div><label class="text-xs font-bold">CI</label><input v-model="profileForm.ci_number" class="input-field" required /></div>
                    <div><label class="text-xs font-bold">Extensión</label><input v-model="profileForm.ci_extension" maxlength="2" class="input-field" required /></div>
                    <div><label class="text-xs font-bold">Fecha Nac.</label><input v-model="profileForm.birth_date" type="date" class="input-field" required /></div>
                    <div><label class="text-xs font-bold">Email</label><input v-model="profileForm.email" type="email" class="input-field" required /></div>
                    <div>
                        <label class="text-xs font-bold">Estado</label>
                        <select v-model="profileForm.status" class="input-field">
                            <option value="active">Activo</option>
                            <option value="inactive">Inactivo</option>
                            <option value="suspended">Suspendido</option>
                        </select>
                    </div>
                    <div><label class="text-xs font-bold">Contraseña (opcional)</label><input v-model="profileForm.password" type="password" class="input-field" /></div>
                    <div><label class="text-xs font-bold">Confirmar Contraseña</label><input v-model="profileForm.password_confirmation" type="password" class="input-field" /></div>
                    <div class="col-span-2 flex justify-end"><button type="submit" :disabled="profileForm.processing" class="btn-primary">Actualizar Perfil</button></div>
                </form>
            </div>

            <!-- Teléfonos -->
            <div v-if="activeTab === 'phones'" class="space-y-4">
                <form @submit.prevent="addPhone" class="flex gap-2">
                    <input v-model="phoneForm.phone_number" type="text" placeholder="Número" class="input-field" required />
                    <select v-model="phoneForm.type" class="input-field">
                        <option>Principal (WhatsApp)</option><option>Fijo Domicilio</option><option>Trabajo</option>
                    </select>
                    <button type="submit" :disabled="phoneForm.processing" class="btn-dark">Añadir</button>
                </form>
                <div v-for="phone in phones" :key="phone.id" class="flex justify-between items-center p-2 border dark:border-gray-800 rounded">
                    <div v-if="editingPhone === phone.id" class="flex gap-2 flex-1">
                        <input v-model="phoneForm.phone_number" type="text" class="input-field flex-1" />
                        <select v-model="phoneForm.type" class="input-field">
                            <option>Principal (WhatsApp)</option><option>Fijo Domicilio</option><option>Trabajo</option>
                        </select>
                        <button @click="updatePhone(phone.id)" :disabled="phoneForm.processing" class="btn-dark text-xs px-2 py-1">Guardar</button>
                        <button @click="cancelEditPhone" class="text-xs text-gray-500">Cancelar</button>
                    </div>
                    <template v-else>
                        <span class="text-xs font-mono">{{ phone.phone_number }} ({{ phone.type }})</span>
                        <div class="flex gap-2">
                            <button @click="startEditPhone(phone)" class="text-xs text-blue-600">Editar</button>
                            <button @click="deletePhone(phone.id)" class="text-xs text-red-500">Eliminar</button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Direcciones -->
            <div v-if="activeTab === 'addresses'" class="space-y-4">
                <form @submit.prevent="addAddress" class="bg-gray-50 dark:bg-gray-800 p-4 rounded space-y-2">
                    <div class="grid grid-cols-3 gap-2">
                        <input v-model="addressForm.zone" type="text" placeholder="Zona" class="input-field" required />
                        <select v-model="addressForm.city" class="input-field">
                            <option>La Paz</option><option>El Alto</option><option>Cochabamba</option><option>Santa Cruz</option>
                        </select>
                        <label class="flex items-center text-xs gap-1"><input v-model="addressForm.is_default" type="checkbox" /> Predeterminada</label>
                    </div>
                    <input v-model="addressForm.street_address" type="text" placeholder="Calle y Número" class="input-field" required />
                    <input v-model="addressForm.reference" type="text" placeholder="Referencia" class="input-field" />
                    <div class="flex justify-end"><button type="submit" :disabled="addressForm.processing" class="btn-dark text-xs px-3 py-1.5">Agregar</button></div>
                </form>
                <div v-for="addr in addresses" :key="addr.id" class="p-3 border dark:border-gray-800 rounded">
                    <div v-if="editingAddress === addr.id" class="space-y-2">
                        <!-- formulario de edición similar al de alta -->
                        <div class="grid grid-cols-3 gap-2">
                            <input v-model="addressForm.zone" type="text" class="input-field" required />
                            <select v-model="addressForm.city" class="input-field"><option>La Paz</option><option>El Alto</option></select>
                            <label class="flex items-center text-xs gap-1"><input v-model="addressForm.is_default" type="checkbox" /> Predeterminada</label>
                        </div>
                        <input v-model="addressForm.street_address" type="text" class="input-field" required />
                        <input v-model="addressForm.reference" type="text" class="input-field" />
                        <div class="flex justify-end gap-2">
                            <button @click="updateAddress(addr.id)" :disabled="addressForm.processing" class="btn-dark text-xs px-2 py-1">Guardar</button>
                            <button @click="cancelEditAddress" class="text-xs text-gray-500">Cancelar</button>
                        </div>
                    </div>
                    <template v-else>
                        <span class="text-xs bg-red-100 text-red-800 px-1.5 py-0.5 rounded">{{ addr.is_default ? '⭐ Principal' : '📍' }}</span>
                        <p class="text-xs font-bold">{{ addr.street_address }}</p>
                        <p class="text-[11px] text-gray-400">Zona {{ addr.zone }} ({{ addr.city }}) <span v-if="addr.reference">| Ref: {{ addr.reference }}</span></p>
                        <div class="flex gap-2 mt-1">
                            <button @click="startEditAddress(addr)" class="text-xs text-blue-600">Editar</button>
                            <button @click="deleteAddress(addr.id)" class="text-xs text-red-500">Eliminar</button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- NIT -->
            <div v-if="activeTab === 'tax'" class="space-y-4">
                <form @submit.prevent="addTaxData" class="bg-gray-50 dark:bg-gray-800 p-4 rounded space-y-2">
                    <div class="grid grid-cols-3 gap-2">
                        <input v-model="taxForm.nit_or_ci" type="text" placeholder="NIT o C.I." class="input-field" required />
                        <input v-model="taxForm.business_name" type="text" placeholder="Razón Social" class="input-field" required />
                        <input v-model="taxForm.alias" type="text" placeholder="Alias" class="input-field" />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <input v-model="taxForm.tax_regimen" type="text" placeholder="Régimen" class="input-field" />
                        <label class="flex items-center text-xs gap-1"><input v-model="taxForm.is_default" type="checkbox" /> Predeterminado</label>
                    </div>
                    <div class="flex justify-end"><button type="submit" :disabled="taxForm.processing" class="btn-dark text-xs px-3 py-1.5">Agregar NIT</button></div>
                </form>
                <div v-for="tax in taxData" :key="tax.id" class="p-3 border dark:border-gray-800 rounded">
                    <div v-if="editingTax === tax.id" class="space-y-2">
                        <div class="grid grid-cols-3 gap-2">
                            <input v-model="taxForm.nit_or_ci" type="text" class="input-field" required />
                            <input v-model="taxForm.business_name" type="text" class="input-field" required />
                            <input v-model="taxForm.alias" type="text" class="input-field" />
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <input v-model="taxForm.tax_regimen" type="text" class="input-field" />
                            <label class="flex items-center text-xs gap-1"><input v-model="taxForm.is_default" type="checkbox" /> Predeterminado</label>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button @click="updateTax(tax.id)" :disabled="taxForm.processing" class="btn-dark text-xs px-2 py-1">Guardar</button>
                            <button @click="cancelEditTax" class="text-xs text-gray-500">Cancelar</button>
                        </div>
                    </div>
                    <template v-else>
                        <span class="text-xs bg-red-100 text-red-800 px-1.5 py-0.5 rounded">{{ tax.is_default ? '⭐ Principal' : '📄' }}</span>
                        <p class="text-xs font-bold">{{ tax.nit_or_ci }}</p>
                        <p class="text-xs text-gray-600">{{ tax.business_name }}</p>
                        <p class="text-[11px] text-gray-400" v-if="tax.tax_regimen">Régimen: {{ tax.tax_regimen }}</p>
                        <p class="text-[11px] text-gray-400" v-if="tax.alias">Alias: {{ tax.alias }}</p>
                        <div class="flex gap-2 mt-1">
                            <button @click="startEditTax(tax)" class="text-xs text-blue-600">Editar</button>
                            <button @click="deleteTaxData(tax.id)" class="text-xs text-red-500">Eliminar</button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>