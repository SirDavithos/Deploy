<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// 1. Datos del Formulario
const form = useForm({
    first_name: '',
    paternal_last_name: '',
    maternal_last_name: '',
    ci_number: '',
    ci_extension: '',
    birth_date: '',
    email: '',
    password: '',
    password_confirmation: '',
});

// 2. Estados para los ojitos de la contraseña
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

// 3. Lógica Frontend: Calcular dinámicamente la fecha máxima para ser mayor de edad
const maxBirthDate = computed(() => {
    const today = new Date();
    const year = today.getFullYear() - 18; // Restamos 18 años al año actual
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`; // Retorna formato YYYY-MM-DD requerido por el input date
});

// 4. Filtros en tiempo real (Impiden la escritura errónea)
const filterLettersOnly = (field) => {
    // Reemplaza cualquier cosa que NO sea una letra (incluye espacios, eñes y acentos) por vacío
    form[field] = form[field].replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
};

const filterNumbersOnly = () => {
    // Reemplaza cualquier cosa que NO sea un número plano por vacío
    form.ci_number = form.ci_number.replace(/[^0-9]/g, '');
};

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Registro - Punto Boliviano" />

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="first_name" value="Nombres" />
                <TextInput
                    id="first_name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.first_name"
                    @input="filterLettersOnly('first_name')"
                    required
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.first_name" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="paternal_last_name" value="Apellido Paterno" />
                    <TextInput
                        id="paternal_last_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.paternal_last_name"
                        @input="filterLettersOnly('paternal_last_name')"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.paternal_last_name" />
                </div>
                <div>
                    <InputLabel for="maternal_last_name" value="Apellido Materno (Opcional)" />
                    <TextInput
                        id="maternal_last_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.maternal_last_name"
                        @input="filterLettersOnly('maternal_last_name')"
                    />
                    <InputError class="mt-2" :message="form.errors.maternal_last_name" />
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2">
                    <InputLabel for="ci_number" value="Carnet de Identidad (C.I.)" />
                    <TextInput
                        id="ci_number"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.ci_number"
                        @input="filterNumbersOnly"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.ci_number" />
                </div>
                <div>
                    <InputLabel for="ci_extension" value="Exp." />
                    <select
                        id="ci_extension"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-red-600 dark:focus:ring-red-600"
                        v-model="form.ci_extension"
                        required
                    >
                        <option value="" disabled selected>--</option>
                        <option value="LP">LP</option>
                        <option value="CB">CB</option>
                        <option value="SC">SC</option>
                        <option value="OR">OR</option>
                        <option value="PT">PT</option>
                        <option value="CH">CH</option>
                        <option value="TJ">TJ</option>
                        <option value="BE">BE</option>
                        <option value="PD">PD</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.ci_extension" />
                </div>
            </div>

            <div>
                <InputLabel for="birth_date" value="Fecha de Nacimiento" />
                <TextInput
                    id="birth_date"
                    type="date"
                    class="mt-1 block w-full"
                    v-model="form.birth_date"
                    :max="maxBirthDate"
                    required
                />
                <div class="text-xs text-gray-500 mt-1 dark:text-gray-400">Debes tener al menos 18 años de edad.</div>
                <InputError class="mt-2" :message="form.errors.birth_date" />
            </div>

            <div>
                <InputLabel for="email" value="Correo Electrónico" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="password" value="Contraseña" />
                    <div class="relative mt-1">
                        <TextInput
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            class="block w-full pr-10"
                            v-model="form.password"
                            required
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                        >
                            <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.644m19.073 0a1.012 1.012 0 0 1 0 .644M15.958 13.512a3.5 3.5 0 1 1-6.916-4.915 3.5 3.5 0 0 1 6.916 4.915Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 9.88a3 3 0 1 1 4.24 4.24m-4.24-4.24L1.05 1.05m14.06 14.06L22.95 22.95" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.644m19.073 0a1.012 1.012 0 0 1 0 .644M12 6.75c-4.32 0-8 3.172-10 5.25 2 2.078 5.68 5.25 10 5.25s8-3.172 10-5.25c-2-2.078-5.68-5.25-10-5.25Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </button>
                    </div>
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div>
                    <InputLabel for="password_confirmation" value="Confirmar Contraseña" />
                    <div class="relative mt-1">
                        <TextInput
                            id="password_confirmation"
                            :type="showPasswordConfirmation ? 'text' : 'password'"
                            class="block w-full pr-10"
                            v-model="form.password_confirmation"
                            required
                        />
                        <button
                            type="button"
                            @click="showPasswordConfirmation = !showPasswordConfirmation"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                        >
                            <svg v-if="showPasswordConfirmation" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.644m19.073 0a1.012 1.012 0 0 1 0 .644M15.958 13.512a3.5 3.5 0 1 1-6.916-4.915 3.5 3.5 0 0 1 6.916 4.915Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 9.88a3 3 0 1 1 4.24 4.24m-4.24-4.24L1.05 1.05m14.06 14.06L22.95 22.95" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.644m19.073 0a1.012 1.012 0 0 1 0 .644M12 6.75c-4.32 0-8 3.172-10 5.25 2 2.078 5.68 5.25 10 5.25s8-3.172 10-5.25c-2-2.078-5.68-5.25-10-5.25Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </button>
                    </div>
                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
                </div>
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                >
                    ¿Ya tienes una cuenta?
                </Link>

                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Registrarse
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>