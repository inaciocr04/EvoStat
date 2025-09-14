<script setup>
import {ref, computed, watch, onUnmounted, onMounted} from 'vue'
import {gsap} from 'gsap'
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import {router} from '@inertiajs/vue3'
import axios from 'axios'

defineOptions({layout: DefaultLayout})


const props = defineProps({
    session: Object,
})

const exercices = ref([])
const currentExIndex = ref(0)
const currentSetIndex = ref(0)
const isResting = ref(false)
const restTimeLeft = ref(0)

// Timer global de la séance
const sessionStartTime = ref(null)
const sessionDuration = ref(0)
const sessionTimer = ref(null)
const isSessionStarted = ref(false)

// Modal de fin de séance
const showEndModal = ref(false)
const sessionSummary = ref({
    totalTime: 0,
    totalSets: 0,
    exercises: []
})

const radius = 80
const circumference = 2 * Math.PI * radius

const isMenuOpen = ref(true)
const asideRef = ref(null)

function toggleMenu() {
    if (!asideRef.value) return

    if (isMenuOpen.value) {
        gsap.to(asideRef.value, {
            x: "-100%",
            duration: 0.4,
            ease: "power2.inOut",
            onComplete() {
                isMenuOpen.value = false
            }
        })
    } else {
        gsap.to(asideRef.value, {
            x: "0%",
            duration: 0.4,
            ease: "power2.inOut",
            onComplete() {
                isMenuOpen.value = true
            }
        })
    }

}

// Fonctions du timer global
function startSessionTimer() {
    if (!isSessionStarted.value) {
        sessionStartTime.value = new Date()
        isSessionStarted.value = true
        
        sessionTimer.value = setInterval(() => {
            if (sessionStartTime.value) {
                sessionDuration.value = Math.floor((new Date() - sessionStartTime.value) / 1000)
            }
        }, 1000)
    }
}

// Démarrer automatiquement le timer au chargement de la page
function autoStartTimer() {
    // Attendre un peu que la page soit chargée, puis démarrer le timer
    setTimeout(() => {
        startSessionTimer()
    }, 1000)
}

function stopSessionTimer() {
    if (sessionTimer.value) {
        clearInterval(sessionTimer.value)
        sessionTimer.value = null
    }
}

function formatTime(seconds) {
    const hours = Math.floor(seconds / 3600)
    const minutes = Math.floor((seconds % 3600) / 60)
    const secs = seconds % 60
    
    if (hours > 0) {
        return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
    }
    return `${minutes}:${secs.toString().padStart(2, '0')}`
}

function formatRestTime(seconds) {
    const minutes = Math.floor(seconds / 60)
    const secs = seconds % 60
    
    if (minutes > 0) {
        return `${minutes}:${secs.toString().padStart(2, '0')}`
    }
    return `${secs}s`
}

async function endSession() {
    stopSessionTimer()
    
    // Calculer le résumé de la séance
    const totalSets = exercices.value.reduce((total, ex) => {
        return total + ex.sets.filter(set => set.done).length
    }, 0)
    
    const exercisesSummary = exercices.value.map(ex => ({
        name: ex.name,
        completedSets: ex.sets.filter(set => set.done).length,
        totalSets: ex.sets.length
    }))
    
    sessionSummary.value = {
        totalTime: sessionDuration.value,
        totalSets: totalSets,
        exercises: exercisesSummary
    }
    
    // Enregistrer le temps total dans la base de données
    try {
        await axios.patch(`/sessions/${props.session.id}`, {
            status: 'completed',
            total_duration: sessionDuration.value,
            completed_at: new Date().toISOString().replace('Z', '')
        })
    } catch (error) {
        console.error('Erreur lors de la sauvegarde:', error)
    }
    
    showEndModal.value = true
}

// Démarrer automatiquement le timer au chargement de la page
onMounted(() => {
    autoStartTimer()
})

// Nettoyage du timer quand le composant est détruit
onUnmounted(() => {
    stopSessionTimer()
})

// Stocke la timeline GSAP pour pouvoir l'arrêter si besoin
let restTimeline = null

