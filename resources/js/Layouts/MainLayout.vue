<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Global Toast Container -->
        <Toast :toasts="toasts" @remove="removeToast" />

        <!-- Page Content -->
        <slot />
    </div>
</template>

<script setup>
import { useFlashToast } from '@/composables/useFlashToast';
import Toast from '@/Components/Toast.vue';

const { toasts, removeToast } = useFlashToast();
</script>
