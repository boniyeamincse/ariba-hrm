import { createContext } from 'react'

export type AuthUser = {
  name: string
  email: string
  role: string
}

export type LoginInput = {
  email: string
  password: string
}

export type RegisterInput = {
  companyName: string
  fullName: string
  email: string
  password: string
}

export type AuthContextValue = {
  user: AuthUser | null
  token: string | null
  isAuthenticated: boolean
  login: (input: LoginInput) => Promise<void>
  register: (input: RegisterInput) => Promise<void>
  logout: () => void
}

export const AuthContext = createContext<AuthContextValue | undefined>(undefined)