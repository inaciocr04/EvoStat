<script setup>
import {ref, computed, watch, onUnmounted} from 'vue'
import {gsap} from 'gsap'
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import {router} from '@inertiajs/vue3'

defineOptions({layout: DefaultLayout})


const props = defineProps({
    session: Object,
})

const exercices = ref([])
const currentExIndex = ref(0)
const currentSetIndex = ref(0)
const isResting = ref(false)
const restTimeLeft = ref(0)

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
                rest_time: set.rest_time ?? 60,
                done: set.done ?? false,
            })) : [{
                reps: ex.notes?.default_reps || 8,
                weight: 0,
                rest_time: ex.notes?.default_rest_time || 60,
                done: false,
            }],
        }))
    }
}, {immediate: true})

const currentExercice = computed(() => exercices.value[currentExIndex.value] || null)
const currentSet = computed(() => currentExercice.value?.sets[currentSetIndex.value] || null)

function addSet() {
    if (!currentExercice.value) return

    currentExercice.value.sets.push({
        id: null,
        reps: currentSet.value?.reps || 8,
        weight: currentSet.value?.weight || 0,
        rest_time: currentSet.value?.rest_time || 60,
        done: false,
    })
}


async function addSetToExercise(exIndex) {
    const ex = exercices.value[exIndex]

    const newSet = {
        session_exercise_id: ex.id, // Obligatoire !
        reps: 8,
        weight: 0,
        rest_time: 60,
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

function startRestTimer(duration) {
    if (restTimeline) {
        restTimeline.kill()
        restTimeline = null
    }

    restTimeLeft.value = duration
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
        duration: duration,
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
        alert('Séance terminée !')
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
    startRestTimer(currentSet.value.rest_time)
}

async function onFinishClick() {
    // Aplatir toutes les séries de tous les exercices en un tableau plat
    const allSets = exercices.value.flatMap(ex => {
        return ex.sets.map(set => ({
            session_exercise_id: ex.id,
            reps: set.reps,
            weight: set.weight ?? null,
            rest_time: set.rest_time ?? null,
        }))
    })

    try {
        await axios.post(`/sessions/${props.session.id}/sets`, {
            sets: allSets,
        })

        await axios.patch(`/sessions/${props.session.id}`, {
            status: 'completed',
        })

        alert('Séance enregistrée avec succès !')

        router.visit(route('workout-templates.index'))

    } catch (error) {
        if (error.response) {
            console.error('Erreur API :', error.response.data)
            alert('Erreur lors de l’enregistrement : ' + JSON.stringify(error.response.data))
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
                            {{ restTimeLeft }}
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
                        <label>Repos (sec) :</label>
                        <input type="number" min="0" v-model.number="currentSet.rest_time"
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
                        isResting ? `Repos en cours: ${restTimeLeft}s` : (isSessionFinished ? 'Terminer la séance' : 'Suivant')
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
</template>
