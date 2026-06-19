<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';

const user = usePage().props.auth.user; // Breeze pasa el usuario autenticado

const form = useForm({
    first_name: user.first_name || '',
    paternal_last_name: user.paternal_last_name || '',
    maternal_last_name: user.maternal_last_name || '',
    ci_number: user.ci_number || '',
    ci_extension: user.ci_extension || '',
    birth_date: user.birth_date || '',
    email: user.email || '',
    avatar: null,
});

const avatarPreview = ref(user.avatar ? `/storage/${user.avatar}` : null);

const selectNewAvatar = (e) => {
    form.avatar = e.target.files[0];
    if (form.avatar) {
        const reader = new FileReader();
        reader.onload = (e) => {
            avatarPreview.value = e.target.result;
        };
        reader.readAsDataURL(form.avatar);
    }
};

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('avatar');
            // La imagen previa se mantiene; en el próximo render el backend devuelve la nueva
        },
    });
};
</script>

<template>
    <Head title="Editar Perfil" />
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Mensaje de éxito -->
            <div v-if="$page.props.status" class="bg-green-100 text-green-700 p-4 rounded">
                {{ $page.props.status }}
            </div>

            <div class="bg-white p-6 shadow sm:rounded-lg">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Avatar -->
                    <div>
                        <InputLabel for="avatar" value="Foto de perfil" />
                        <div class="flex items-center gap-4 mt-1">
                            <img v-if="avatarPreview" :src="avatarPreview" class="h-20 w-20 rounded-full object-cover" />
                            <span v-else class="h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">Sin foto</span>
                            <input type="file" @change="selectNewAvatar" accept="image/*" class="text-sm" />
                        </div>
                        <InputError :message="form.errors.avatar" class="mt-2" />
                    </div>

                    <!-- Nombres y apellidos -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <InputLabel for="first_name" value="Nombres" />
                            <TextInput id="first_name" v-model="form.first_name" required />
                            <InputError :message="form.errors.first_name" />
                        </div>
                        <div>
                            <InputLabel for="paternal_last_name" value="Apellido Paterno" />
                            <TextInput id="paternal_last_name" v-model="form.paternal_last_name" required />
                            <InputError :message="form.errors.paternal_last_name" />
                        </div>
                        <div>
                            <InputLabel for="maternal_last_name" value="Apellido Materno" />
                            <TextInput id="maternal_last_name" v-model="form.maternal_last_name" />
                            <InputError :message="form.errors.maternal_last_name" />
                        </div>
                    </div>

                    <!-- CI y extensión -->
                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2">
                            <InputLabel for="ci_number" value="Número de Cédula" />
                            <TextInput id="ci_number" v-model="form.ci_number" required />
                            <InputError :message="form.errors.ci_number" />
                        </div>
                        <div>
                            <InputLabel for="ci_extension" value="Extensión" />
                            <TextInput id="ci_extension" v-model="form.ci_extension" maxlength="2" placeholder="LP, CB..." required />
                            <InputError :message="form.errors.ci_extension" />
                        </div>
                    </div>

                    <!-- Fecha de nacimiento -->
                    <div>
                        <InputLabel for="birth_date" value="Fecha de Nacimiento" />
                        <TextInput id="birth_date" type="date" v-model="form.birth_date" required />
                        <InputError :message="form.errors.birth_date" />
                    </div>

                    <!-- Correo -->
                    <div>
                        <InputLabel for="email" value="Correo Electrónico" />
                        <TextInput id="email" type="email" v-model="form.email" required />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="flex items-center gap-4">
                        <PrimaryButton :disabled="form.processing">Guardar Cambios</PrimaryButton>
                    </div>
                </form>
            </div>

            <!-- Sección de cambio de contraseña (la dejamos como Breeze la genera, no la tocamos por ahora) -->
            <UpdatePasswordForm class="mt-6" />
        </div>
    </div>
</template>