<script setup lang="ts">
import { useI18n } from '../i18n'
import type { Locale } from '../i18n/messages'

const { locale, setLocale } = useI18n()

function toggle(next: Locale) {
  setLocale(next)
}
</script>

<template>
  <div class="lang" role="group" aria-label="Language">
    <button type="button" :class="{ on: locale === 'en' }" @click="toggle('en')">EN</button>
    <button type="button" :class="{ on: locale === 'vi' }" @click="toggle('vi')">VI</button>
  </div>
</template>

<style scoped>
.lang {
  display: inline-flex;
  border: 1px solid var(--line);
  border-radius: 999px;
  overflow: hidden;
  background: rgba(255, 255, 255, 0.7);
}

button {
  border: none;
  background: transparent;
  padding: 0.35rem 0.55rem;
  cursor: pointer;
  color: var(--muted);
  font-size: 0.78rem;
  letter-spacing: 0.04em;
}

button.on {
  background: var(--accent);
  color: #f5f7f4;
}
</style>
