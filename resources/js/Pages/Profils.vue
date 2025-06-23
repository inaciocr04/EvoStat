<script setup>
import {computed} from "vue";

defineOptions({layout: DefaultLayout})
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import {Head, usePage} from '@inertiajs/vue3';

const page = usePage()
// console.log('Props disponibles :', page.props)

const totalWeight = computed(() => page.props.countWeight)
const totalReps = computed(() => page.props.countReps)
const totalWorkouts = computed(() => page.props.countWorkouts)

const user = page.props.user

// console.log('Total soulevé:', totalWeight.value)

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
                    <p class="text-4xl font-bold">1{{ totalWeight }} Kg</p></div>
            </div>
        </div>

    </section>
</template>

