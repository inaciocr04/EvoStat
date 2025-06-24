<script setup>
defineOptions({layout: DefaultLayout})

import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import {Link, router, usePage, Head} from '@inertiajs/vue3'
import {ref, watch} from 'vue'


const props = defineProps({
    muscleCategories: Array,
    muscleTargets: Array,
    exercises: Array
})

const page = usePage()
const user = page.props.auth?.user

function deleteExercise(id) {
    if (confirm('Voulez-vous vraiment supprimer cet exercice ?')) {
        router.delete(route('exercises.destroy', id))
    }
}

function getUniqueExercises(category) {
    const exercisesMap = {}
    category.muscle_targets.forEach(mt => {
        mt.exercises.forEach(ex => {
            exercisesMap[ex.id] = ex
        })
    })
    return Object.values(exercisesMap)
}

const bgClasses = {
    evochest: 'bg-evochest',
    evolegs: 'bg-evolegs',
    evoback: 'bg-evoback',
    evoarm: 'bg-evoarm',
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
    activeTab.value = 'explication' // reset onglet à l’ouverture
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


</script>

<template>
    <Head title="Exercices"/>

    <div class=" mx-auto p-6">
        <h1 class="text-5xl font-bold text-center mb-8 text-evogray">Exercices</h1>

        <div v-if="user?.role === 'admin'" class="flex gap-4 mb-6">
            <Link :href="route('exercises.create')" class="btn-primary">+ Nouvel exercice</Link>
            <Link :href="route('muscleTargets.index')" class="btn-primary">Liste des muscles</Link>
            <Link :href="route('muscleCategories.index')" class="btn-primary">Liste des catégories</Link>
        </div>

        <div v-for="category in muscleCategories" :key="category.id" class="mb-10">
            <h2 class="text-4xl font-semibold mb-4 text-evogray">{{ category.name }}</h2>

            <div v-if="category.muscle_targets && category.muscle_targets.length"
                 class="grid grid-cols-4 gap-2">
                <div
                    v-for="exercise in getUniqueExercises(category)"
                    :key="exercise.id"
                    @click="openModal(exercise, category)"
                    class="cursor-pointer border rounded-mainRounded mb-3 w-evocardwidth h-evocardheight flex flex-col items-center justify-between"
                    :class="category.color ? bgClasses[category.color] : 'bg-gray-500'"


                >
                    <div class="py-3 flex flex-col items-center justify-around h-4/5">
                        <h3 class="text-2xl font-bold text-evogray">{{ exercise.name }}</h3>
                        <img src="/img/test_img_exo.png" alt="">
                        <!--                    <p class="text-sm text-gray-600">{{ exercise.description || 'Aucune description' }}</p>-->
                        <div v-if="user?.role === 'admin'" class="mt-2 flex gap-2">
                            <Link :href="route('exercises.edit', exercise.id)" class="btn-edit">Modifier</Link>
                            <button @click="deleteExercise(exercise.id)" class="btn-delete">Supprimer</button>
                        </div>
                    </div>
                    <div class="flex justify-between items-center bg-white w-full h-1/5 rounded-b-mainRounded px-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="size-10 text-evogray">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
                        </svg>
                        <div class="flex items-center gap-1 font-bold text-2xl">
                            <p>4,5</p>
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24"
                                 class="size-10"
                                 fill="url(#evogradient)">
                                <defs>
                                    <linearGradient id="evogradient" x1="100%" y1="0%" x2="0%" y2="0%">
                                        <stop offset="0%" stop-color="#3690DE"/>
                                        <stop offset="100%" stop-color="#32D8A0"/>
                                    </linearGradient>
                                </defs>

                                <path
                                    d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else>
                <p class="italic text-gray-500">Aucun exercice pour cette catégorie</p>
            </div>
        </div>
    </div>
    <!-- 🌟 MODALE EXERCICE -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">

        <!-- Bouton croix fixé en haut à droite de l'écran -->
        <button
            @click="closeModal"
            class="fixed top-4 right-4 text-gray-500 hover:text-black text-4xl z-[9999] cursor-pointer bg-white rounded-full w-12 h-12 flex items-center justify-center shadow-lg"
        >
            ×
        </button>

        <div
            class="relative h-fit bg-white border rounded-mainRounded mb-3 w-evocardfullwidth flex flex-col items-center justify-between"
        >
            <!-- contenu modal, sans bouton croix ici -->
            <div
                class="py-3 flex flex-col items-center justify-center h-4/5 w-full rounded-t-mainRounded relative"
                :class="selectedCategoryColor ? bgClasses[selectedCategoryColor] : 'bg-gray-500'"
            >
                <!-- Contenu nom + image -->
                <div class="flex flex-col justify-center items-center">
                    <h3 class="text-2xl font-bold text-evogray">{{ selectedExerciseForModal.name }}</h3>
                    <img src="/img/test_img_exo.png" alt="" />
                </div>

                <!-- Étoile + note -->
                <div class="absolute bottom-3 right-3 flex items-center gap-1 font-bold text-2xl rounded-md px-2 py-1">
                    <p>4,5</p>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-10" fill="url(#evogradient)">
                        <defs>
                            <linearGradient id="evogradient" x1="100%" y1="0%" x2="0%" y2="0%">
                                <stop offset="0%" stop-color="#3690DE" />
                                <stop offset="100%" stop-color="#32D8A0" />
                            </linearGradient>
                        </defs>
                        <path
                            d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"
                        />
                    </svg>
                </div>
            </div>

            <div class=" bg-white rounded-b-mainRounded w-full mb-8">
                <div class="grid grid-cols-3 gap-6  cursor-pointer px-6 py-4">
                    <p
                        @click="activeTab = 'explication'"
                        :class="['rounded-thirdRounded shadow-evoShadow px-8 py-2 text-center', activeTab === 'explication' ? 'bg-evogradientleft font-bold text-white' : '']"
                    >
                        Explication
                    </p>
                    <p
                        @click="activeTab = 'statistique'"
                        :class="['rounded-thirdRounded shadow-evoShadow px-8 py-2 text-center', activeTab === 'statistique' ? 'bg-evogradientleft font-bold text-white' : '']"
                    >
                        Statistique
                    </p>
                    <p
                        @click="activeTab = 'rank'"
                        :class="['rounded-thirdRounded shadow-evoShadow px-8 py-2 text-center', activeTab === 'rank' ? 'bg-evogradientleft font-bold text-white' : '']"
                    >
                        Rank
                    </p>
                </div>

                <div>
                    <div v-if="activeTab === 'explication'" class="px-6 py-4">
                        <p class="text-sm text-gray-600">{{ selectedExerciseForModal.description || 'Aucune description' }}</p>
                    </div>

                    <div v-if="activeTab === 'statistique'" class="px-6 py-4">
                        <div v-if="statsExist">
                            <!-- Ici tu mets ton graphique, par exemple un component chart -->
                            <p>Graphique des stats (placeholder)</p>
                        </div>
                        <div v-else class="px-6 py-4">
                            <p class="italic text-gray-500">Pas de statistiques disponibles pour cet exercice.</p>
                        </div>
                    </div>

                    <div v-if="activeTab === 'rank'" class="bg-evogradientleft h-24 flex justify-center items-center text-4xl">
                        <p class="italic text-white font-bold">Coming soon</p>
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
