<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center px-10 pt-30 h-10 bg-evogradientleft text-sm font-medium leading-5 text-white rounded-secondaryRounded focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
        :'inline-flex items-center px-10 pt-30 h-10 text-sm font-medium leading-5 text-evogray',
);
// const classes = computed(() =>
//     props.active
//         ? 'inline-flex items-center px-10 pt-30 h-10 bg-evogradientleft text-sm font-medium leading-5 text-white rounded-secondaryRounded focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
//         : 'inline-flex items-center px-10 pt-30 h-10 rounded-secondaryRounded border-2 border-transparent text-sm font-medium leading-5 text-evogray hover:text-gray-700 hover:bg-gray-300 hover:text-white focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out',
// );
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
