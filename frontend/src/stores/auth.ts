import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { api } from '../api/client'
import type { User } from '../types'

const TOKEN_KEY = 'slow_goods_token'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem(TOKEN_KEY))
  const loading = ref(false)

  const isAuthenticated = computed(() => !!user.value && !!token.value)
  const isAdmin = computed(() => !!user.value?.is_admin)

  async function register(payload: {
    name: string
    email: string
    password: string
    password_confirmation: string
    phone?: string
  }) {
    loading.value = true
    try {
      const data = await api<{ user: User; token: string }>('/auth/register', {
        method: 'POST',
        body: JSON.stringify(payload),
      })
      setSession(data.user, data.token)
      return data.user
    } finally {
      loading.value = false
    }
  }

  async function login(email: string, password: string) {
    loading.value = true
    try {
      const data = await api<{ user: User; token: string }>('/auth/login', {
        method: 'POST',
        body: JSON.stringify({ email, password }),
      })
      setSession(data.user, data.token)
      return data.user
    } finally {
      loading.value = false
    }
  }

  async function fetchUser() {
    if (!token.value) return null
    try {
      user.value = await api<User>('/auth/user', {}, token.value)
      return user.value
    } catch {
      clearSession()
      return null
    }
  }

  async function logout() {
    if (token.value) {
      try {
        await api('/auth/logout', { method: 'POST' }, token.value)
      } catch {
        // ignore
      }
    }
    clearSession()
  }

  function setSession(nextUser: User, nextToken: string) {
    user.value = nextUser
    token.value = nextToken
    localStorage.setItem(TOKEN_KEY, nextToken)
  }

  function clearSession() {
    user.value = null
    token.value = null
    localStorage.removeItem(TOKEN_KEY)
  }

  return {
    user,
    token,
    loading,
    isAuthenticated,
    isAdmin,
    register,
    login,
    logout,
    fetchUser,
  }
})
