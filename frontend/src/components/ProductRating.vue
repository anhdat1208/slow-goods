<script setup lang="ts">
defineProps<{ rating: number; max?: number }>()
</script>

<template>
  <div class="rating" aria-label="Product rating">
    <span v-for="n in max || 5" :key="n" :class="{ on: n <= Math.round(rating) }">★</span>
    <small v-if="rating">{{ rating.toFixed(1) }}</small>
  </div>
</template>

<style scoped>
.rating {
  display: inline-flex;
  gap: 0.15rem;
  align-items: center;
  color: #c7c7c7;
}

.on {
  color: var(--accent);
}

small {
  margin-left: 0.35rem;
  color: var(--muted);
}
</style>
