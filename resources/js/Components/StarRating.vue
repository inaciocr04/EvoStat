<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
    exerciseId: Number,
})

const localAverage = ref(0)
const localUserRating = ref(0)
const isLoading = ref(false)
const hoverRating = ref(0)

async function fetchInitialRating() {
    try {
        const response = await fetch(`/exercises/${props.exerciseId}/rating`)
        const data = await response.json()
        localAverage.value = data.average ?? 0
        localUserRating.value = data.user_rating ?? 0
    } catch (e) {
        console.error('Erreur de chargement des notes :', e)
    }
}

async function rate(star) {
    if (isLoading.value) return

    const isSame = localUserRating.value === star
    const newRating = isSame ? 0 : star

    const oldRating = localUserRating.value
    localUserRating.value = newRating
    isLoading.value = true

    try {
        const res = await fetch(`/exercises/${props.exerciseId}/rate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ rating: newRating }),
        })

        if (!res.ok) throw new Error('Erreur réseau')
        const data = await res.json()
        localAverage.value = data.average ?? localAverage.value
    } catch (e) {
        localUserRating.value = oldRating
        console.error('Erreur d’enregistrement :', e)
    } finally {
        isLoading.value = false
    }
}

const isBubbleOpen = ref(false)
let closeTimeout = null

function openBubble() {
    if (closeTimeout) {
        clearTimeout(closeTimeout)
        closeTimeout = null
    }
    isBubbleOpen.value = true
}

function closeBubbleWithDelay() {
    closeTimeout = setTimeout(() => {
        isBubbleOpen.value = false
        closeTimeout = null
    }, 200) // délai de 200ms avant de fermer la bulle
}

onMounted(fetchInitialRating)
</script>

<template>
    <div
        class="relative inline-block"
        @mouseenter="openBubble"
        @mouseleave="closeBubbleWithDelay"
    >
        <!-- Moyenne + étoile dégradée -->
        <div class="flex items-center gap-1 font-bold text-2xl cursor-default select-none">
            <span>{{ localAverage?.toFixed(1) ?? '0.0' }}</span>
            <svg xmlns="http://www.w3.org/2000/svg"
                 viewBox="0 0 24 24"
                 class="w-7 h-7"
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

        <!-- Bulle étoiles flottante -->
        <div
            v-show="isBubbleOpen"
            @mouseenter="openBubble"
            @mouseleave="closeBubbleWithDelay"
            class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 p-2 rounded-xl shadow-lg bg-white border z-50 flex space-x-1"
            style="user-select:none;"
        >
            <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
                <defs>
                    <linearGradient id="evogradient" x1="100%" y1="0%" x2="0%" y2="0%">
                        <stop offset="0%" stop-color="#3690DE" />
                        <stop offset="100%" stop-color="#32D8A0" />
                    </linearGradient>
                </defs>
            </svg>

            <template v-for="n in 5" :key="n">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    @click="rate(n)"
                    class="w-6 h-6 cursor-pointer transition-transform hover:scale-125"
                    :fill="n <= (hoverRating || localUserRating) ? 'url(#evogradient)' : '#ccc'"
                    @mouseover="hoverRating = n"
                    @mouseleave="hoverRating = 0"
                >
                    <path
                        d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"
                    />
                </svg>
            </template>
        </div>

    </div>
</template>
