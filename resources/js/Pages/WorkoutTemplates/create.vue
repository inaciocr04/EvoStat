<script setup>
import { useForm } from '@inertiajs/vue3'
import { defineProps } from 'vue'

const props = defineProps({
    exercises: Array
})

const form = useForm({
    name: '',
    exercises: [
        // { exercise_id: null, order: 1, notes: '' }
    ]
})

function addExercise() {
    form.exercises.push({ exercise_id: null, order: form.exercises.length + 1, notes: '' })
}

function removeExercise(index) {
    form.exercises.splice(index, 1)
}

function submit() {
    form.post(route('workout-templates.store'))
}
</script>

<template>
    <div class="max-w-2xl mx-auto p-6 bg-white rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Créer un template de séance</h1>

        <form @submit.prevent="submit">
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Nom</label>
                <input v-model="form.name" class="border px-3 py-2 w-full rounded" type="text" />
                <p v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</p>
            </div>

            <h2 class="font-semibold mb-2">Exercices</h2>
            <div v-for="(exercise, index) in form.exercises" :key="index" class="mb-4 border p-3 rounded">
                <select v-model="exercise.exercise_id" class="w-full mb-2 border rounded px-2 py-1">
                    <option disabled value="">Choisir un exercice</option>
                    <option v-for="ex in exercises" :key="ex.id" :value="ex.id">{{ ex.name }}</option>
                </select>

                <input v-model="exercise.order" type="number" class="w-full mb-2 border px-2 py-1 rounded" placeholder="Ordre" />

                <textarea v-model="exercise.notes" class="w-full border px-2 py-1 rounded" placeholder="Notes facultatives"></textarea>

                <button type="button" @click="removeExercise(index)" class="mt-2 text-red-600 text-sm">Supprimer</button>
            </div>

            <button type="button" @click="addExercise" class="bg-blue-500 text-white px-3 py-1 rounded">
                Ajouter un exercice
            </button>

            <div class="mt-6">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">
                    Enregistrer le template
                </button>
            </div>
        </form>
    </div>
</template>
