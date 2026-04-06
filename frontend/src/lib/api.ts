import axios from 'axios'

export const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? 'http://localhost:8001/api',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
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

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error?.response?.status === 401) {
      localStorage.removeItem('ariba_token')
      localStorage.removeItem('ariba_user')
    }

    return Promise.reject(error)
  },
)