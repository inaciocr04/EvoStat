<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue'

defineOptions({layout: DefaultLayout})

import {ref, onMounted, onBeforeUnmount, computed, nextTick} from 'vue'
import {router, useForm, Link, Head} from '@inertiajs/vue3'
import Draggable from 'vuedraggable'
import axios from 'axios'

const props = defineProps({
    exercises: Array,
    workoutTemplates: Array,
})

const showModal = ref(false)
const templates = ref([])

// Variables pour le carrousel des templates
const templatesScrollContainer = ref(null)
const canScrollLeft = ref(false)
const canScrollRight = ref(false)
const currentTemplateIndex = ref(0)
const currentTemplatePage = ref(0)
const templatesPerPage = 3 // Nombre de templates visibles par page

// Computed properties pour la pagination
const templatePages = computed(() => {
    const totalPages = Math.ceil(templates.value.length / templatesPerPage)
    return Array.from({ length: totalPages }, (_, i) => i)
})


// Fonction pour formater le temps de repos en minutes
const formatRestTimeMinutes = (seconds) => {
    if (!seconds) return '0min'
    const minutes = Math.floor(seconds / 60)
    const remainingSeconds = seconds % 60
    
    if (minutes === 0) {
        return `${remainingSeconds}s repos`
    } else if (remainingSeconds === 0) {
        return `${minutes}min repos`
    } else {
        return `${minutes}min ${remainingSeconds}s repos`
    }
}


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
        estimated_sets: 3,
        estimated_reps: 10,
        estimated_weight: null,
        estimated_rest_time: 90, // Default value in seconds
        estimated_rest_time_formatted: "01:30"
    })
}

function removeExercise(index) {
    form.exercises.splice(index, 1)
}

