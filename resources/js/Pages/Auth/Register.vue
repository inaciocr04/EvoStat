<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import {Head, Link, useForm} from '@inertiajs/vue3';

const form = useForm({
    lastname: '',
    firstname: '',
    gender: '',
    pseudo: '',
    age: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <!--    <GuestLayout>-->

    <Head title="Register"/>

    <div class="min-h-screen flex items-center justify-center bg-evogradient">
        <div class="bg-white rounded-2xl shadow-lg p-10 w-full max-w-2xl">
            <div class="flex flex-col items-center mb-6">
                <a href="/">
                    <img src="/img/logo_evostat.png" alt="EvoStat" class=" h-20 mb-2"/>
                </a>
                <h2 class="text-2xl font-bold text-center text-gray-800">Rejoins EvoStat</h2>
                <p class="text-center text-gray-600">Commence ton évolution</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Grid à 3 colonnes pour nom / prénom / sexe -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <TextInput v-model="form.lastname" placeholder="Nom"/>
                    <TextInput v-model="form.firstname" placeholder="Prénom"/>
                    <select v-model="form.gender" class="border rounded-md px-3 py-2 w-full text-gray-700">
                        <option value="" disabled selected>Sexe</option>
                        <option value="male">Homme</option>
                        <option value="female">Femme</option>
                        <option value="other">Autre</option>
                    </select>
                </div>

                <!-- Pseudo + âge -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <TextInput v-model="form.pseudo" placeholder="Pseudo"/>
                    <TextInput v-model="form.age" placeholder="Âge" type="number"/>
                </div>

                <div class="flex flex-col gap-4">
                    <TextInput v-model="form.email" placeholder="Adresse mail" type="email"/>
                    <TextInput v-model="form.password" placeholder="Mot de passe" type="password"/>
                    <TextInput v-model="form.password_confirmation" placeholder="Confirmation mot de passe"
                               type="password"/>
                </div>
                <!-- Erreurs -->
                <div v-if="Object.keys(form.errors).length" class="text-red-500 text-sm">
                    <div v-for="(error, key) in form.errors" :key="key">{{ error }}</div>
                </div>

                <div class="mt-6 flex justify-center">
                    <div
                        class="group w-full sm:w-1/3 p-2 rounded-md bg-gradient-to-r from-evoblue to-evogreen transition duration-300 ease-in-out"
                    >
                        <button
                            class="w-full bg-transparent text-white text-4xl font-semibold py-2 px-4 rounded-md transition duration-300 ease-in-out"
                        >
                            GO
                        </button>
                    </div>
                </div>


                <div class="text-center mt-4">
                    <Link :href="route('login')" class="text-sm text-gray-600 hover:underline">
                        Déjà inscrit ? Connecte-toi
                    </Link>
                </div>
            </form>
        </div>
    </div>
    <!--    </GuestLayout>-->
</template>
