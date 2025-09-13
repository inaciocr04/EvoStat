<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue'

defineOptions({layout: DefaultLayout})

import {ref, onMounted, onBeforeUnmount, computed} from 'vue'
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

const activeTab = ref('completed')

const tabs = [
    {label: '✔️ Terminées', value: 'completed', color: 'text-green-700'},
    {label: '⏳ En cours', value: 'in_progress', color: 'text-yellow-600'},
    {label: '📝 Brouillons', value: 'draft', color: 'text-gray-700'}
]

const filteredSessions = computed(() => {
    if (activeTab.value === 'completed') return props.workoutCompleted
    if (activeTab.value === 'in_progress') return props.workoutInProgress
    if (activeTab.value === 'draft') return props.workoutDraft
    return []
})

const toggleSession = (id) => {
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
    draft: {x: 0, scrollLeft: 0},
    completed: {x: 0, scrollLeft: 0},
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
    getRef(section).value.scrollBy({left: -200, behavior: 'smooth'})
}

function scrollRight(section) {
    getRef(section).value.scrollBy({left: 200, behavior: 'smooth'})
}

function getRef(section) {
    if (section === 'draft') return scrollContainerDraft
    if (section === 'completed') return scrollContainerCompleted
    if (section === 'in_progress') return scrollContainerInProgress
    return null
}

const openMenuId = ref(null)

function toggleMenu(id) {
    openMenuId.value = openMenuId.value === id ? null : id
}

// Fermer si clic ailleurs
onMounted(() => {
    templates.value = props.workoutTemplates

    document.addEventListener('click', (e) => {
        const clickedInsideMenu = e.target.closest('.template-menu-wrapper')
        if (!clickedInsideMenu) {
            openMenuId.value = null
        }
    })
})
onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside)
})

function handleClickOutside(e) {
    const clickedInsideMenu = e.target.closest('.template-menu-wrapper')
    if (!clickedInsideMenu) {
        openMenuId.value = null
    }
}
</script>

<template>
    <Head title="Séances"/>
    <h1 class="text-center text-6xl font-bold my-20">Séances</h1>
    <div class=" mx-auto p-6">
        <!-- Bouton création -->
        <button @click="showModal = true"
                class="bg-evogradientleft text-white w-full h-40 px-4 py-2 rounded-mainRounded hover:bg-green-700 mb-6 text-4xl font-bold">
            Commence une séance !
        </button>

        <!-- Liste -->
        <div v-if="templates.length">
            <h1 class="text-2xl font-bold mb-4">Tes Templates</h1>

            <!-- Conteneur scrollable horizontal avec drag -->
            <div
                ref="scrollContainer"
                class="flex space-x-6 overflow-x-auto cursor-grab active:cursor-grabbing scrollbar-hide select-none py-8 px-6"
                @mousedown="startDrag"
                @mousemove="onDrag"
                @mouseup="stopDrag"
                @mouseleave="stopDrag"
                style="scroll-behavior: smooth;"
            >
                <div
                    v-for="template in templates"
                    :key="template.id"
                    class="min-w-[20rem] min-h-56 py-2 px-6 rounded-mainRounded shadow-evoShadow relative bg-white flex flex-col justify-between"
                >
                    <!-- Titre + menu -->
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold">{{ template.name }}</h2>

                        <!-- Bouton trois points -->
                        <div class="relative h-fit" @click.stop>
                            <button @click="toggleMenu(template.id)">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-9 text-gray-600 hover:text-black"
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M6 10a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm6 0a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 2a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" />
                                </svg>
                            </button>

                            <!-- Menu déroulant -->
                            <div
                                v-if="openMenuId === template.id"
                                class="absolute right-0 mt-1 w-32 bg-white border rounded shadow z-50"
                            >
                                <Link :href="route('workout-templates.edit', template.id)"
                                      class="block px-4 py-2 hover:bg-gray-100 text-sm">
                                    Modifier
                                </Link>
                                <button @click="deleteTemplate(template.id)"
                                        class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-sm text-red-600">
                                    Supprimer
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des exos -->
                    <ul class="list-disc list-inside mt-2 ml-4">
                        <li v-for="ex in template.workout_template_exercises" :key="ex.id">
                            {{ ex.order }}. {{ ex.exercise.name }}
                            <span class="italic text-gray-600">- {{ ex.notes }}</span>
                        </li>
                    </ul>

                    <!-- Bouton Lancer -->
                    <div class="space-x-2 mt-2 text-center">
                        <button @click="launchSession(template.id)"
                                class="bg-evogradienttop text-white px-3 py-1 rounded-thirdRounded hover:bg-blue-600 text-2xl font-bold w-28">
                            GO
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-else>
            <p>Aucun template trouvé.</p>
        </div>

        <!-- HISTORIQUE DES SÉANCES -->
        <div class="mt-10">
            <h1 class="text-2xl font-bold mb-4">Historique des Séances</h1>

            <!-- Menu d'onglets -->
            <div class="flex justify-center space-x-8 mb-8">
                <button
                    v-for="tab in tabs"
                    :key="tab.value"
                    @click="activeTab = tab.value"
                    :class="[
          'px-6 py-3 rounded-lg font-semibold transition',
          activeTab === tab.value ? 'bg-blue-600 text-white shadow-lg' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
        ]"
                >
                    {{ tab.label }}
                </button>
            </div>


            <!-- Contenu dynamique selon l’onglet -->
            <div
                ref="scrollContainer"
                class="flex space-x-6 overflow-x-auto cursor-grab active:cursor-grabbing scrollbar-hide select-none"
                @mousedown="startDrag"
                @mousemove="onDrag"
                @mouseup="stopDrag"
                @mouseleave="stopDrag"
                style="scroll-behavior: smooth;"
            >
                <div
                    v-for="session in filteredSessions"
                    :key="session.id"
                    class="min-w-[18rem] w-72 border rounded-xl p-5 shadow-md bg-white hover:scale-[1.03] hover:shadow-lg transition-transform"
                >
                    <h3 class="text-lg font-bold truncate mb-1">
                        {{ session.workout_template?.name ?? 'Sans nom' }}
                    </h3>
                    <p class="text-xs text-gray-500 mb-1">
                        {{ new Date(session.created_at).toLocaleDateString('fr-FR', {dateStyle: 'medium'}) }}
                    </p>
                    <p class="text-sm text-gray-700 font-medium mb-1">
                        {{ session.session_exercises.length }} exos —
                        {{ session.session_exercises.reduce((sum, ex) => sum + ex.sets.length, 0) }} séries
                    </p>
                    <p
                        class="text-xs italic"
                        :class="{
            'text-green-500': activeTab === 'completed',
            'text-yellow-500': activeTab === 'in_progress',
            'text-gray-500': activeTab === 'draft'
          }"
                    >
                        {{
                            activeTab === 'completed' ? 'Terminée' : activeTab === 'in_progress' ? 'En cours' : 'Brouillon'
                        }}
                    </p>
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

