<script setup>
import {computed} from "vue";

defineOptions({layout: DefaultLayout})
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import {Head, usePage, Link} from '@inertiajs/vue3';
import NavLink from "@/Components/NavLink.vue";

const page = usePage()
// console.log('Props disponibles :', page.props)

const totalWeight = computed(() => page.props.countWeight)
const totalReps = computed(() => page.props.countReps)
const totalWorkouts = computed(() => page.props.countWorkouts)
const latestWorkouts = page.props.latestWorkouts

console.log(latestWorkouts)

const user = page.props.user

// console.log('Total soulevé:', totalWeight.value)

const formattedWeight = computed(() => {
    const weight = totalWeight.value;

    if (weight >= 1_000_000_000) {
        return (weight / 1_000_000_000).toFixed(2) + ' Mt'; // Mégatonnes
    } else if (weight >= 1_000_000) {
        return (weight / 1_000_000).toFixed(2) + ' kt'; // Kilotonnes
    } else if (weight >= 1_000) {
        return (weight / 1_000).toFixed(weight < 10_000 ? 2 : 0) + ' T'; // Tonnes
    } else {
        return weight + ' kg'; // Kilogrammes
    }
});


</script>

<template>
    <Head title="Profils"/>
    <section class="space-y-8 mt-8">
        <div class="flex flex-col justify-center items-center gap-3">
            <div class="bg-evogradienttop rounded-full p-2">
                <div
                    class="bg-evogray rounded-full w-40 h-40 flex justify-center items-center">
                    <img src="/img/logo_evostat.png" alt="">
                </div>
            </div>
            <h1 class="text-4xl text-evogray font-bold">{{ user.pseudo }}</h1>
        </div>

        <div class="w-full flex justify-center items-center">
            <div class="grid grid-cols-3 gap-6 mb-8 text-evogray">
                <div
                    class="rounded-thirdRounded shadow-evoShadow py-4 px-16 flex flex-col justify-start items-center space-y-6">
                    <p class="text-2xl">Séances</p>
                    <p class="text-4xl font-bold">{{ totalWorkouts }}</p></div>
                <div
                    class="rounded-thirdRounded shadow-evoShadow py-4 px-16 flex flex-col justify-start items-center space-y-6">
                    <p class="text-2xl">Répétitions</p>
                    <p class="text-4xl font-bold">{{ totalReps }}</p></div>
                <div
                    class="rounded-thirdRounded shadow-evoShadow py-4 px-16 flex flex-col justify-start items-center space-y-6">
                    <p class="text-2xl">Total poids</p>
                    <p class="text-4xl font-bold">{{ formattedWeight }}</p>
                </div>
            </div>
        </div>

        <div>
            <div class="w-1/4 space-y-6">
                <h2 class="text-3xl font-bold">Séances récente</h2>
                <div class="space-y-4">
                    <div v-for="lastW in latestWorkouts" :key="lastW.id"
                         class=" px-6 py-4 rounded-mainRounded bg-white shadow-evoShadow">
                        <div class="flex justify-between items-center text-evogray">
                            <h3 class="text-2xl font-bold">
                                {{ lastW.workout_template?.name ?? 'Template inconnu' }}
                            </h3>
                            <p class="text-sm text-gray-500">
                                {{ new Date(lastW.created_at).toLocaleDateString() }}
                            </p>
                        </div>
                        <p>{{ lastW.session_exercises.length }} exercices</p>
                    </div>
                    <Link
                        :href="route('workout-templates.index')"
                        class="w-full h-20 bg-evogradientleft text-white text-2xl font-bold rounded-mainRounded flex items-center justify-center"
                    >
                        Nouvelle séance ?
                    </Link>

                </div>
            </div>
            <div class="w-2/3"></div>
        </div>
    </section>
</template>

