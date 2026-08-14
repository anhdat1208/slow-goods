<script setup lang="ts">
import { ref, watch } from 'vue'

const props = defineProps<{
  src?: string | null
  alt?: string
}>()

const FALLBACK = 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=800&q=80'
const current = ref(props.src || FALLBACK)

watch(
  () => props.src,
  (value) => {
    current.value = value || FALLBACK
  },
)

function onError() {
  if (current.value !== FALLBACK) current.value = FALLBACK
}
</script>

<template>
  <img :src="current" :alt="alt || ''" @error="onError" />
</template>
