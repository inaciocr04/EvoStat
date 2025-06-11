<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
defineOptions({ layout: DefaultLayout })

import { ref, onMounted } from 'vue'
import { router, useForm, Link } from '@inertiajs/vue3'
import Draggable from 'vuedraggable'
import axios from 'axios'

const props = defineProps({
    exercises: Array,
    workoutTemplates: Array, // passés depuis le backend Inertia
})

const showModal = ref(false)
const templates = ref([])

const form = useForm({
    name: '',
    exercises: [],
})

function addExercise() {
    form.exercises.push({
        id: Date.now() + Math.random(),
        exercise_id: null,
        notes: '',
        order: form.exercises.length + 1,
    })
}

function removeExercise(index) {
    form.exercises.splice(index, 1)
}

function updateOrders() {
    form.exercises.forEach((exercise, index) => {
        exercise.order = index + 1
    })
}

function deleteTemplate(id) {
    if (confirm('Voulez-vous vraiment supprimer ce template ?')) {
        router.delete(route('workout-templates.destroy', id))
    }
}

function submit() {
    form.post(route('workout-templates.store'), {
        onSuccess: () => {
            form.reset()
            showModal.value = false
        },
        onError: () => {
            console.log('Erreurs', form.errors)
        },
    })
}

async function launchSession(templateId) {
    try {
        const res = await axios.post('/sessions/create-from-template', { template_id: templateId })
        const sessionId = res.data.session_id
        router.visit(`/sessions/${sessionId}`)
    } catch (error) {
        console.error('Erreur lancement session', error)
        alert('Impossible de lancer la séance.')
    }
}


onMounted(() => {
    templates.value = props.workoutTemplates
})
</script>

<template>
    <div class="max-w-4xl mx-auto p-6">
        <!-- Bouton création -->
        <button @click="showModal = true" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 mb-6">
            + Créer un template
        </button>

        <!-- Liste -->
        <div v-if="templates.length">
            <h1 class="text-2xl font-bold mb-4">Mes Templates de Séances</h1>

            <div v-for="template in templates" :key="template.id" class="mb-4 border p-4 rounded shadow">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-semibold">{{ template.name }}</h2>
                    <div class="space-x-2">
                        <button @click="launchSession(template.id)"
                                class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm">
                            Lancer cette séance
                        </button>

                        <Link :href="route('workout-templates.edit', template.id)"
                              class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 text-sm">
                            Modifier
                        </Link>

                        <button @click="deleteTemplate(template.id)"
                                class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-sm">
                            Supprimer
                        </button>
                    </div>
                </div>

                <ul class="list-disc list-inside mt-2">
                    <li v-for="ex in template.workout_template_exercises" :key="ex.id">
                        {{ ex.order }}. {{ ex.exercise.name }} <span class="italic text-gray-600">- {{ ex.notes }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div v-else>
            <p>Aucun template trouvé.</p>
        </div>

        <!-- MODAL -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                <div class="bg-white rounded shadow-lg max-w-xl w-full p-6 relative z-50">
                    <button @click="showModal = false"
                            class="absolute top-2 right-2 text-gray-500 hover:text-black text-xl">✕</button>

                    <h2 class="text-xl font-bold mb-4">Créer un Template</h2>

                    <form @submit.prevent="submit">
                        <!-- Nom -->
                        <div class="mb-4">
                            <label class="block mb-1 font-semibold">Nom du template</label>
                            <input v-model="form.name" class="border px-2 py-1 rounded w-full" />
                            <p v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</p>
                        </div>

                        <!-- Exercices -->
                        <h3 class="font-semibold mb-2">Exercices</h3>

                        <Draggable v-model="form.exercises" item-key="id" class="space-y-2" ghost-class="bg-gray-100"
                                   handle=".drag-handle" @update="updateOrders">
                            <template #item="{ element, index }">
                                <div class="grid grid-cols-12 gap-2 items-center border p-2 rounded bg-white shadow-sm">
                                    <!-- Drag -->
                                    <div class="drag-handle cursor-move col-span-1 flex justify-center items-center text-gray-400">
                                        <span class="text-xl">≡</span>
                                    </div>

                                    <!-- Choix exercice -->
                                    <div class="col-span-4">
                                        <select v-model="element.exercise_id" class="w-full border rounded px-2 py-1">
                                            <option disabled value="">Choisir un exercice</option>
                                            <option v-for="ex in exercises" :key="ex.id" :value="ex.id">{{ ex.name }}</option>
                                        </select>
                                    </div>

                                    <!-- Notes -->
                                    <div class="col-span-5">
                                        <input v-model="element.notes" placeholder="Notes" class="w-full border rounded px-2 py-1" />
                                    </div>

                                    <!-- Supprimer -->
                                    <div class="col-span-2 text-right">
                                        <button @click="removeExercise(index)" type="button"
                                                class="text-red-600 hover:text-red-800">Supprimer</button>
                                    </div>
                                </div>
                            </template>
                        </Draggable>

                        <button type="button" @click="addExercise" class="bg-blue-500 text-white px-2 py-1 rounded my-4">
                            Ajouter un exercice
                        </button>

                        <div class="text-right">
                            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Créer</button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </div>
</template>
