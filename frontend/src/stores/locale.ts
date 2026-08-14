import { defineStore } from 'pinia'
import { ref, watch } from 'vue'
import type { Locale } from '../i18n/messages'

const KEY = 'slow_goods_locale'

function detect(): Locale {
  const saved = localStorage.getItem(KEY)
  if (saved === 'vi' || saved === 'en') return saved
  return navigator.language.toLowerCase().startsWith('vi') ? 'vi' : 'en'
}

export const useLocaleStore = defineStore('locale', () => {
  const locale = ref<Locale>(detect())

  watch(locale, (value) => {
    localStorage.setItem(KEY, value)
    document.documentElement.lang = value === 'vi' ? 'vi' : 'en'
  }, { immediate: true })

  function setLocale(next: Locale) {
    locale.value = next
  }

  return { locale, setLocale }
})