watch(() => props.session.session_exercises, (newVal) => {
    if (Array.isArray(newVal)) {
        exercices.value = newVal.map(ex => ({
            id: ex.id,
            name: ex.exercise?.name || 'Nom non défini',
            sets: ex.sets?.length ? ex.sets.map(set => ({
                id: set.id,
                reps: set.reps ?? 0,
                weight: set.weight ?? 0,
                rest_time: set.rest_time ?? 1.5,
                done: set.done ?? false,
            })) : [{
                reps: ex.notes?.default_reps || 8,
                weight: 0,
                rest_time: ex.notes?.default_rest_time || 1.5,
                done: false,
            }],
        }))
    }
}, {immediate: true})

const currentExercice = computed(() => exercices.value[currentExIndex.value] || null)
const currentSet = computed(() => currentExercice.value?.sets[currentSetIndex.value] || null)

// Computed property pour convertir le temps de repos au format MM:SS
const currentSetRestTimeFormatted = computed({
    get() {
        if (!currentSet.value) return "01:30"
        
        const totalSeconds = currentSet.value.rest_time
        const minutes = Math.floor(totalSeconds / 60)
        const seconds = totalSeconds % 60
        
        return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
    },
    set(value) {
        if (!currentSet.value) return
        
        // Parser le format MM:SS
        const parts = value.split(':')
        if (parts.length === 2) {
            const minutes = parseInt(parts[0]) || 0
            const seconds = parseInt(parts[1]) || 0
            
            // Validation : secondes entre 0 et 59
            if (seconds >= 0 && seconds <= 59) {
                currentSet.value.rest_time = (minutes * 60) + seconds
            }
        }
    }
})

function addSet() {
    if (!currentExercice.value) return

    currentExercice.value.sets.push({
        id: null,
        reps: currentSet.value?.reps || 8,
        weight: currentSet.value?.weight || 0,
        rest_time: currentSet.value?.rest_time || 1.5,
        done: false,
    })
}


async function addSetToExercise(exIndex) {
    const ex = exercices.value[exIndex]

    const newSet = {
        session_exercise_id: ex.id, // Obligatoire !
        reps: 8,
        weight: 0,
        rest_time: 1.5 * 60, // Convertir minutes en secondes
    }

    try {
        const response = await axios.post(
            `/sessions/${props.session.id}/sets`,
            {
                sets: [newSet], // ✅ Ce que ton contrôleur attend
            }
        )

        ex.sets.push({
            ...newSet,
            id: response.data.id ?? Date.now(), // fallback si pas d'ID retourné
            done: false,
        })
    } catch (error) {
        if (error.response) {
            console.error('Erreur API 422 :', error.response.data)
        } else {
            console.error('Erreur inconnue :', error)
        }
    }
}

function canAddSet(exIndex) {
    if (exIndex !== currentExIndex.value) return false;
    const currentSetsCount = exercices.value[exIndex].sets.length;
    return currentSetIndex.value >= currentSetsCount - 1;
}

function startRestTimer(durationInMinutes) {
    if (restTimeline) {
        restTimeline.kill()
        restTimeline = null
    }

    const durationInSeconds = durationInMinutes * 60 // Convertir minutes en secondes pour le timer
    restTimeLeft.value = durationInSeconds
    isResting.value = true

    // Animation GSAP sur le cercle
    restTimeline = gsap.timeline({
        onComplete() {
            isResting.value = false
            nextStep()
        }
    })

    restTimeline.to(restTimeLeft, {
        value: 0,
        duration: durationInSeconds,
        ease: 'linear',
        onUpdate() {
            // restTimeLeft.value est automatiquement mis à jour par gsap
            restTimeLeft.value = Math.ceil(restTimeLeft.value)
        }
    })
}

const nextItem = computed(() => {
    if (!currentExercice.value) return null

    if (currentSetIndex.value + 1 < currentExercice.value.sets.length) {
        return {
            type: 'set',
            exName: currentExercice.value.name,
            setNumber: currentSetIndex.value + 2,
        }
    } else if (currentExIndex.value + 1 < exercices.value.length) {
        return {
            type: 'exercice',
            exName: exercices.value[currentExIndex.value + 1].name,
        }
    }
    return null
})


function nextStep() {
    if (currentSet.value) currentSet.value.done = true

    if (currentSetIndex.value + 1 < currentExercice.value.sets.length) {
        currentSetIndex.value++
    } else if (currentExIndex.value + 1 < exercices.value.length) {
        currentExIndex.value++
        currentSetIndex.value = 0
    } else {
        // Séance terminée - ouvrir automatiquement la modal de résumé
        endSession()
    }
}

