import { computed } from 'vue'
import { useLocaleStore } from '../stores/locale'
import { messages, type Locale } from './messages'

export function t(key: string, locale: Locale, vars?: Record<string, string | number>): string {
  let str = messages[locale][key] ?? messages.en[key] ?? key
  if (vars) {
    for (const [k, v] of Object.entries(vars)) {
      str = str.replaceAll(`{${k}}`, String(v))
    }
  }
  return str
}

export function useI18n() {
  const store = useLocaleStore()
  const locale = computed(() => store.locale)

  function translate(key: string, vars?: Record<string, string | number>) {
    return t(key, store.locale, vars)
  }

  return {
    t: translate,
    locale,
    setLocale: store.setLocale,
  }
}
