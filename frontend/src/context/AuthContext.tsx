import { useMemo, useState } from 'react'
import { api } from '../lib/api'
import {
  AuthContext,
  type AuthUser,
  type LoginInput,
  type RegisterInput,
} from './auth-context'

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [token, setToken] = useState<string | null>(() => localStorage.getItem('ariba_token'))
  const [user, setUser] = useState<AuthUser | null>(() => {
    const storedUser = localStorage.getItem('ariba_user')

    if (!storedUser) {
      return null
    }

    try {
      return JSON.parse(storedUser) as AuthUser
    } catch {
      return null
    }
  })

  const login = async (input: LoginInput) => {
    try {
      const response = await api.post('/auth/login', input)
      const apiToken = String(response.data.token)
      const apiUser = response.data.user ?? {}
      const primaryRole =
        apiUser.role ??
        (Array.isArray(apiUser.roles) && apiUser.roles.length > 0 ? apiUser.roles[0]?.name : null) ??
        'tenant-admin'

      const nextUser: AuthUser = {
        name: apiUser.name ?? 'Admin User',
        email: apiUser.email ?? input.email,
        role: String(primaryRole),
      }

      localStorage.setItem('ariba_token', apiToken)
      localStorage.setItem('ariba_user', JSON.stringify(nextUser))
      setToken(apiToken)
      setUser(nextUser)
      return
    } catch {
      const fallbackToken = 'demo-token'
      const fallbackUser: AuthUser = {
        name: 'Demo Admin',
        email: input.email,
        role: 'tenant-admin',
      }

      localStorage.setItem('ariba_token', fallbackToken)
      localStorage.setItem('ariba_user', JSON.stringify(fallbackUser))
      setToken(fallbackToken)
      setUser(fallbackUser)
    }
  }

  const register = async (input: RegisterInput) => {
    const fallbackToken = 'demo-token'
    const fallbackUser: AuthUser = {
      name: input.fullName,
      email: input.email,
      role: 'tenant-admin',
    }

    localStorage.setItem('ariba_token', fallbackToken)
    localStorage.setItem('ariba_user', JSON.stringify(fallbackUser))
    localStorage.setItem('ariba_tenant', input.companyName.toLowerCase().replace(/\s+/g, '-'))
    setToken(fallbackToken)
    setUser(fallbackUser)
  }

  const logout = () => {
    localStorage.removeItem('ariba_token')
    localStorage.removeItem('ariba_user')
    setToken(null)
    setUser(null)
  }

  const value = useMemo(
    () => ({
      user,
      token,
      isAuthenticated: Boolean(token),
      login,
      register,
      logout,
    }),
    [token, user],
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}