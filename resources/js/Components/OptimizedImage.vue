<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    src: {
        type: String,
        required: true
    },
    alt: {
        type: String,
        default: ''
    },
    width: {
        type: [String, Number],
        default: 'auto'
    },
    height: {
        type: [String, Number],
        default: 'auto'
    },
    lazy: {
        type: Boolean,
        default: true
    },
    placeholder: {
        type: String,
        default: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjNmNGY2Ii8+PC9zdmc+'
    }
});

const isLoaded = ref(false);
const isInView = ref(false);
const imageRef = ref(null);

const handleLoad = () => {
    isLoaded.value = true;
};

const handleIntersection = (entries) => {
    const entry = entries[0];
    if (entry.isIntersecting) {
        isInView.value = true;
    }
};

onMounted(() => {
    if (props.lazy && imageRef.value) {
        const observer = new IntersectionObserver(handleIntersection, {
            threshold: 0.1,
            rootMargin: '50px'
        });
        observer.observe(imageRef.value);
    } else {
        isInView.value = true;
    }
});
</script>

<template>
    <div 
        ref="imageRef"
        class="relative overflow-hidden"
        :style="{ width: typeof width === 'number' ? width + 'px' : width, height: typeof height === 'number' ? height + 'px' : height }"
    >
        <!-- Placeholder -->
        <img
            v-if="!isLoaded"
            :src="placeholder"
            :alt="alt"
            class="absolute inset-0 w-full h-full object-cover blur-sm transition-opacity duration-300"
            :class="{ 'opacity-0': isLoaded }"
        />
        
        <!-- Image principale -->
        <img
            v-if="isInView"
            :src="src"
            :alt="alt"
            @load="handleLoad"
            class="w-full h-full object-cover transition-opacity duration-300"
            :class="{ 'opacity-0': !isLoaded, 'opacity-100': isLoaded }"
            loading="lazy"
        />
        
        <!-- Spinner de chargement -->
        <div 
            v-if="!isLoaded && isInView"
            class="absolute inset-0 flex items-center justify-center bg-gray-100"
        >
            <div class="w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
        </div>
    </div>
</template>
