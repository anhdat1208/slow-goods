<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from '../i18n'

defineProps<{ compact?: boolean }>()

const { t } = useI18n()
const router = useRouter()
const q = ref('')

function submit() {
  const search = q.value.trim()
  router.push({ name: 'products', query: search ? { search } : {} })
}
</script>

<template>
  <form class="search" :class="{ compact }" @submit.prevent="submit">
    <input v-model="q" type="search" :placeholder="t('search_placeholder')" :aria-label="t('search_aria')" />
  </form>
</template>

<style scoped>
.search {
  width: 100%;
}

.search.compact {
  max-width: 180px;
}

input {
  width: 100%;
  border: 1px solid var(--line);
  background: rgba(255, 255, 255, 0.7);
  border-radius: 999px;
  padding: 0.55rem 0.9rem;
}

@media (max-width: 720px) {
  .search.compact {
    display: none;
  }
}
</style>
