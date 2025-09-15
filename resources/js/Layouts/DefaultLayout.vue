<template>
    <AuthenticatedLayout/>
    <main class="px-4 sm:px-8 md:px-16 lg:px-24 xl:px-32">
        <slot/>
    </main>
    <FooterBar />
</template>

<script setup>
import FooterBar from '@/Components/FooterBar.vue'
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
</script>
