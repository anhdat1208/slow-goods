<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import type { Category } from '../types'
import { useI18n } from '../i18n'
import { localizeCategory } from '../i18n/catalog'
import SafeImage from './SafeImage.vue'

const props = defineProps<{ category: Category }>()
const { locale } = useI18n()
const localized = computed(() => localizeCategory(props.category, locale.value))

const imageSrc = computed(() => {
  const overrides: Record<string, string> = {
    'craft-diy': 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=800&q=80',
    desk: 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?w=800&q=80',
  }
  return overrides[props.category.slug] || props.category.image_url
})
</script>

<template>
  <RouterLink class="category" :to="`/categories/${category.slug}`">
    <SafeImage :src="imageSrc" :alt="localized.name" />
    <div>
      <h3>{{ localized.name }}</h3>
      <p>{{ localized.description }}</p>
    </div>
  </RouterLink>
</template>

<style scoped>
.category {
  display: grid;
  grid-template-rows: 180px auto;
  gap: 0.9rem;
}

:deep(img) {
  width: 100%;
  height: 180px;
  object-fit: cover;
  border-radius: 16px;
}

h3 {
  margin: 0 0 0.35rem;
}

p {
  margin: 0;
  color: var(--muted);
  font-size: 0.95rem;
}
</style>
