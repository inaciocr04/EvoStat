<script setup>
defineOptions({ layout: DefaultLayout })
import DefaultLayout from '@/Layouts/DefaultLayout.vue'

defineProps({
    workouts_completed: Array,
    exercises: Array,
    muscleTargets: Array
})


</script>

<template>

    <div v-for="workouts_completed in workouts" :key="workouts_completed.id">
        <h2>{{ workouts.workoutTemplate.name}}</h2>

    </div>

</template>

<style scoped>

</style>
