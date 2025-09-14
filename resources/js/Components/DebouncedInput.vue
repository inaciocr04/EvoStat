<script setup>
import { ref, watch, nextTick } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: ''
    },
    placeholder: {
        type: String,
        default: ''
    },
    debounceMs: {
        type: Number,
        default: 300
    },
    type: {
        type: String,
        default: 'text'
    }
});

const emit = defineEmits(['update:modelValue']);

const inputValue = ref(props.modelValue);
const isTyping = ref(false);

let debounceTimer = null;

const handleInput = (event) => {
    isTyping.value = true;
    
    // Annuler le timer précédent
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
    
    // Définir un nouveau timer
    debounceTimer = setTimeout(() => {
        emit('update:modelValue', event.target.value);
        isTyping.value = false;
    }, props.debounceMs);
};

// Synchroniser avec la prop modelValue
watch(() => props.modelValue, (newValue) => {
    if (newValue !== inputValue.value) {
        inputValue.value = newValue;
    }
});
</script>

<template>
    <div class="relative">
        <input
            :type="type"
            :placeholder="placeholder"
            v-model="inputValue"
            @input="handleInput"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
            :class="{ 'ring-2 ring-blue-300': isTyping }"
        />
        
        <!-- Indicateur de frappe -->
        <div 
            v-if="isTyping"
            class="absolute right-3 top-1/2 transform -translate-y-1/2"
        >
            <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
        </div>
    </div>
</template>
