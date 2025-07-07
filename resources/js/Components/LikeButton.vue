<script setup>
import { ref } from 'vue'

const props = defineProps({
    exerciseId: Number,
    initialLiked: Boolean,
    initialCount: Number,
})

const isLiked = ref(props.initialLiked)
const likeCount = ref(props.initialCount)
const isLoading = ref(false)
const error = ref(null)

async function toggleLike() {
    if (isLoading.value) return

    // Sauvegarde l'état actuel au cas où il faille rollback
    const oldLiked = isLiked.value
    const oldCount = likeCount.value

    // Optimistic update : on inverse direct l’état local
    isLiked.value = !isLiked.value
    likeCount.value += isLiked.value ? 1 : -1

    isLoading.value = true
    error.value = null

    try {
        const response = await fetch(`/exercises/${props.exerciseId}/like`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
        })

        if (!response.ok) throw new Error('Erreur réseau')

        // Ici on peut éventuellement vérifier la réponse et corriger l’état local
        // const data = await response.json()
        // isLiked.value = data.liked
        // likeCount.value = data.likes_count

    } catch (err) {
        // Rollback en cas d'erreur
        isLiked.value = oldLiked
        likeCount.value = oldCount
        error.value = 'Impossible de changer le like. Réessaye.'
    } finally {
        isLoading.value = false
    }
}
</script>

<template>
    <button
        @click.prevent="toggleLike"
        :aria-pressed="isLiked.toString()"
        class="flex items-center gap-1 text-xl font-semibold select-none transition
           focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-emerald-400
           rounded-md px-1 py-1"
        :disabled="isLoading"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            :class="{
        'fill-rose-500 stroke-rose-500 scale-110 animate-pulse': isLiked,
        'fill-none stroke-gray-400': !isLiked,
      }"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke-linejoin="round"
            stroke-linecap="round"
            stroke="currentColor"
            class="size-6 transition-transform duration-300 ease-in-out"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"
            />
        </svg>

        <span
            class="transition-all duration-300 text-gray-700 dark:text-gray-300 select-none"
            :class="{ 'text-rose-500 font-bold': isLiked }"
        >
      {{ likeCount }}
    </span>
    </button>

    <p v-if="error" class="text-red-500 mt-2">{{ error }}</p>
</template>

<style scoped>
@keyframes pulse {
    0%,
    100% {
        transform: scale(1.1);
        opacity: 1;
    }
    50% {
        transform: scale(1.3);
        opacity: 0.8;
    }
}

.animate-pulse {
    animation: pulse 0.3s ease-in-out;
}
</style>
