<script setup>
import LikeButton from "@/Components/LikeButton.vue";
import LoadingSpinner from "@/Components/LoadingSpinner.vue";
import SkeletonLoader from "@/Components/SkeletonLoader.vue";

defineOptions({layout: DefaultLayout})

import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import {Link, router, usePage, Head} from '@inertiajs/vue3'
import {ref, watch, computed, onMounted} from 'vue'
import StarRating from "@/Components/StarRating.vue";


const props = defineProps({
    muscleCategories: Array,
    muscleTargets: Array,
    exercises: {
        type:Array,
        required:true,
    }
})

const page = usePage()
const user = page.props.auth?.user

// États de chargement
const isLoading = ref(false)
const isInitialLoad = ref(true)

// Computed pour les exercices avec pagination virtuelle
const visibleExercises = computed(() => {
    return props.exercises.slice(0, 20) // Afficher seulement les 20 premiers
})

// Simulation du chargement initial
onMounted(() => {
    setTimeout(() => {
        isInitialLoad.value = false
    }, 1000)
})

function deleteExercise(id) {
    if (confirm('Voulez-vous vraiment supprimer cet exercice ?')) {
        router.delete(route('exercises.destroy', id))
    }
}

function getUniqueExercises(category) {
    const exercisesMap = {}

    category.muscle_targets.forEach(mt => {
        // Utiliser primaryExercises au lieu de exercises
        const exercises = mt.primary_exercises || mt.exercises || []
        exercises.forEach(ex => {
            const enriched = props.exercises.find(e => e.id === ex.id)
            if (enriched) {
                exercisesMap[ex.id] = enriched
            } else {
                exercisesMap[ex.id] = ex
            }
        })
    })

    return Object.values(exercisesMap)
}



const bgClasses = {
    evochest: 'bg-evochest',
    evolegs: 'bg-evolegs',
    evoback: 'bg-evoback',
    evoarm: 'bg-evoarm',
    evoshoulder: 'bg-evoshoulder',
    evocardio: 'bg-evocardio',
}

const showModal = ref(false)
const selectedExerciseForModal = ref(null)
const selectedCategoryColor = ref(null)

const activeTab = ref('explication')

// Simule des stats (true si tu as des stats, false sinon)
const statsExist = ref(false)

function openModal(exercise, category) {
    selectedExerciseForModal.value = exercise
    selectedCategoryColor.value = category.color
    activeTab.value = 'explication' // reset onglet à l'ouverture
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    selectedExerciseForModal.value = null
    selectedCategoryColor.value = null
}

watch(showModal, (newVal) => {
    if (newVal) {
        document.body.classList.add('overflow-hidden')
    } else {
        document.body.classList.remove('overflow-hidden')
    }
})

// Fonctions pour récupérer les muscles
function getPrimaryMuscle(exercise) {
    const muscleTargets = exercise.muscle_targets || exercise.muscleTargets
    if (!muscleTargets) return null
    const primaryMuscle = muscleTargets.find(m => m.pivot && m.pivot.is_primary)
    return primaryMuscle ? primaryMuscle.name : null
}

function getSecondaryMuscles(exercise) {
    const muscleTargets = exercise.muscle_targets || exercise.muscleTargets
    if (!muscleTargets) return []
    return muscleTargets
        .filter(m => m.pivot && !m.pivot.is_primary)
        .map(m => m.name)
}


</script>

<template>
    <Head title="Exercices"/>

    <div class="mx-auto p-4 sm:p-6">
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-center mb-6 sm:mb-8 text-evogray">Exercices</h1>

        <div v-if="user?.role === 'admin'" class="flex flex-col sm:flex-row gap-2 sm:gap-4 mb-4 sm:mb-6">
            <Link :href="route('exercises.create')" class="btn-primary">+ Nouvel exercice</Link>
            <Link :href="route('muscleTargets.index')" class="btn-primary">Liste des muscles</Link>
            <Link :href="route('muscleCategories.index')" class="btn-primary">Liste des catégories</Link>
        </div>

        <div v-for="category in muscleCategories" :key="category.id" class="mb-6 sm:mb-10">
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-semibold mb-3 sm:mb-4 text-evogray">{{ category.name }}</h2>

            <div v-if="category.muscle_targets && category.muscle_targets.length"
                 class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                <div
                    v-for="exercise in getUniqueExercises(category)"
                    :key="exercise.id"
                    class="cursor-pointer border rounded-mainRounded mb-3 w-full sm:w-evocardwidth h-evocardheight flex flex-col items-center justify-between"
                    :class="category.color ? bgClasses[category.color] : 'bg-gray-500'"
                >
                    <div class="py-3 flex flex-col items-center justify-around h-4/5"
                         @click="openModal(exercise, category)"
                    >
                        <h3 class="text-lg sm:text-xl lg:text-2xl font-bold text-evogray">{{ exercise.name }}</h3>
                        <img src="/img/test_img_exo.png" alt="" class="w-16 sm:w-20 h-auto">
                        <!--                    <p class="text-sm text-gray-600">{{ exercise.description || 'Aucune description' }}</p>-->
                        <div v-if="user?.role === 'admin'" class="mt-2 flex gap-2">
                            <Link :href="route('exercises.edit', exercise.id)" class="btn-edit">Modifier</Link>
                            <button @click.stop="deleteExercise(exercise.id)" class="btn-delete">Supprimer</button>
                        </div>
                    </div>
                    <div class="flex justify-between items-center bg-white w-full h-1/5 rounded-b-mainRounded px-4">
                        <LikeButton
                            :exercise-id="exercise.id"
                            :initial-liked="exercise.is_liked"
                            :initial-count="exercise.likes_count"
                        />

