export const useAuthStore = defineStore('auth', () => {
  const config = useRuntimeConfig()
  
  const user = ref(null)
  const token = ref(null)
  const isAuthenticated = computed(() => !!token.value)

  // Cargar token del localStorage al inicializar
  const initAuth = () => {
    if (process.client) {
      const savedToken = localStorage.getItem('auth_token')
      const savedUser = localStorage.getItem('auth_user')
      
      if (savedToken && savedUser) {
        token.value = savedToken
        user.value = JSON.parse(savedUser)
      }
    }
  }

  // Enviar OTP
  const sendOtp = async (email: string) => {
    try {
      const { data } = await $fetch(`${config.public.apiBase}/auth/send-otp`, {
        method: 'POST',
        body: { email }
      })
      return { success: true, message: data?.message || 'Código enviado' }
    } catch (error: any) {
      return { 
        success: false, 
        message: error.data?.message || 'Error al enviar código' 
      }
    }
  }

  // Verificar OTP
  const verifyOtp = async (email: string, code: string) => {
    try {
      const data = await $fetch(`${config.public.apiBase}/auth/verify-otp`, {
        method: 'POST',
        body: { email, code }
      })
      
      token.value = data.access_token
      user.value = data.user
      
      // Guardar en localStorage
      if (process.client) {
        localStorage.setItem('auth_token', data.access_token)
        localStorage.setItem('auth_user', JSON.stringify(data.user))
      }
      
      return { success: true, data }
    } catch (error: any) {
      return { 
        success: false, 
        message: error.data?.message || 'Código inválido' 
      }
    }
  }

  // Obtener usuario actual
  const fetchUser = async () => {
    if (!token.value) return null
    
    try {
      const data = await $fetch(`${config.public.apiBase}/user`, {
        headers: {
          Authorization: `Bearer ${token.value}`
        }
      })
      user.value = data.data
      return data.data
    } catch (error) {
      logout()
      return null
    }
  }

  // Logout
  const logout = async () => {
    try {
      if (token.value) {
        await $fetch(`${config.public.apiBase}/logout`, {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${token.value}`
          }
        })
      }
    } catch (error) {
      // Ignorar errores de logout
    } finally {
      token.value = null
      user.value = null
      
      if (process.client) {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')
      }
    }
  }

  return {
    user: readonly(user),
    token: readonly(token),
    isAuthenticated,
    initAuth,
    sendOtp,
    verifyOtp,
    fetchUser,
    logout
  }
})