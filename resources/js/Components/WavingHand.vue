<template>
    <div ref="hand" class="origin-bottom-right">
        <img class="w-28 " src="/img/evomain.png" alt="">
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