// Fonction pour convertir MM:SS en secondes
function parseRestTime(formatted) {
    const parts = formatted.split(':')
    if (parts.length === 2) {
        const minutes = parseInt(parts[0]) || 0
        const seconds = parseInt(parts[1]) || 0
        if (seconds >= 0 && seconds <= 59) {
            return (minutes * 60) + seconds
        }
    }
    return 90 // Default value (1:30)
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

function submit(shouldStartSession = false) {
    // Convertir le format MM:SS en secondes avant l'envoi
    form.exercises.forEach(exercise => {
        if (exercise.estimated_rest_time_formatted) {
            exercise.estimated_rest_time = parseRestTime(exercise.estimated_rest_time_formatted)
        }
    })
    
    form.post(route('workout-templates.store'), {
        onSuccess: (response) => {
            form.reset()
            showModal.value = false
            
            // Si on doit démarrer la séance, récupérer l'ID du template créé et lancer la séance
            if (shouldStartSession && response.props.templateId) {
                launchSession(response.props.templateId)
            }
        },
        onError: () => {
            console.log('Erreurs', form.errors)
        },
    })
}

async function createAndStartSession() {
    // Convertir le format MM:SS en secondes avant l'envoi
    form.exercises.forEach(exercise => {
        if (exercise.estimated_rest_time_formatted) {
            exercise.estimated_rest_time = parseRestTime(exercise.estimated_rest_time_formatted)
        }
    })
    
    // Utiliser la nouvelle route qui crée le template et démarre la séance directement
    form.post(route('workout-templates.store-and-start'), {
        onSuccess: () => {
            // La redirection se fait automatiquement côté serveur
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


// Fonctions pour le carrousel des templates
function scrollTemplatesLeft() {
    if (templatesScrollContainer.value) {
        templatesScrollContainer.value.scrollBy({left: -300, behavior: 'smooth'})
    }
}

function scrollTemplatesRight() {
    if (templatesScrollContainer.value) {
        templatesScrollContainer.value.scrollBy({left: 300, behavior: 'smooth'})
    }
}

function updateScrollButtons() {
    if (templatesScrollContainer.value) {
        const {scrollLeft, scrollWidth, clientWidth} = templatesScrollContainer.value
        canScrollLeft.value = scrollLeft > 0
        canScrollRight.value = scrollLeft < scrollWidth - clientWidth - 10
        
        // Calculer l'index actuel basé sur la position de scroll
        const templateWidth = 320 // min-w-[20rem] + gap
        currentTemplateIndex.value = Math.round(scrollLeft / templateWidth)
        
        // Calculer la page actuelle
        currentTemplatePage.value = Math.floor(currentTemplateIndex.value / templatesPerPage)
    }
}

function goToTemplate(index) {
    if (templatesScrollContainer.value && index >= 0 && index < templates.length) {
        const templateWidth = 320 // min-w-[20rem] + gap
        templatesScrollContainer.value.scrollTo({
            left: index * templateWidth,
            behavior: 'smooth'
        })
        currentTemplateIndex.value = index
    }
}

function goToTemplatePage(pageIndex) {
    if (templatesScrollContainer.value && pageIndex >= 0 && pageIndex < templatePages.value.length) {
        const templateWidth = 320 // min-w-[20rem] + gap
        const scrollPosition = pageIndex * templatesPerPage * templateWidth
        templatesScrollContainer.value.scrollTo({
            left: scrollPosition,
            behavior: 'smooth'
        })
        currentTemplatePage.value = pageIndex
    }
}


const openMenuId = ref(null)

function toggleMenu(id) {
    openMenuId.value = openMenuId.value === id ? null : id
}





// Fermer si clic ailleurs
onMounted(async () => {
    templates.value = props.workoutTemplates

    // Initialiser les boutons du carrousel
    setTimeout(() => {
        updateScrollButtons()
    }, 100)

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
    <h1 class="text-center text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold my-8 sm:my-12 md:my-16 lg:my-20">Séances</h1>
    <div class="mx-auto p-4 sm:p-6">
        <!-- Bouton création -->
        <button @click="showModal = true"
                class="bg-evogradientleft text-white w-full h-24 sm:h-32 md:h-40 px-4 py-2 rounded-mainRounded hover:bg-green-700 mb-4 sm:mb-6 text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold">
            Commence une séance !
        </button>

        <!-- SECTION TEMPLATES -->
        <div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-2xl p-4 sm:p-6 mb-6 sm:mb-8">
        <div v-if="templates.length">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 sm:mb-6 gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Tes Templates</h1>
                        <p class="text-gray-600 mt-1 text-sm sm:text-base">Créez et gérez vos séances d'entraînement</p>
                    </div>
                    <div class="flex space-x-2">
                        <button @click="scrollTemplatesLeft" 
                                class="bg-white hover:bg-gray-50 rounded-full p-2 sm:p-3 shadow-md transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="!canScrollLeft">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <button @click="scrollTemplatesRight" 
                                class="bg-white hover:bg-gray-50 rounded-full p-2 sm:p-3 shadow-md transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="!canScrollRight">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>

            <!-- Conteneur scrollable horizontal avec drag -->
            <div
                ref="templatesScrollContainer"
                class="flex space-x-4 sm:space-x-6 overflow-x-auto cursor-grab active:cursor-grabbing scrollbar-hide select-none py-4 sm:py-8 px-2 sm:px-6"
                @mousedown="startDrag"
                @mousemove="onDrag"
                @mouseup="stopDrag"
                @mouseleave="stopDrag"
                @scroll="updateScrollButtons"
                style="scroll-behavior: smooth;"
            >
                <div
                    v-for="template in templates"
                    :key="template.id"
                    class="min-w-[18rem] sm:min-w-[20rem] bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 flex justify-between flex-col px-4 sm:px-6 py-4 sm:py-6"
                >
                    <!-- Header -->
                    <div>
                        <div class="flex justify-between items-start">
                            <h2 class="text-lg sm:text-xl font-bold text-gray-800">{{ template.name }}</h2>
                            
                            <!-- Menu trois points -->
                            <div class="relative" @click.stop>
                                <button @click="toggleMenu(template.id)" class="text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M6 10a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm6 0a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 2a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" />
                                </svg>
                            </button>

                            <!-- Menu déroulant -->
                                <div v-if="openMenuId === template.id" class="absolute right-0 mt-2 w-32 bg-white border rounded shadow-lg z-50">
                                    <Link :href="route('workout-templates.edit', template.id)" class="block px-4 py-2 hover:bg-gray-100 text-sm">
                                        ✏️ Modifier
                                </Link>
                                    <button @click="deleteTemplate(template.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-sm text-red-600">
                                        🗑️ Supprimer
                                </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des exercices -->
                    <div class="p-4 sm:p-6">
                        <div class="space-y-2">
                            <div v-for="ex in template.workout_template_exercises.slice(0, 5)" :key="ex.id" 
                                 class="text-gray-700 text-xs sm:text-sm">
                                {{ ex.estimated_sets }} x {{ ex.estimated_reps }} {{ ex.exercise.name }}
                            </div>
                            <div v-if="template.workout_template_exercises.length > 5" class="text-gray-500 text-xs sm:text-sm">
                                +{{ template.workout_template_exercises.length - 5 }} autres exercices
                            </div>
                        </div>
                    </div>

                    <!-- Bouton Lancer -->
                    <div class="space-x-2 text-center">
                        <button @click="launchSession(template.id)"
                                class="bg-evogradienttop text-white px-3 py-1 rounded-thirdRounded hover:bg-blue-600 text-lg sm:text-2xl font-bold w-20 sm:w-28">
                            GO
                        </button>
                    </div>
                </div>
            </div>

                <!-- Indicateurs de pagination interactifs -->
                <div v-if="templates.length > 3" class="flex flex-col items-center mt-6">
                    <div class="flex space-x-2 mb-2">
                        <button v-for="(page, index) in templatePages" :key="`page-${index}`"
                                @click="goToTemplatePage(index)"
                                class="w-3 h-3 rounded-full transition-all duration-200 hover:scale-110"
                                :class="index === currentTemplatePage ? 'bg-blue-500 shadow-lg' : 'bg-white shadow-sm hover:bg-gray-200'">
                        </button>
                    </div>
                    <p class="text-sm text-gray-600">{{ templatePages.length }} pages • Cliquez sur les points ou utilisez les flèches</p>
            </div>
        </div>

            <div v-else class="text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">📋</div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">Aucun template trouvé</h3>
                <p class="text-gray-500">Créez votre premier template pour commencer !</p>
            </div>
        </div>


        <!-- MODAL -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded shadow-lg max-w-xl w-full p-4 sm:p-6 relative z-50 max-h-[90vh] overflow-y-auto">
                    <button @click="showModal = false"
                            class="absolute top-2 right-2 text-gray-500 hover:text-black text-xl">✕
                    </button>

                    <h2 class="text-lg sm:text-xl font-bold mb-4">Créer un Template</h2>

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
                                <div class="border p-3 rounded bg-white shadow-sm mb-3">
                                    <!-- Ligne principale -->
                                    <div class="grid grid-cols-12 gap-2 items-center mb-2">
                                    <!-- Drag -->
                                        <div class="drag-handle cursor-move col-span-1 flex justify-center items-center text-gray-400">
                                        <span class="text-xl">≡</span>
                                    </div>

                                    <!-- Choix exercice -->
                                        <div class="col-span-6">
                                        <select v-model="element.exercise_id" class="w-full border rounded px-2 py-1">
                                            <option disabled value="">Choisir un exercice</option>
                                                <option v-for="ex in exercises" :key="ex.id" :value="ex.id">{{ ex.name }}</option>
                                        </select>
                                        </div>

                                        <!-- Supprimer -->
                                        <div class="col-span-5 text-right">
                                            <button @click="removeExercise(index)" type="button" class="text-red-600 hover:text-red-800 text-sm">
                                                Supprimer
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Paramètres estimés -->
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-2">
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">Séries</label>
                                            <input v-model="element.estimated_sets" type="number" min="1" max="10" 
                                                   class="w-full border rounded px-2 py-1 text-sm" placeholder="3"/>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">Répétitions</label>
                                            <input v-model="element.estimated_reps" type="number" min="1" max="50" 
                                                   class="w-full border rounded px-2 py-1 text-sm" placeholder="10"/>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">Poids (kg)</label>
                                            <input v-model="element.estimated_weight" type="number" step="0.5" min="0" 
                                                   class="w-full border rounded px-2 py-1 text-sm" placeholder="Auto"/>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">Repos (MM:SS)</label>
                                            <input v-model="element.estimated_rest_time_formatted" type="text" 
                                                   pattern="[0-9]{1,2}:[0-5][0-9]"
                                                   class="w-full border rounded px-2 py-1 text-sm" placeholder="01:30"/>
                                        </div>
                                    </div>

                                    <!-- Notes -->
                                    <div>
                                        <input v-model="element.notes" placeholder="Notes facultatives"
                                               class="w-full border rounded px-2 py-1 text-sm"/>
                                    </div>
                                </div>
                            </template>
                        </Draggable>

                        <button type="button" @click="addExercise"
                                class="bg-blue-500 text-white px-2 py-1 rounded my-4">
                            Ajouter un exercice
                        </button>

                        <div class="flex justify-between gap-3">
                            <button type="button" @click="createAndStartSession" 
                                    class="bg-evogradientleft text-white px-4 py-2 rounded font-semibold flex items-center gap-2">
                                🚀 Créer et commencer
                            </button>
                            <button type="submit" 
                                    class="bg-green-600 text-white px-4 py-2 rounded font-semibold">
                                💾 Créer seulement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </div>
</template>

