<script setup>
import { useForm } from '@inertiajs/vue3'
import { defineProps, onMounted, ref } from 'vue'
import Draggable from 'vuedraggable'

const props = defineProps({
    exercises: {
        type: Array,
        default: () => []
    },
    workoutTemplate: {
        type: Object,
        default: () => ({ name: '', exercises: [] }),
    }
})

const form = useForm({
    name: '',
    exercises: []
})

function updateOrders() {
    form.exercises.forEach((ex, index) => {
        ex.order = index + 1
    })
}

onMounted(() => {
    form.name = props.workoutTemplate.name || ''
    form.exercises = props.workoutTemplate.exercises
        ? props.workoutTemplate.exercises.map(ex => ({
            id: ex.id || Date.now() + Math.random(),
            exercise_id: ex.exercise_id,
            order: ex.order,
            notes: ex.notes || ''
        }))
        : []
})

function addExercise() {
    form.exercises.push({
        id: null,
        exercise_id: null,
        order: form.exercises.length + 1,
        notes: ''
    })
}

function removeExercise(index) {
    form.exercises.splice(index, 1)
    updateOrders() // Mettre à jour les ordres après suppression
}

function submit() {
    form.put(route('workout-templates.update', props.workoutTemplate.id), {
        onSuccess: () => {
            form.clearErrors()
        }
    })
}
</script>

<template>
    <div class="max-w-2xl mx-auto p-6 bg-white rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Modifier le template de séance</h1>

        <form @submit.prevent="submit">
            <!-- Nom du template -->
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Nom</label>
                <input v-model="form.name" class="border px-3 py-2 w-full rounded" type="text" />
                <p v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</p>
            </div>

            <!-- Liste des exercices -->
            <h2 class="font-semibold mb-2">Exercices</h2>
            <Draggable
                v-model="form.exercises"
                item-key="id"
                ghost-class="bg-gray-100"
                handle=".drag-handle"
                @end="updateOrders"
                class="space-y-4"
            >
                <template #item="{ element: exercise, index }">
                    <div class="border p-3 rounded bg-gray-50 flex items-center space-x-3">
                        <!-- Poignée drag -->
                        <div class="drag-handle cursor-move text-gray-400 select-none px-2 text-2xl">≡</div>

                        <div class="flex-1">
                            <!-- Sélection exercice -->
                            <select v-model="exercise.exercise_id" class="w-full mb-2 border rounded px-2 py-1">
                                <option disabled value="">Choisir un exercice</option>
                                <option v-for="ex in exercises" :key="ex.id" :value="ex.id">{{ ex.name }}</option>
                            </select>
                            <p v-if="form.errors[`exercises.${index}.exercise_id`]" class="text-red-500 text-sm">
                                {{ form.errors[`exercises.${index}.exercise_id`] }}
                            </p>

                            <!-- Notes -->
                            <textarea
                                v-model="exercise.notes"
                                class="w-full border px-2 py-1 rounded mb-2"
                                placeholder="Notes facultatives"
                            ></textarea>
                            <p v-if="form.errors[`exercises.${index}.notes`]" class="text-red-500 text-sm">
                                {{ form.errors[`exercises.${index}.notes`] }}
                            </p>
                        </div>

                        <div class="w-20 flex flex-col items-center space-y-2">
                            <!-- Ordre indicatif (non modifiable) -->
                            <div
                                class="border px-2 py-1 rounded w-full text-center bg-gray-100 select-none cursor-default text-gray-600 font-semibold"
                            >
                                {{ exercise.order }}
                            </div>
                            <p v-if="form.errors[`exercises.${index}.order`]" class="text-red-500 text-xs text-center">
                                {{ form.errors[`exercises.${index}.order`] }}
                            </p>

                            <!-- Supprimer -->
                            <button
                                type="button"
                                @click="removeExercise(index)"
                                class="text-red-600 hover:text-red-800 text-sm mt-auto"
                            >
                                Supprimer
                            </button>
                        </div>
                    </div>
                </template>
            </Draggable>

            <!-- Ajouter un exercice -->
            <button type="button" @click="addExercise" class="bg-blue-500 text-white px-3 py-1 rounded">
                Ajouter un exercice
            </button>

            <!-- Soumission -->
            <div class="mt-6">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">
                    Mettre à jour le template
                </button>
            </div>
        </form>
    </div>
</template>
