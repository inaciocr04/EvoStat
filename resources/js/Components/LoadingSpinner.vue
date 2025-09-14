<script setup>
const props = defineProps({
    size: {
        type: String,
        default: 'medium',
        validator: (value) => ['small', 'medium', 'large'].includes(value)
    },
    color: {
        type: String,
        default: 'blue'
    },
    text: {
        type: String,
        default: ''
    }
});

const sizeClasses = {
    small: 'w-4 h-4',
    medium: 'w-8 h-8',
    large: 'w-12 h-12'
};

const colorClasses = {
    blue: 'text-blue-500',
    green: 'text-green-500',
    red: 'text-red-500',
    gray: 'text-gray-500'
};
</script>

<template>
    <div class="flex flex-col items-center justify-center space-y-2">
        <div class="relative">
            <div 
                :class="[
                    sizeClasses[size],
                    colorClasses[color],
                    'animate-spin'
                ]"
                class="border-2 border-current border-t-transparent rounded-full"
            ></div>
        </div>
        <p v-if="text" class="text-sm text-gray-600 animate-pulse">
            {{ text }}
        </p>
    </div>
</template>
