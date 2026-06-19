<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

// Determina la pestaña activa según la URL actual
const activeMainTab = computed(() => {
    if (page.url.startsWith('/admin/orders')) return 'orders';
    if (page.url.startsWith('/admin/shops')) return 'shops';
    return 'users';
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 transition-colors duration-200">
        <!-- Barra superior del admin -->
        <nav class="border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-800 dark:bg-gray-900 sticky top-0 z-50 shadow-sm">
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <div class="flex items-center space-x-8">
                    <Link :href="route('admin.users.index')" class="text-xl font-black text-red-600 dark:text-red-400 tracking-tight">
                        ADMIN PANEL
                    </Link>
                    <div class="flex space-x-2">
                        <Link
                            :href="route('admin.users.index')"
                            :class="activeMainTab === 'users' ? 'tab-active' : 'tab-inactive'"
                            class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors"
                        >
                            👥 Usuarios
                        </Link>
                        <Link
                            :href="route('admin.shops.index')"
                            :class="activeMainTab === 'shops' ? 'tab-active' : 'tab-inactive'"
                            class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors"
                        >
                            🏪 Tiendas
                        </Link>
                        <Link
                            :href="route('admin.orders.index')"
                            :class="activeMainTab === 'orders' ? 'tab-active' : 'tab-inactive'"
                            class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors"
                        >
                            📦 Pedidos
                        </Link>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        {{ $page.props.auth.user.first_name }} <span class="text-xs text-red-500">(Admin)</span>
                    </span>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-xs font-semibold text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400"
                    >
                        Salir
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Contenido dinámico -->
        <main class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>