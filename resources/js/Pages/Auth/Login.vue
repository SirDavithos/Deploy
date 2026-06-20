<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Iniciar Sesión – Punto Boliviano" />
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <!-- Logo / Identidad -->
            <div class="text-center mb-8">
                <Link href="/" class="inline-flex items-center space-x-2">
                    <span class="text-3xl font-black tracking-wider text-red-600 dark:text-red-500">PUNTO</span>
                    <span class="bg-red-600 px-2 py-0.5 text-lg font-bold text-white rounded dark:bg-red-500">BOLIVIANO</span>
                </Link>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">El corazón del arte artesanal paceño</p>
            </div>

            <!-- Tarjeta del formulario -->
            <div class="card">
                <!-- Mensaje de estado (ej. restablecimiento de contraseña) -->
                <div v-if="status" class="mb-4 text-sm font-medium text-green-600 bg-green-50 p-3 rounded-lg">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Email -->
                    <div>
                        <label for="email" class="text-xs font-bold block mb-1 text-gray-700 dark:text-gray-300">
                            Correo electrónico
                        </label>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            class="input-field"
                            placeholder="tu@correo.com"
                        />
                        <p v-if="form.errors.email" class="text-[11px] text-red-500 mt-1">{{ form.errors.email }}</p>
                    </div>

                    <!-- Contraseña -->
                    <div>
                        <label for="password" class="text-xs font-bold block mb-1 text-gray-700 dark:text-gray-300">
                            Contraseña
                        </label>
                        <input
                            id="password"
                            type="password"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            class="input-field"
                            placeholder="••••••••"
                        />
                        <p v-if="form.errors.password" class="text-[11px] text-red-500 mt-1">{{ form.errors.password }}</p>
                    </div>

                    <!-- Recordarme + Olvidé contraseña -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center text-xs gap-2">
                            <input type="checkbox" v-model="form.remember" class="rounded border-gray-300 text-red-600 focus:ring-red-500" />
                            <span class="text-gray-600 dark:text-gray-400">Recordarme</span>
                        </label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-xs font-medium text-red-600 hover:text-red-500 dark:text-red-400 dark:hover:text-red-300"
                        >
                            ¿Olvidaste tu contraseña?
                        </Link>
                    </div>

                    <!-- Botón de envío -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="btn-primary w-full py-2.5 text-sm"
                    >
                        Iniciar sesión
                    </button>
                </form>

                <!-- Enlace a registro -->
                <p class="mt-6 text-center text-xs text-gray-500 dark:text-gray-400">
                    ¿No tienes una cuenta?
                    <Link :href="route('register')" class="font-bold text-red-600 hover:text-red-500 dark:text-red-400">
                        Regístrate aquí
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>