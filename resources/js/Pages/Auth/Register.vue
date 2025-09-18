<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import {Head, Link, useForm} from '@inertiajs/vue3';
import gsap from 'gsap'
import {onMounted, ref} from "vue";

const form = useForm({
    lastname: '',
    firstname: '',
    gender: '',
    pseudo: '',
    age: '',
    weight: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};


const wrapper = ref(null)
const button = ref(null)
const text = ref(null)

onMounted(() => {
    wrapper.value.addEventListener('mouseenter', () => {
        gsap.to(wrapper.value, {
            background: 'linear-gradient(to right, #22c55e, #3b82f6)',
            duration: 0.5,
            ease: "power2.out"
        });
        gsap.to(button.value, {
            backgroundColor: '#ffffff',
            duration: 0.5,
            ease: "power2.out"
        });
        gsap.to(text.value, {
            color: 'transparent',
            backgroundImage: 'linear-gradient(to right, #22c55e, #3b82f6)',
            backgroundClip: 'text',
            webkitBackgroundClip: 'text',
            duration: 0.5,
            ease: "power2.out",
            onStart: () => {
                text.value.style.backgroundImage = 'linear-gradient(to right, #22c55e, #3b82f6)';
                text.value.style.webkitBackgroundClip = 'text';
                text.value.style.backgroundClip = 'text';
            }
        });
    });

    wrapper.value.addEventListener('mouseleave', () => {
        gsap.to(wrapper.value, {
            background: 'transparent',
            duration: 0.5,
            ease: "power2.out"
        });
        gsap.to(button.value, {
            backgroundColor: '',
            duration: 0.5,
            ease: "power2.out"
        });
        gsap.to(text.value, {
            color: '#ffffff',
            backgroundImage: 'none',
            duration: 0.5,
            ease: "power2.out",
            onComplete: () => {
                text.value.style.backgroundClip = '';
                text.value.style.webkitBackgroundClip = '';
            }
        });
    });
})

</script>

<template>
    <!--    <GuestLayout>-->

    <Head title="Register"/>

    <div class="min-h-screen flex items-center justify-center bg-evogradient p-4">
        <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8 lg:p-10 w-full max-w-md lg:max-w-2xl">
            <div class="flex flex-col items-center mb-4 sm:mb-6">
                <a href="/">
                    <img src="/img/logo_evostat.png" alt="EvoStat" class="h-16 sm:h-20 mb-2"/>
                </a>
                <h2 class="text-xl sm:text-2xl font-bold text-center text-gray-800">Rejoins EvoStat</h2>
                <p class="text-center text-gray-600 text-sm sm:text-base">Commence ton évolution</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Grid à 3 colonnes pour nom / prénom / sexe -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <TextInput v-model="form.lastname" placeholder="Nom"/>
                    <TextInput v-model="form.firstname" placeholder="Prénom"/>
                    <TextInput v-model="form.pseudo" placeholder="Pseudo"/>
                </div>

                <!-- Pseudo + âge -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <TextInput v-model="form.age" placeholder="Âge" type="number"/>
                    <select v-model="form.gender" class="border rounded-md px-3 py-2 w-full text-gray-700">
                        <option value="" disabled selected>Sexe</option>
                        <option value="male">Homme</option>
                        <option value="female">Femme</option>
<!--                        <option value="other">Autre</option>-->
                    </select>
                    <TextInput v-model="form.weight" placeholder="Poids" type="number"/>
                </div>

                <div class="flex flex-col gap-4">
                    <TextInput v-model="form.email" placeholder="Adresse mail" type="email"/>
                    <TextInput v-model="form.password" placeholder="Mot de passe" type="password"/>
                    <TextInput v-model="form.password_confirmation" placeholder="Confirmation mot de passe"
                               type="password"/>
                    
                    <!-- CAPTCHA Google reCAPTCHA -->
                    <div class="flex justify-center">
                        <div v-if="$page.props.recaptchaSiteKey" class="g-recaptcha" :data-sitekey="$page.props.recaptchaSiteKey"></div>
                    </div>
                </div>
                <!-- Erreurs -->
                <div v-if="Object.keys(form.errors).length" class="text-red-500 text-sm">
                    <div v-for="(error, key) in form.errors" :key="key">{{ error }}</div>
                </div>

                <div class="mt-6 flex justify-center">
                    <div
                        ref="wrapper"
                        class="p-1 rounded-secondaryRounded bg-transparent group w-full sm:w-1/3"
                        style="background: transparent; transition: background 0.5s ease-in-out;"
                    >
                        <button
                            ref="button"
                            class="w-full px-4 sm:px-6 py-2 sm:py-3 bg-evogradientleft rounded-secondaryButtonRounded"
                            style="transition: background-color 0.5s ease-in-out;"
                        >
      <span
          ref="text"
          class="text-2xl sm:text-4xl font-bold text-white"
      >
        GO
      </span>
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
