<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import {Head, Link, useForm} from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
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
    <!--    <GuestLayout>-->
    <Head title="Log in"/>

    <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
        {{ status }}
    </div>

    <div class="min-h-screen flex items-center justify-center bg-evogradient p-4">
        <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8 lg:p-10 w-full max-w-md lg:max-w-2xl">
            <div class="flex flex-col items-center mb-4 sm:mb-6">
                <a href="/">
                    <img src="/img/logo_evostat.png" alt="EvoStat" class="h-16 sm:h-20 mb-2"/>
                </a>
                <h2 class="text-xl sm:text-2xl font-bold text-center text-gray-800">Merci d'être la</h2>
                <p class="text-center text-gray-600 text-sm sm:text-base">Continue ton évolution</p>
            </div>
            <form @submit.prevent="submit">
                <div>
                    <InputLabel for="email" value="Email"/>

                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                    />

                    <InputError class="mt-2" :message="form.errors.email"/>
                </div>

                <div class="mt-4">
                    <InputLabel for="password" value="Password"/>

                    <TextInput
                        id="password"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                    />

                    <InputError class="mt-2" :message="form.errors.password"/>
                </div>

                <div class="mt-4 block">
                    <label class="flex items-center">
                        <Checkbox name="remember" v-model:checked="form.remember"/>
                        <span class="ms-2 text-sm text-gray-600"
                        >Remember me</span
                        >
                    </label>
                </div>

                <div class="mt-4 flex flex-col sm:flex-row items-center justify-end gap-3">
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="rounded-md text-xs sm:text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Forgot your password?
                    </Link>

                    <PrimaryButton
                        class="w-full sm:w-auto bg-evogradient"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Log in
                    </PrimaryButton>
                </div>

            </form>
        </div>
    </div>
    <!--    </GuestLayout>-->
</template>
