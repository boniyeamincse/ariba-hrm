import { RouterProvider, createBrowserRouter } from 'react-router-dom'
import { AuthProvider } from './context/AuthContext'
import { ProtectedRoute } from './routes/ProtectedRoute'
import { AuthLayout } from './layouts/AuthLayout'
import { DashboardLayout } from './layouts/DashboardLayout'
import { PublicLayout } from './layouts/PublicLayout'
import { AboutPage } from './pages/public/AboutPage'
import { BlogPage } from './pages/public/BlogPage'
import { ContactPage } from './pages/public/ContactPage'
import { FAQPage } from './pages/public/FAQPage'
import { FeaturesPage } from './pages/public/FeaturesPage'
import { HomePage } from './pages/public/HomePage'
import { PricingPage } from './pages/public/PricingPage'
import { ForgotPasswordPage } from './pages/auth/ForgotPasswordPage'
import { LoginPage } from './pages/auth/LoginPage'
import { RegisterPage } from './pages/auth/RegisterPage'
import { ResetPasswordPage } from './pages/auth/ResetPasswordPage'
import { TwoFactorPage } from './pages/auth/TwoFactorPage'
import { VerifyEmailPage } from './pages/auth/VerifyEmailPage'
import { AttendancePage } from './pages/dashboard/AttendancePage'
import { DashboardHomePage } from './pages/dashboard/DashboardHomePage'
import { EmployeesPage } from './pages/dashboard/EmployeesPage'
import { LeavePage } from './pages/dashboard/LeavePage'
import { PayrollPage } from './pages/dashboard/PayrollPage'
import { RecruitmentPage } from './pages/dashboard/RecruitmentPage'
import { SettingsPage } from './pages/dashboard/SettingsPage'

const router = createBrowserRouter([
  {
    path: '/',
    element: <PublicLayout />,
    children: [
      { index: true, element: <HomePage /> },
      { path: 'features', element: <FeaturesPage /> },
      { path: 'pricing', element: <PricingPage /> },
      { path: 'about', element: <AboutPage /> },
      { path: 'contact', element: <ContactPage /> },
      { path: 'blog', element: <BlogPage /> },
      { path: 'faq', element: <FAQPage /> },
    ],
  },
  {
    path: '/auth',
    element: <AuthLayout />,
    children: [
      { path: 'login', element: <LoginPage /> },
      { path: 'register', element: <RegisterPage /> },
      { path: 'forgot-password', element: <ForgotPasswordPage /> },
      { path: 'reset-password', element: <ResetPasswordPage /> },
      { path: 'verify-email', element: <VerifyEmailPage /> },
      { path: 'verify-2fa', element: <TwoFactorPage /> },
    ],
  },
  {
    path: '/dashboard',
    element: (
      <ProtectedRoute>
        <DashboardLayout />
      </ProtectedRoute>
    ),
    children: [
      { index: true, element: <DashboardHomePage /> },
      { path: 'employees', element: <EmployeesPage /> },
      { path: 'attendance', element: <AttendancePage /> },
      { path: 'payroll', element: <PayrollPage /> },
      { path: 'leave', element: <LeavePage /> },
      { path: 'recruitment', element: <RecruitmentPage /> },
      { path: 'settings', element: <SettingsPage /> },
    ],
  },
])

function App() {
  return (
    <AuthProvider>
      <RouterProvider router={router} />
    </AuthProvider>
  )
}

export default App
