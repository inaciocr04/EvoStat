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
    <section class="space-y-6 sm:space-y-8 mt-4 sm:mt-8 px-4 sm:px-0">
        <div class="flex flex-col justify-center items-center gap-3">
            <div class="bg-evogradienttop rounded-full p-2">
                <div
                    class="bg-evogray rounded-full w-32 h-32 sm:w-40 sm:h-40 flex justify-center items-center">
                    <img src="/img/logo_evostat.png" alt="" class="w-16 h-16 sm:w-20 sm:h-20">
                </div>
            </div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl text-evogray font-bold">{{ user.pseudo }}</h1>
        </div>

        <div class="w-full flex justify-center items-center">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8 text-evogray">
                <div
                    class="rounded-thirdRounded shadow-evoShadow py-3 sm:py-4 px-8 sm:px-16 flex flex-col justify-start items-center space-y-4 sm:space-y-6">
                    <p class="text-lg sm:text-xl lg:text-2xl">Séances</p>
                    <p class="text-2xl sm:text-3xl lg:text-4xl font-bold">{{ totalWorkouts }}</p>
                </div>
                <div
                    class="rounded-thirdRounded shadow-evoShadow py-3 sm:py-4 px-8 sm:px-16 flex flex-col justify-start items-center space-y-4 sm:space-y-6">
                    <p class="text-lg sm:text-xl lg:text-2xl">Répétitions</p>
                    <p class="text-2xl sm:text-3xl lg:text-4xl font-bold">{{ totalReps }}</p>
                </div>
                <div
                    class="rounded-thirdRounded shadow-evoShadow py-3 sm:py-4 px-8 sm:px-16 flex flex-col justify-start items-center space-y-4 sm:space-y-6">
                    <p class="text-lg sm:text-xl lg:text-2xl">Total poids</p>
                    <p class="text-2xl sm:text-3xl lg:text-4xl font-bold">{{ formattedWeight }}</p>
                </div>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-6 lg:gap-8">
            <div class="w-full lg:w-1/4 space-y-4 sm:space-y-6">
                <h2 class="text-2xl sm:text-3xl font-bold">Séances récente</h2>
                <div class="space-y-3 sm:space-y-4">
                    <div v-for="lastW in latestWorkouts" :key="lastW.id"
                         class="px-4 sm:px-6 py-3 sm:py-4 rounded-secondaryRounded bg-white shadow-evoShadow">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center text-evogray gap-2">
                            <h3 class="text-lg sm:text-xl lg:text-2xl font-bold">
                                {{ lastW.workout_template?.name ?? 'Template inconnu' }}
                            </h3>
                            <p class="text-xs sm:text-sm text-gray-500">
                                {{ new Date(lastW.created_at).toLocaleDateString() }}
                            </p>
                        </div>
                        <p class="text-sm sm:text-base">{{ lastW.session_exercises.length }} exercices</p>
                    </div>
                    <Link
                        :href="route('workout-templates.index')"
                        class="w-full h-16 sm:h-20 bg-evogradientleft text-white text-lg sm:text-xl lg:text-2xl font-bold rounded-secondaryRounded flex items-center justify-center"
                    >
                        Nouvelle séance ?
                    </Link>

                </div>
            </div>
            <div class="w-full lg:w-2/3"></div>
        </div>
    </section>
</template>

