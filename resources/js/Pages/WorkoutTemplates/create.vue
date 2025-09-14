<script setup>
import { useForm } from '@inertiajs/vue3'
import { defineProps } from 'vue'

const props = defineProps({
    exercises: Array
})

const form = useForm({
    name: '',
    exercises: [
    ]
})

function addExercise() {
    form.exercises.push({ 
        exercise_id: null, 
        order: form.exercises.length + 1, 
        notes: '',
        estimated_sets: 3,
        estimated_reps: 10,
        estimated_weight: null,
        estimated_rest_time: 90,
        estimated_rest_time_formatted: "01:30"
    })
}

function removeExercise(index) {
    form.exercises.splice(index, 1)
}

// Fonction pour convertir le format MM:SS en secondes
function parseRestTime(formatted) {
    const parts = formatted.split(':')
    if (parts.length === 2) {
        const minutes = parseInt(parts[0]) || 0
        const seconds = parseInt(parts[1]) || 0
        if (seconds >= 0 && seconds <= 59) {
            return (minutes * 60) + seconds
        }
    }
    return 90 // Valeur par défaut (1:30)
}

function submit() {
    // Convertir les temps de repos formatés en secondes avant l'envoi
    form.exercises.forEach(exercise => {
        if (exercise.estimated_rest_time_formatted) {
            exercise.estimated_rest_time = parseRestTime(exercise.estimated_rest_time_formatted)
        }
    })
    
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
            <div v-for="(exercise, index) in form.exercises" :key="index" class="mb-6 border p-4 rounded-lg bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Sélection de l'exercice -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Exercice</label>
                        <select v-model="exercise.exercise_id" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500">
                            <option disabled value="">Choisir un exercice</option>
                            <option v-for="ex in exercises" :key="ex.id" :value="ex.id">{{ ex.name }}</option>
                        </select>
                    </div>

                    <!-- Ordre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ordre</label>
                        <input v-model="exercise.order" type="number" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500" placeholder="Ordre" />
                    </div>
                </div>

                <!-- Séries estimées -->
                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="text-lg font-semibold text-blue-800 mb-3">📊 Paramètres de l'exercice</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de séries</label>
                            <input 
                                v-model="exercise.estimated_sets" 
                                type="number" 
                                min="1" 
                                max="10" 
                                class="w-full border-2 border-blue-300 rounded px-3 py-2 text-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                placeholder="Ex: 3"
                            />
                            <p v-if="form.errors[`exercises.${index}.estimated_sets`]" class="text-red-500 text-sm mt-1">
                                {{ form.errors[`exercises.${index}.estimated_sets`] }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Répétitions par série</label>
                            <input 
                                v-model="exercise.estimated_reps" 
                                type="number" 
                                min="1" 
                                max="50" 
                                class="w-full border-2 border-blue-300 rounded px-3 py-2 text-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                placeholder="Ex: 10"
                            />
                            <p v-if="form.errors[`exercises.${index}.estimated_reps`]" class="text-red-500 text-sm mt-1">
                                {{ form.errors[`exercises.${index}.estimated_reps`] }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Poids estimé (kg)</label>
                            <input 
                                v-model="exercise.estimated_weight" 
                                type="number" 
                                step="0.5" 
                                min="0" 
                                class="w-full border-2 border-blue-300 rounded px-3 py-2 text-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                placeholder="Ex: 20 (laisser vide pour auto)"
                            />
                            <p class="text-xs text-gray-500 mt-1">💡 Laissez vide pour une estimation automatique</p>
                            <p v-if="form.errors[`exercises.${index}.estimated_weight`]" class="text-red-500 text-sm mt-1">
                                {{ form.errors[`exercises.${index}.estimated_weight`] }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Temps de repos (MM:SS)</label>
                            <input 
                                v-model="exercise.estimated_rest_time_formatted" 
                                type="text"
                                pattern="[0-9]{1,2}:[0-5][0-9]"
                                class="w-full border-2 border-blue-300 rounded px-3 py-2 text-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                placeholder="01:30"
                            />
                            <p class="text-xs text-gray-500 mt-1">💡 Temps de repos entre les séries</p>
                            <p v-if="form.errors[`exercises.${index}.estimated_rest_time`]" class="text-red-500 text-sm mt-1">
                                {{ form.errors[`exercises.${index}.estimated_rest_time`] }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mt-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea v-model="exercise.notes" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500" placeholder="Notes facultatives" rows="2"></textarea>
                </div>

                <button type="button" @click="removeExercise(index)" class="mt-3 text-red-600 text-sm hover:text-red-800">Supprimer cet exercice</button>
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