const isSessionFinished = computed(() => {
    if (!currentExercice.value) return true

    return (
        currentExIndex.value === exercices.value.length - 1 &&
        currentSetIndex.value === currentExercice.value.sets.length - 1
    )
})

function onNextClick() {
    if (!currentSet.value) return
    startRestTimer(currentSet.value.rest_time / 60) // Convertir secondes en minutes
}

async function onFinishClick() {
    // Marquer la série actuelle comme terminée
    if (currentSet.value) {
        currentSet.value.done = true
    }
    
    // Aplatir toutes les séries de tous les exercices en un tableau plat
    const allSets = exercices.value.flatMap(ex => {
        return ex.sets.map(set => ({
            session_exercise_id: ex.id,
            reps: set.reps,
            weight: set.weight ?? null,
            rest_time: (set.rest_time ?? null) * 60, // Convertir minutes en secondes
        }))
    })

    try {
        await axios.post(`/sessions/${props.session.id}/sets`, {
            sets: allSets,
        })

        // Appeler endSession() pour afficher la modal de résumé et sauvegarder le temps
        endSession()

    } catch (error) {
        if (error.response) {
            console.error('Erreur API :', error.response.data)
            alert('Erreur lors de l\'enregistrement : ' + JSON.stringify(error.response.data))
        } else {
            console.error('Erreur inconnue :', error)
            alert('Erreur inconnue.')
        }
    }

}


onUnmounted(() => {
    if (restTimeline) restTimeline.kill()
})

</script>

