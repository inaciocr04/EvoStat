<script setup>
import {computed} from "vue";

defineOptions({layout: DefaultLayout})
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import {Head, usePage} from '@inertiajs/vue3';

const page = usePage()
console.log('Props disponibles :', page.props)

const totalWeight = computed(() => page.props.countWeight)
const totalReps = computed(() => page.props.countReps)
const totalWorkouts = computed(() => page.props.countWorkouts)

console.log('Total soulevé:', totalWeight.value)

</script>

<template class="mt-8">
    <Head title="Profils"/>

    <div class="w-full flex justify-center items-center">
        <div class="grid grid-cols-3 gap-6 mb-8 text-evogray">
            <div class="rounded-thirdRounded shadow-evoShadow py-4 px-16 flex flex-col justify-start items-center space-y-6"><p class="text-2xl">Séances</p><p class="text-4xl font-bold">{{ totalWorkouts }}</p></div>
            <div class="rounded-thirdRounded shadow-evoShadow py-4 px-16 flex flex-col justify-start items-center space-y-6"><p class="text-2xl">Répétitions</p><p class="text-4xl font-bold">{{ totalReps }}</p></div>
            <div class="rounded-thirdRounded shadow-evoShadow py-4 px-16 flex flex-col justify-start items-center space-y-6"><p class="text-2xl">Total poids</p><p class="text-4xl font-bold">1{{ totalWeight }} Kg</p></div>
        </div>
    </div>


</template>

