<template>
    <div ref="hand" class="text-6xl w-fit origin-bottom-right">
        👋
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { gsap } from 'gsap'

const hand = ref(null)

onMounted(() => {
    gsap.to(hand.value, {
        rotation: 20,
        yoyo: true,
        repeat: -1,
        ease: 'power1.inOut',
        duration: 1,
        transformOrigin: 'bottom right'
    })
})
</script>