<template>
    <div class="mx-auto max-w-3xl mt-20 flex items-start space-x-2 h-screen">
        <button
            class=" bg-blue-500 text-white px-3 py-1 rounded shadow"
            @click="toggleMenu"
        >
            {{ isMenuOpen ? 'Fermer le menu' : 'Ouvrir le menu' }}
        </button>
        <aside
            ref="asideRef"
            class="fixed top-50 left-0 w-64 max-h-96 bg-gray-50 p-4 border-r overflow-y-auto h-[calc(100vh-5rem)] shadow transition-transform z-40"
            style="transform: translateX(0%)"
        >
            <h3 class="font-bold mb-3 text-gray-800">Séance</h3>
            <div v-for="(ex, exIndex) in exercices" :key="ex.id" class="mb-4">
                <p class="font-semibold">{{ ex.name }}</p>
                <ul class="ml-2 mt-1">
                    <li
                        v-for="(set, setIndex) in ex.sets"
                        :key="set.id || `${ex.id}-set-${setIndex}`"
                        class="text-sm text-gray-600"
                    >
                        Série {{ setIndex + 1 }} — {{ set.reps }} reps @ {{ set.weight }}kg
                    </li>
                </ul>
                <button
                    class="text-xs mt-1"
                    :class="canAddSet(exIndex) ? 'text-blue-600' : 'text-gray-400 cursor-not-allowed'"
                    @click="addSetToExercise(exIndex)"
                    :disabled="!canAddSet(exIndex)"
                    :title="canAddSet(exIndex) ? 'Ajouter une série' : 'Impossible après avoir terminé cet exercice'"
                >
                    {{ canAddSet(exIndex) ? '+ Ajouter une série' : 'Ajout bloqué' }}
                </button>


            </div>
        </aside>
        <div class="w-3/4 p-6 bg-white rounded-lg shadow-md text-center">
            <header class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800">
                    Séance {{ session.workout_template?.name || '...' }} (En cours)
                </h2>
            </header>

            <section v-if="!currentExercice">
                <p>Aucun exercice trouvé.</p>
            </section>

            <section v-else class="flex flex-col">
                <h3 class="text-xl font-semibold mb-4">{{ currentExercice.name }}</h3>

                <div v-if="isResting" class="relative inline-block">
                    <svg
                        :width="(radius + 20) * 2"
                        :height="(radius + 20) * 2"
                        :viewBox="`0 0 ${(radius + 20) * 2} ${(radius + 20) * 2}`"
                        class="mx-auto"
                    >
                        <!-- Cercle de fond -->
                        <circle
                            :cx="radius + 20"
                            :cy="radius + 20"
                            :r="radius"
                            stroke="#ddd"
                            stroke-width="10"
                            fill="none"
                        />
                        <!-- Cercle animé -->
                        <circle
                            ref="progressCircle"
                            :cx="radius + 20"
                            :cy="radius + 20"
                            :r="radius"
                            stroke="#10b981"
                            stroke-width="10"
                            fill="none"
                            :stroke-dasharray="circumference"
                            :stroke-dashoffset="circumference * (restTimeLeft / (currentSet?.rest_time || 1))"
                            stroke-linecap="round"
                            class="transform -rotate-90 origin-center"
                        />
                        <!-- Texte centré -->
                        <text
                            :x="radius + 20"
                            :y="radius + 20"
                            text-anchor="middle"
                            dominant-baseline="middle"
                            font-size="48"
                            fill="#10b981"
                            font-weight="bold"
                        >
                            {{ formatRestTime(restTimeLeft) }}
                        </text>
                    </svg>
                </div>

                <div v-else>
                    <div class="mb-4">
                        <label>Répétitions :</label>
                        <input type="number" min="0" v-model.number="currentSet.reps"
                               class="border rounded p-1 w-20 ml-2"/>
                    </div>

                    <div class="mb-4">
                        <label>Poids (kg) :</label>
                        <input type="number" min="0" step="0.1" v-model.number="currentSet.weight"
                               class="border rounded p-1 w-24 ml-2"/>
                    </div>

                    <div class="mb-4">
                        <label>Repos (MM:SS) :</label>
                        <input type="text" v-model="currentSetRestTimeFormatted"
                               placeholder="01:30"
                               pattern="[0-9]{1,2}:[0-5][0-9]"
                               class="border rounded p-1 w-20 ml-2"/>
                    </div>

                    <button
                        class="bg-green-500 text-white font-semibold py-1 px-3 rounded mt-2"
                        @click="addSetToExercise(exIndex)"
                        :disabled="!canAddSet(exIndex)"
                    >
                        + Ajouter une série
                    </button>


                </div>

                <button
                    class="bg-blue-600 text-white font-bold py-2 px-4 rounded mt-4"
                    @click="isSessionFinished ? onFinishClick() : onNextClick()"
                    :disabled="isResting"
                >
                    {{
                        isResting ? `Repos en cours: ${formatRestTime(restTimeLeft)}` : (isSessionFinished ? 'Terminer la séance' : 'Suivant')
                    }}
                </button>
            </section>
        </div>
        <div v-if="nextItem" class=" p-4 bg-gray-100 rounded shadow-md text-left  w-1/4">
            <strong class="block text-gray-700 mb-1">À venir :</strong>
            <template v-if="nextItem.type === 'set'">
                <span>Série {{ nextItem.setNumber }} de {{ nextItem.exName }}</span>
            </template>
            <template v-else-if="nextItem.type === 'exercice'">
                <span>Exercice suivant : {{ nextItem.exName }}</span>
            </template>
        </div>

    </div>

    <!-- Timer global de la séance -->
    <div class="fixed top-4 right-4 bg-white rounded-lg shadow-lg p-4 z-50">
        <div class="text-center">
            <div class="text-sm text-gray-600 mb-1">Temps de séance</div>
            <div class="text-2xl font-bold text-green-600">{{ formatTime(sessionDuration) }}</div>
        </div>
    </div>

    <!-- Modal de fin de séance -->
    <div v-if="showEndModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h2 class="text-2xl font-bold text-center mb-4">🏆 Séance terminée !</h2>
            
            <div class="space-y-4">
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600">{{ formatTime(sessionSummary.totalTime) }}</div>
                    <div class="text-gray-600">Temps total</div>
                </div>
                
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ sessionSummary.totalSets }}</div>
                    <div class="text-gray-600">Séries complétées</div>
                </div>
                
                <div class="border-t pt-4">
                    <h3 class="font-semibold mb-2">Résumé des exercices :</h3>
                    <div class="space-y-2">
                        <div v-for="exercise in sessionSummary.exercises" :key="exercise.name" 
                             class="flex justify-between items-center p-2 bg-gray-50 rounded">
                            <span class="font-medium">{{ exercise.name }}</span>
                            <span class="text-sm text-gray-600">
                                {{ exercise.completedSets }}/{{ exercise.totalSets }} séries
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-center">
                <button @click="router.visit('/profils')" 
                        class="bg-green-500 text-white px-6 py-3 rounded-lg font-semibold">
                    🏠 Retour à l'accueil
                </button>
            </div>
        </div>
    </div>
</template>
