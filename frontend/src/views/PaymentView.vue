<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { api, ApiError, money } from '../api/client'
import type { OrderPaymentStatus } from '../types'
import { useAuthStore } from '../stores/auth'
import { useI18n } from '../i18n'

const POLL_MS = 4000

const route = useRoute()
const auth = useAuthStore()
const { t, locale } = useI18n()

const payment = ref<OrderPaymentStatus | null>(null)
const error = ref('')
const copiedField = ref('')
let pollTimer: ReturnType<typeof setInterval> | null = null
let pollInFlight = false

const isPaid = computed(() => payment.value?.payment_status === 'paid')
const isPendingBank = computed(
  () =>
    payment.value?.payment_method === 'bank_transfer' &&
    payment.value?.payment_status === 'pending',
)

function stopPolling() {
  if (pollTimer) {
    clearInterval(pollTimer)
    pollTimer = null
  }
}

function startPolling() {
  stopPolling()
  pollTimer = setInterval(() => {
    void refreshStatus({ silent: true })
  }, POLL_MS)
}

async function refreshStatus(options: { silent?: boolean } = {}) {
  if (pollInFlight) return
  pollInFlight = true
  try {
    const data = await api<OrderPaymentStatus>(
      `/orders/${route.params.id}/payment-status`,
      {},
      auth.token,
    )
    payment.value = data
    error.value = ''

    if (data.payment_status === 'paid') {
      stopPolling()
    } else if (data.payment_method === 'bank_transfer' && data.payment_status === 'pending' && !pollTimer) {
      startPolling()
    }
  } catch (e) {
    if (!options.silent) {
      error.value = e instanceof ApiError ? e.message : t('payment_load_failed')
    }
    // Keep last known state on transient poll failures.
  } finally {
    pollInFlight = false
  }
}

async function copyText(value: string, field: string) {
  try {
    await navigator.clipboard.writeText(value)
    copiedField.value = field
    window.setTimeout(() => {
      if (copiedField.value === field) copiedField.value = ''
    }, 1600)
  } catch {
    error.value = t('copy_failed')
  }
}

function formatPaidAt(value?: string | null) {
  if (!value) return ''
  return new Date(value).toLocaleString(locale.value === 'vi' ? 'vi-VN' : 'en-GB')
}

onMounted(async () => {
  await refreshStatus()
})

onUnmounted(() => {
  stopPolling()
})
</script>

<template>
  <section class="section">
    <div class="container narrow">
      <p v-if="error" class="error-text">{{ error }}</p>

      <template v-if="payment">
        <template v-if="isPaid">
          <p class="eyebrow">{{ t('payment') }}</p>
          <h2 class="success-title">{{ t('payment_success_title') }}</h2>
          <p class="muted">{{ t('payment_success_lede') }}</p>

          <div class="success-block fade-up">
            <p><strong>{{ payment.order_number }}</strong></p>
            <p>{{ t('amount') }}: {{ money(payment.amount) }}</p>
            <p v-if="payment.paid_at" class="muted">{{ t('paid_at') }}: {{ formatPaidAt(payment.paid_at) }}</p>
          </div>

          <RouterLink class="btn" :to="{ name: 'order', params: { id: payment.order_id } }">
            {{ t('view_order') }}
          </RouterLink>
        </template>

        <template v-else-if="isPendingBank && payment.payment">
          <p class="eyebrow">{{ t('payment_required') }}</p>
          <h2>{{ payment.order_number }}</h2>
          <p class="waiting">{{ t('payment_waiting') }}</p>
          <p class="muted">{{ t('payment_waiting_hint') }}</p>

          <div class="info-grid">
            <div class="info-row">
              <span>{{ t('amount') }}</span>
              <strong>{{ money(payment.amount) }}</strong>
            </div>
            <div class="info-row">
              <span>{{ t('bank') }}</span>
              <strong>{{ payment.payment.bank_name }}</strong>
            </div>
            <div class="info-row">
              <span>{{ t('account_number') }}</span>
              <div class="copy-line">
                <strong>{{ payment.payment.account_number }}</strong>
                <button type="button" class="linkish" @click="copyText(payment.payment.account_number, 'account')">
                  {{ copiedField === 'account' ? t('copied') : t('copy') }}
                </button>
              </div>
            </div>
            <div class="info-row">
              <span>{{ t('account_holder') }}</span>
              <strong>{{ payment.payment.account_holder }}</strong>
            </div>
            <div class="info-row highlight">
              <span>{{ t('transfer_content') }}</span>
              <div class="copy-line">
                <strong>{{ payment.payment.transfer_content }}</strong>
                <button type="button" class="linkish" @click="copyText(payment.payment.transfer_content, 'content')">
                  {{ copiedField === 'content' ? t('copied') : t('copy') }}
                </button>
              </div>
            </div>
          </div>

          <div class="qr-wrap">
            <img
              class="qr"
              :src="payment.payment.qr_image_url"
              :alt="t('qr_alt')"
              width="280"
              height="280"
            />
            <p class="muted center">{{ t('qr_hint') }}</p>
          </div>
        </template>

        <template v-else>
          <p class="eyebrow">{{ t('payment') }}</p>
          <h2>{{ payment.order_number }}</h2>
          <p class="muted">{{ t('payment_status') }}: {{ t('payment_status_' + payment.payment_status) }}</p>
          <RouterLink class="btn" :to="{ name: 'order', params: { id: payment.order_id } }">
            {{ t('view_order') }}
          </RouterLink>
        </template>
      </template>
    </div>
  </section>
</template>

<style scoped>
.narrow { max-width: 640px; }
.waiting {
  margin-top: 0.75rem;
  font-weight: 600;
  color: var(--ink);
}
.success-title { color: var(--accent, #2f4f3e); }
.success-block {
  margin: 1.25rem 0 1.5rem;
  display: grid;
  gap: 0.35rem;
  padding: 1rem;
  background: rgba(47, 79, 62, 0.08);
  border-radius: 12px;
}
.info-grid {
  margin-top: 1.5rem;
  display: grid;
  gap: 0.85rem;
}
.info-row {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: flex-start;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--line);
}
.info-row.highlight strong {
  letter-spacing: 0.02em;
}
.copy-line {
  display: grid;
  justify-items: end;
  gap: 0.25rem;
}
.linkish {
  border: 0;
  background: transparent;
  color: var(--accent, #2f4f3e);
  cursor: pointer;
  padding: 0;
  font: inherit;
  text-decoration: underline;
  text-underline-offset: 2px;
}
.qr-wrap {
  margin-top: 1.75rem;
  display: grid;
  gap: 0.75rem;
  justify-items: center;
}
.qr {
  width: min(280px, 100%);
  height: auto;
  border: 1px solid var(--line);
  border-radius: 12px;
  background: #fff;
}
.center { text-align: center; }
.btn { margin-top: 1rem; display: inline-flex; width: fit-content; }
</style>
