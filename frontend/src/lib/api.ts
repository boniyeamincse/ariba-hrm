import axios from 'axios'

export const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? 'http://localhost:8001/api',
  headers: {
    'Content-Type': 'application/json',
  },
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('ariba_token')
  const tenant = localStorage.getItem('ariba_tenant')

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  if (tenant) {
    config.headers['X-Tenant'] = tenant
  }

  return config
})