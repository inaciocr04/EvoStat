<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue'

defineOptions({layout: DefaultLayout})

import {ref, onMounted} from 'vue'
import {router, useForm, Link, Head} from '@inertiajs/vue3'
import Draggable from 'vuedraggable'
import axios from 'axios'

const props = defineProps({
    exercises: Array,
    workoutTemplates: Array,
    workoutCompleted: Array,
    workoutDraft: Array,
    workoutInProgress: Array,
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
        const res = await axios.post('/sessions/create-from-template', {template_id: templateId})
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

const openSession = ref(null) // ID de la session ouverte

function toggleSession(id) {
    openSession.value = openSession.value === id ? null : id
}

function countTotalSets(exercises) {
    return exercises.reduce((total, ex) => total + (ex.sets?.length || 0), 0)
}

const scrollContainerDraft = ref(null)
const scrollContainerCompleted = ref(null)
const scrollContainerInProgress = ref(null)

const isDragging = ref({
    draft: false,
    completed: false,
})
const dragStart = {
    draft: { x: 0, scrollLeft: 0 },
    completed: { x: 0, scrollLeft: 0 },
}

function startDrag(e, section) {
    isDragging.value[section] = true
    dragStart[section].x = e.pageX
    dragStart[section].scrollLeft = getRef(section).value.scrollLeft
}

function onDrag(e, section) {
    if (!isDragging.value[section]) return
    const dx = e.pageX - dragStart[section].x
    getRef(section).value.scrollLeft = dragStart[section].scrollLeft - dx
}

function stopDrag(section) {
    isDragging.value[section] = false
}

function scrollLeft(section) {
    getRef(section).value.scrollBy({ left: -200, behavior: 'smooth' })
}

function scrollRight(section) {
    getRef(section).value.scrollBy({ left: 200, behavior: 'smooth' })
}

function getRef(section) {
    if (section === 'draft') return scrollContainerDraft
    if (section === 'completed') return scrollContainerCompleted
    if (section === 'in_progress') return scrollContainerInProgress
    return null
}
</script>

<template>
    <Head title="Séances"/>

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
                        {{ ex.order }}. {{ ex.exercise.name }} <span class="italic text-gray-600">- {{
                            ex.notes
                        }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div v-else>
            <p>Aucun template trouvé.</p>
        </div>

        <!-- HISTORIQUE DES SÉANCES -->
        <div class="mt-10">
            <h1 class="text-2xl font-bold mb-4">Historique des Séances</h1>

            <!-- Séances terminées -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-green-700 mb-4">✔️ Séances terminées</h2>

                <div class="relative px-4 py-6">
                    <!-- Boutons de scroll -->
                    <button
                        @click="scrollLeft('completed')"
                        class="absolute -left-12 top-1/2 -translate-y-1/2 z-10 bg-white p-2 shadow rounded-full"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                        </svg>

                    </button>
                    <button
                        @click="scrollRight('completed')"
                        class="absolute -right-12 top-1/2 -translate-y-1/2 z-10 bg-white p-2 shadow rounded-full"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                        </svg>

                    </button>

                    <!-- Conteneur scrollable -->
                    <div
                        ref="scrollContainerCompleted"
                        v-if="props.workoutCompleted.length"
                        class="flex space-x-4 overflow-x-auto items-start scrollbar-hide px-10 cursor-grab active:cursor-grabbing"
                        @mousedown="startDrag($event, 'completed')"
                        @mousemove="onDrag($event, 'completed')"
                        @mouseup="stopDrag('completed')"
                        @mouseleave="stopDrag('completed')"
                    >
                        <div
                            v-for="session in props.workoutCompleted"
                            :key="session.id"
                            class="w-64 min-w-[16rem] border rounded-lg p-4 shadow-sm bg-white transition-all"
                        >
                            <!-- En-tête cliquable -->
                            <div @click="toggleSession(session.id)" class="cursor-pointer">
                                <h3 class="text-md font-bold text-evogray truncate">
                                    {{ session.workout_template.name ?? 'Sans nom' }}
                                </h3>
                                <p class="text-xs text-evogray">
                                    {{
                                        new Date(session.created_at).toLocaleDateString('fr-FR', {dateStyle: 'medium'})
                                    }}
                                </p>
                                <p class="text-sm text-gray-700 font-medium">
                                    {{ session.session_exercises.length }} exos —
                                    {{
                                        session.session_exercises.reduce((sum, ex) => sum + ex.sets.length, 0)
                                    }} séries
                                </p>
                            </div>

                            <!-- Contenu déroulant -->
                            <div
                                v-if="openSession === session.id"
                                class="mt-3 max-h-48 overflow-y-auto space-y-2 border-t pt-2 transition-all duration-200 ease-in-out"
                            >
                                <div v-for="ex in session.session_exercises" :key="ex.id" class="text-sm">
                                    <p class="font-semibold">{{ ex.order }}. {{ ex.exercise.name }}</p>
                                    <p v-if="ex.notes" class="italic text-gray-500 text-xs">{{ ex.notes }}</p>

                                    <div
                                        v-for="(set, index) in ex.sets"
                                        :key="index"
                                        class="text-xs bg-gray-50 rounded px-2 py-1 mt-1 border"
                                    >
                                        <p><strong>Série {{ index + 1 }}</strong> — {{ set.weight }} kg, {{ set.reps }}
                                            reps, {{ set.rest_time }}s</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-gray-500">Aucune séance terminée.</p>

                </div>

            </div>

            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-2">📝 Brouillons</h2>

                <div class="relative px-4 py-6">
                    <!-- Boutons de scroll -->
                    <button
                        @click="scrollLeft('draft')"
                        class="absolute -left-12 top-1/2 -translate-y-1/2 z-10 bg-white p-2 shadow rounded-full"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                        </svg>

                    </button>
                    <button
                        @click="scrollRight('draft')"
                        class="absolute -right-12 top-1/2 -translate-y-1/2 z-10 bg-white p-2 shadow rounded-full"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                        </svg>

                    </button>

                    <!-- Conteneur scrollable -->
                    <div
                        ref="scrollContainerDraft"
                        v-if="props.workoutDraft.length"
                        class="flex space-x-4 overflow-x-auto items-start scrollbar-hide px-10 cursor-grab active:cursor-grabbing"
                        @mousedown="startDrag($event, 'draft')"
                        @mousemove="onDrag($event, 'draft')"
                        @mouseup="stopDrag('draft')"
                        @mouseleave="stopDrag('draft')"
                    >
                        <div
                            v-for="session in props.workoutDraft"
                            :key="session.id"
                            class="w-64 min-w-[16rem] border rounded-lg p-4 shadow-sm bg-white transition-all"
                        >
                            <!-- En-tête cliquable -->
                            <div @click="toggleSession(session.id)" class="cursor-pointer">
                                <Link :href="`/sessions/${session.id}`" class="text-md font-bold text-evogray truncate">
                                    {{ session.workout_template.name ?? 'Sans nom' }}
                                </Link>
                                <p class="text-xs text-evogray">
                                    {{
                                        new Date(session.created_at).toLocaleDateString('fr-FR', {dateStyle: 'medium'})
                                    }}
                                </p>
                                <p class="text-sm text-gray-700 font-medium">
                                    {{ session.session_exercises.length }} exos —
                                    {{
                                        session.session_exercises.reduce((sum, ex) => sum + ex.sets.length, 0)
                                    }} séries
                                </p>
                            </div>

                            <!-- Contenu déroulant -->
                            <div
                                v-if="openSession === session.id"
                                class="mt-3 max-h-48 overflow-y-auto space-y-2 border-t pt-2 transition-all duration-200 ease-in-out"
                            >
                                <div v-for="ex in session.session_exercises" :key="ex.id" class="text-sm">
                                    <p class="font-semibold">{{ ex.order }}. {{ ex.exercise.name }}</p>
                                    <p v-if="ex.notes" class="italic text-gray-500 text-xs">{{ ex.notes }}</p>

                                    <div
                                        v-for="(set, index) in ex.sets"
                                        :key="index"
                                        class="text-xs bg-gray-50 rounded px-2 py-1 mt-1 border"
                                    >
                                        <p><strong>Série {{ index + 1 }}</strong> — {{ set.weight }} kg, {{ set.reps }}
                                            reps, {{ set.rest_time }}s</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-gray-500">Aucune séance terminée.</p>

                </div>

            </div>


            <!-- Et pour les séances en cours -->

            <div class="mb-6">
                <h2 class="text-xl font-semibold text-yellow-600 mb-2">⏳ En cours</h2>

                <div class="relative px-4 py-6">
                    <!-- Boutons de scroll -->
                    <button
                        @click="scrollLeft('in_progress')"
                        class="absolute -left-12 top-1/2 -translate-y-1/2 z-10 bg-white p-2 shadow rounded-full"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                        </svg>

                    </button>
                    <button
                        @click="scrollRight('in_progress')"
                        class="absolute -right-12 top-1/2 -translate-y-1/2 z-10 bg-white p-2 shadow rounded-full"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                        </svg>

                    </button>

                    <!-- Conteneur scrollable -->
                    <div
                        ref="scrollContainerInProgress"
                        v-if="props.workoutInProgress.length"
                        class="flex space-x-4 overflow-x-auto items-start scrollbar-hide px-10 cursor-grab active:cursor-grabbing"
                        @mousedown="startDrag($event, 'in_progress')"
                        @mousemove="onDrag($event, 'in_progress')"
                        @mouseup="stopDrag('in_progress')"
                        @mouseleave="stopDrag('in_progress')"
                    >
                        <div
                            v-for="session in props.workoutInProgress"
                            :key="session.id"
                            class="w-64 min-w-[16rem] border rounded-lg p-4 shadow-sm bg-white transition-all"
                        >
                            <!-- En-tête cliquable -->
                            <div @click="toggleSession(session.id)" class="cursor-pointer">
                                <Link :href="`/sessions/${session.id}`" class="text-md font-bold text-evogray truncate">
                                    {{ session.workout_template.name ?? 'Sans nom' }}
                                </Link>
                                <p class="text-xs text-evogray">
                                    {{
                                        new Date(session.created_at).toLocaleDateString('fr-FR', {dateStyle: 'medium'})
                                    }}
                                </p>
                                <p class="text-sm text-gray-700 font-medium">
                                    {{ session.session_exercises.length }} exos —
                                    {{
                                        session.session_exercises.reduce((sum, ex) => sum + ex.sets.length, 0)
                                    }} séries
                                </p>
                            </div>

                            <!-- Contenu déroulant -->
                            <div
                                v-if="openSession === session.id"
                                class="mt-3 max-h-48 overflow-y-auto space-y-2 border-t pt-2 transition-all duration-200 ease-in-out"
                            >
                                <div v-for="ex in session.session_exercises" :key="ex.id" class="text-sm">
                                    <p class="font-semibold">{{ ex.order }}. {{ ex.exercise.name }}</p>
                                    <p v-if="ex.notes" class="italic text-gray-500 text-xs">{{ ex.notes }}</p>

                                    <div
                                        v-for="(set, index) in ex.sets"
                                        :key="index"
                                        class="text-xs bg-gray-50 rounded px-2 py-1 mt-1 border"
                                    >
                                        <p><strong>Série {{ index + 1 }}</strong> — {{ set.weight }} kg, {{ set.reps }}
                                            reps, {{ set.rest_time }}s</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-gray-500">Aucune séance terminée.</p>

                </div>

            </div>
        </div>


        <!-- MODAL -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                <div class="bg-white rounded shadow-lg max-w-xl w-full p-6 relative z-50">
                    <button @click="showModal = false"
                            class="absolute top-2 right-2 text-gray-500 hover:text-black text-xl">✕
                    </button>

                    <h2 class="text-xl font-bold mb-4">Créer un Template</h2>

                    <form @submit.prevent="submit">
                        <!-- Nom -->
                        <div class="mb-4">
                            <label class="block mb-1 font-semibold">Nom du template</label>
                            <input v-model="form.name" class="border px-2 py-1 rounded w-full"/>
                            <p v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</p>
                        </div>

                        <!-- Exercices -->
                        <h3 class="font-semibold mb-2">Exercices</h3>

                        <Draggable v-model="form.exercises" item-key="id" class="space-y-2" ghost-class="bg-gray-100"
                                   handle=".drag-handle" @update="updateOrders">
                            <template #item="{ element, index }">
                                <div class="grid grid-cols-12 gap-2 items-center border p-2 rounded bg-white shadow-sm">
                                    <!-- Drag -->
                                    <div
                                        class="drag-handle cursor-move col-span-1 flex justify-center items-center text-gray-400">
                                        <span class="text-xl">≡</span>
                                    </div>

                                    <!-- Choix exercice -->
                                    <div class="col-span-4">
                                        <select v-model="element.exercise_id" class="w-full border rounded px-2 py-1">
                                            <option disabled value="">Choisir un exercice</option>
                                            <option v-for="ex in exercises" :key="ex.id" :value="ex.id">{{
                                                    ex.name
                                                }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Notes -->
                                    <div class="col-span-5">
                                        <input v-model="element.notes" placeholder="Notes"
                                               class="w-full border rounded px-2 py-1"/>
                                    </div>

                                    <!-- Supprimer -->
                                    <div class="col-span-2 text-right">
                                        <button @click="removeExercise(index)" type="button"
                                                class="text-red-600 hover:text-red-800">Supprimer
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </Draggable>

                        <button type="button" @click="addExercise"
                                class="bg-blue-500 text-white px-2 py-1 rounded my-4">
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
