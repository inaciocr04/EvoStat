<script setup>
const props = defineProps({
    type: {
        type: String,
        default: 'card',
        validator: (value) => ['card', 'list', 'table', 'text'].includes(value)
    },
    count: {
        type: Number,
        default: 1
    }
});
</script>

<template>
    <div v-for="n in count" :key="n">
        <!-- Card skeleton -->
        <div v-if="type === 'card'" class="bg-white border border-gray-200 rounded-lg shadow-md p-6 animate-pulse">
            <div class="flex justify-between items-start mb-4">
                <div class="h-6 bg-gray-200 rounded w-3/4"></div>
                <div class="h-6 w-6 bg-gray-200 rounded"></div>
            </div>
            <div class="space-y-3">
                <div class="h-4 bg-gray-200 rounded w-full"></div>
                <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                <div class="h-4 bg-gray-200 rounded w-4/6"></div>
            </div>
            <div class="mt-4 flex space-x-2">
                <div class="h-6 bg-gray-200 rounded w-16"></div>
                <div class="h-6 bg-gray-200 rounded w-20"></div>
            </div>
        </div>

        <!-- List skeleton -->
        <div v-else-if="type === 'list'" class="flex items-center space-x-4 p-4 animate-pulse">
            <div class="h-12 w-12 bg-gray-200 rounded-full"></div>
            <div class="flex-1 space-y-2">
                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
            </div>
        </div>

        <!-- Table skeleton -->
        <div v-else-if="type === 'table'" class="animate-pulse">
            <div class="h-10 bg-gray-200 rounded mb-2"></div>
            <div class="space-y-2">
                <div class="h-8 bg-gray-200 rounded"></div>
                <div class="h-8 bg-gray-200 rounded"></div>
                <div class="h-8 bg-gray-200 rounded"></div>
            </div>
        </div>

        <!-- Text skeleton -->
        <div v-else-if="type === 'text'" class="space-y-2 animate-pulse">
            <div class="h-4 bg-gray-200 rounded w-full"></div>
            <div class="h-4 bg-gray-200 rounded w-5/6"></div>
            <div class="h-4 bg-gray-200 rounded w-4/6"></div>
        </div>
    </div>
</template>