<!--                        <div class="flex items-center gap-1 font-bold text-2xl">-->
<!--                            <p>4,5</p>-->
<!--                            <svg xmlns="http://www.w3.org/2000/svg"-->
<!--                                 viewBox="0 0 24 24"-->
<!--                                 class="size-10"-->
<!--                                 fill="url(#evogradient)">-->
<!--                                <defs>-->
<!--                                    <linearGradient id="evogradient" x1="100%" y1="0%" x2="0%" y2="0%">-->
<!--                                        <stop offset="0%" stop-color="#3690DE"/>-->
<!--                                        <stop offset="100%" stop-color="#32D8A0"/>-->
<!--                                    </linearGradient>-->
<!--                                </defs>-->

<!--                                <path-->
<!--                                    d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/>-->
<!--                            </svg>-->
<!--                        </div>-->
                        <StarRating :exercise-id="exercise.id" />


                    </div>
                </div>

            </div>
            <div v-else>
                <p class="italic text-gray-500">Aucun exercice pour cette catégorie</p>
            </div>
        </div>
    </div>
    <!-- 🌟 MODALE EXERCICE AMÉLIORÉE -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 p-4 transition-opacity duration-300">
        <!-- Overlay avec animation -->
        <div 
            @click="closeModal"
            class="absolute inset-0 bg-black bg-opacity-60 transition-opacity duration-300"
        ></div>

        <!-- Bouton fermer amélioré -->
        <button
            @click="closeModal"
            class="fixed top-4 right-4 z-[9999] bg-white hover:bg-gray-100 text-gray-600 hover:text-gray-800 rounded-full w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center shadow-lg transition-all duration-200 hover:scale-110"
        >
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <!-- Contenu de la modal -->
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden transform transition-all duration-300 scale-100">
            <!-- Header avec image et informations principales -->
            <div
                class="relative p-6 sm:p-8 text-gray-800"
                :class="selectedCategoryColor ? bgClasses[selectedCategoryColor] : 'bg-gradient-to-br from-gray-500 to-gray-700'"
            >
                <!-- Image et nom de l'exercice -->
                <div class="flex items-center space-x-4 sm:space-x-6">
                    <div class="flex-shrink-0">
                        <img src="/img/test_img_exo.png" alt="" class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl shadow-lg" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800 mb-2">
                            {{ selectedExerciseForModal.name }}
                        </h2>
                        
                    </div>
                </div>

                <!-- Note et likes -->
                <div class="flex items-center justify-between mt-4">
                    <div class="flex items-center space-x-4">
                        <!-- Note -->
                        <div class="flex items-center space-x-1 bg-white bg-opacity-20 rounded-lg px-3 py-1">
                            <svg class="w-4 h-4 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span class="text-sm font-semibold text-gray-800">4.5</span>
                        </div>
                        
                        <!-- Likes -->
                        <div class="flex items-center space-x-1 bg-white bg-opacity-20 rounded-lg px-3 py-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                    </svg>
                            <span class="text-sm font-semibold text-gray-800">{{ selectedExerciseForModal.likes_count || 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenu principal avec onglets -->
            <div class="bg-white">
                <!-- Onglets améliorés -->
                <div class="flex border-b border-gray-200">
                    <button
                        @click="activeTab = 'explication'"
                        :class="[
                            'flex-1 px-4 py-3 text-sm font-medium transition-colors duration-200',
                            activeTab === 'explication' 
                                ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50' 
                                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'
                        ]"
                    >
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Explication
                    </button>
                    <button
                        @click="activeTab = 'statistique'"
                        :class="[
                            'flex-1 px-4 py-3 text-sm font-medium transition-colors duration-200',
                            activeTab === 'statistique' 
                                ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50' 
                                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'
                        ]"
                    >
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Statistiques
                    </button>
                    <button
                        @click="activeTab = 'rank'"
                        :class="[
                            'flex-1 px-4 py-3 text-sm font-medium transition-colors duration-200',
                            activeTab === 'rank' 
                                ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50' 
                                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'
                        ]"
                    >
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                        Classement
                    </button>
                </div>

                <!-- Contenu des onglets -->
                <div class="p-6">
                    <!-- Onglet Explication -->
                    <div v-if="activeTab === 'explication'" class="space-y-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Description</h3>
                            <p class="text-gray-600 leading-relaxed">
                                {{ selectedExerciseForModal.description || 'Aucune description disponible pour cet exercice.' }}
                            </p>
                        </div>
                        
                        <!-- Muscles ciblés -->
                        <div class="bg-blue-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-blue-800 mb-3">Muscles ciblés</h3>
                            <div class="space-y-3">
                                <!-- Muscle principal -->
                                <div v-if="getPrimaryMuscle(selectedExerciseForModal)" class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-blue-900">{{ getPrimaryMuscle(selectedExerciseForModal) }}</div>
                                        <div class="text-sm text-blue-700">Muscle principal</div>
                                    </div>
                                </div>
                                
                                <!-- Muscles secondaires -->
                                <div v-if="getSecondaryMuscles(selectedExerciseForModal).length > 0" class="space-y-2">
                                    <div class="text-sm font-medium text-blue-800 mb-2">Muscles secondaires :</div>
                                    <div class="flex flex-wrap gap-2">
                                        <span 
                                            v-for="muscle in getSecondaryMuscles(selectedExerciseForModal)"
                                            :key="muscle"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800"
                                        >
                                            {{ muscle }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Message si aucun muscle -->
                                <div v-if="!getPrimaryMuscle(selectedExerciseForModal) && getSecondaryMuscles(selectedExerciseForModal).length === 0" class="text-center py-4">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    <p class="text-gray-500 text-sm">Aucun muscle ciblé défini</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Statistiques -->
                    <div v-if="activeTab === 'statistique'" class="space-y-4">
                        <!-- Statistiques utilisateur -->
                        <div v-if="selectedExerciseForModal.user_stats && selectedExerciseForModal.user_stats.session_count > 0" class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-800">Vos statistiques</h3>
                            
                            <!-- Statistiques principales -->
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Nombre de séances -->
                                <div class="bg-green-50 rounded-lg p-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-2xl font-bold text-green-600">
                                                {{ selectedExerciseForModal.user_stats.session_count }}
                                            </div>
                                            <div class="text-sm text-green-700">Séances</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total séries -->
                                <div class="bg-blue-50 rounded-lg p-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-2xl font-bold text-blue-600">
                                                {{ selectedExerciseForModal.user_stats.total_sets }}
                                            </div>
                                            <div class="text-sm text-blue-700">Séries</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Records -->
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Poids max -->
                                <div class="bg-red-50 rounded-lg p-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-red-600 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-2xl font-bold text-red-600">
                                                {{ selectedExerciseForModal.user_stats.max_weight }}kg
                                            </div>
                                            <div class="text-sm text-red-700">Record poids</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Répétitions max -->
                                <div class="bg-purple-50 rounded-lg p-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 0h10m-10 0a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-2xl font-bold text-purple-600">
                                                {{ selectedExerciseForModal.user_stats.max_reps }}
                                            </div>
                                            <div class="text-sm text-purple-700">Record reps</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Moyennes -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="text-md font-semibold text-gray-800 mb-3">Moyennes</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="text-center">
                                        <div class="text-xl font-bold text-gray-800">{{ selectedExerciseForModal.user_stats.avg_weight }}kg</div>
                                        <div class="text-sm text-gray-600">Poids moyen</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-xl font-bold text-gray-800">{{ selectedExerciseForModal.user_stats.avg_reps }}</div>
                                        <div class="text-sm text-gray-600">Répétitions moyennes</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dernière séance -->
                            <div v-if="selectedExerciseForModal.user_stats.last_session" class="bg-yellow-50 rounded-lg p-4">
                                <h4 class="text-md font-semibold text-gray-800 mb-2">Dernière séance</h4>
                                <p class="text-sm text-gray-600">
                                    {{ new Date(selectedExerciseForModal.user_stats.last_session).toLocaleDateString('fr-FR', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric'
                                    }) }}
                                </p>
                            </div>
                        </div>

                        <!-- Message si pas encore utilisé -->
                        <div v-else class="text-center py-8">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <p class="text-gray-500 text-lg font-medium mb-2">Pas encore utilisé</p>
                            <p class="text-gray-400 text-sm">Commencez à utiliser cet exercice pour voir vos statistiques !</p>
                        </div>
                    </div>

                    <!-- Onglet Classement -->
                    <div v-if="activeTab === 'rank'" class="text-center py-12">
                        <svg class="w-20 h-20 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">Classement</h3>
                        <p class="text-gray-400">Fonctionnalité à venir prochainement</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>


<style scoped>
.btn-primary {
    @apply bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition;
}

.btn-edit {
    @apply text-white bg-yellow-500 hover:bg-yellow-600 px-3 py-1 rounded text-sm;
}

.btn-delete {
    @apply text-white bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm;
}
</style>
