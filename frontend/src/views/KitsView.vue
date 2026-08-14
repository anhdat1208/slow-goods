<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { api } from '../api/client'
import type { Kit } from '../types'
import KitCard from '../components/KitCard.vue'
import { useI18n } from '../i18n'

const kits = ref<Kit[]>([])
const { t } = useI18n()

onMounted(async () => {
  kits.value = await api('/kits')
})
</script>

<template>
  <section class="section">
    <div class="container">
      <div class="section-head">
        <div>
          <p class="eyebrow">{{ t('build_a_kit') }}</p>
          <h2>{{ t('kits_title') }}</h2>
          <p class="muted">{{ t('kits_lede') }}</p>
        </div>
      </div>
      <KitCard v-for="kit in kits" :key="kit.slug" :kit="kit" />
    </div>
  </section>
</template>
