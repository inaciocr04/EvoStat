<template>
    <AuthenticatedLayout/>
    <main class="min-h-screen px-32">
        <slot/>
    </main>
    <FooterBar />
</template>

<script setup>
import FooterBar from '@/Components/FooterBar.vue'
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
</script>
