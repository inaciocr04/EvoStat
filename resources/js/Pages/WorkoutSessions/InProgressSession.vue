<script setup>
import { ref, computed, watch, onUnmounted } from 'vue'
import { gsap } from 'gsap'
import DefaultLayout from '@/Layouts/DefaultLayout.vue'

defineOptions({ layout: DefaultLayout })


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
}, { immediate: true })

const currentExercice = computed(() => exercices.value[currentExIndex.value] || null)
const currentSet = computed(() => currentExercice.value?.sets[currentSetIndex.value] || null)

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
    // ton code saveSets ici
}

onUnmounted(() => {
    if (restTimeline) restTimeline.kill()
})

</script>

<template>
    <div class="max-w-3xl mx-auto mt-20 p-6 bg-white rounded-lg shadow-md text-center">
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
                    <input type="number" min="0" v-model.number="currentSet.reps" class="border rounded p-1 w-20 ml-2" />
                </div>

                <div class="mb-4">
                    <label>Poids (kg) :</label>
                    <input type="number" min="0" step="0.1" v-model.number="currentSet.weight" class="border rounded p-1 w-24 ml-2" />
                </div>

                <div class="mb-4">
                    <label>Repos (sec) :</label>
                    <input type="number" min="0" v-model.number="currentSet.rest_time" class="border rounded p-1 w-20 ml-2" />
                </div>
            </div>

            <button
                class="bg-blue-600 text-white font-bold py-2 px-4 rounded mt-4"
                @click="isSessionFinished ? onFinishClick() : onNextClick()"
                :disabled="isResting"
            >
                {{ isResting ? `Repos en cours: ${restTimeLeft}s` : (isSessionFinished ? 'Terminer la séance' : 'Suivant') }}
            </button>
        </section>
    </div>
</template>
