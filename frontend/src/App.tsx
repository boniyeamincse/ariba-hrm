import { Navigate, RouterProvider, createBrowserRouter } from 'react-router-dom'
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
import { ResetPasswordPage } from './pages/auth/ResetPasswordPage'
import { TwoFactorPage } from './pages/auth/TwoFactorPage'
import { VerifyEmailPage } from './pages/auth/VerifyEmailPage'
import { AttendancePage } from './components/dashboard/AttendancePage'
import { Dashboard } from './components/dashboard/Dashboard'
import { EmployeesPage } from './components/dashboard/EmployeesPage'
import { LeavePage } from './components/dashboard/LeavePage'
import { PayrollPage } from './components/dashboard/PayrollPage'
import { RecruitmentPage } from './components/dashboard/RecruitmentPage'
import { SettingsPage } from './components/dashboard/SettingsPage'
import { UsersPage } from './components/dashboard/UsersPage'
import { PatientSearchPage } from './components/dashboard/PatientSearchPage'
import { PatientRegistrationPage } from './components/dashboard/PatientRegistrationPage'
import { PatientProfilePage } from './components/dashboard/PatientProfilePage'
import { TaskDashboard } from './components/dashboard/TaskDashboard'
import { ModulePlaceholderPage } from './components/dashboard/ModulePlaceholderPage'
import { OpdQueuePage } from './components/dashboard/OpdQueuePage'
import { OpdVitalsEntryPage } from './components/dashboard/OpdVitalsEntryPage'
import { OpdConsultationPage } from './components/dashboard/OpdConsultationPage'

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
      { index: true, element: <Navigate to="login" replace /> },
      { path: 'login', element: <LoginPage /> },
      { path: 'register', element: <Navigate to="/auth/login" replace /> },
      { path: 'forgot-password', element: <ForgotPasswordPage /> },
      { path: 'reset-password', element: <ResetPasswordPage /> },
      { path: 'verify-email', element: <VerifyEmailPage /> },
      { path: 'verify-2fa', element: <TwoFactorPage /> },
    ],
  },
  { path: '/login', element: <Navigate to="/auth/login" replace /> },
  { path: '/register', element: <Navigate to="/auth/login" replace /> },
  {
    path: '/dashboard',
    element: (
      <ProtectedRoute>
        <DashboardLayout />
      </ProtectedRoute>
    ),
    children: [
      { index: true, element: <Dashboard /> },
      { path: 'users', element: <UsersPage /> },
      {
        path: 'patients',
        element: <ModulePlaceholderPage title="Patients" subtitle="Patient search, registration, and demographic records." />,
      },
      {
        path: 'appointments',
        element: <ModulePlaceholderPage title="Appointments" subtitle="Appointment scheduling, booking, and slot management." />,
      },
      {
        path: 'billing',
        element: <ModulePlaceholderPage title="Billing" subtitle="Billing records, payments, and finance workflows." />,
      },
      {
        path: 'inventory',
        element: <ModulePlaceholderPage title="Inventory" subtitle="Stock levels, purchase orders, and suppliers." />,
      },
      {
        path: 'reports',
        element: <ModulePlaceholderPage title="Reports" subtitle="Operational, financial, and compliance analytics." />,
      },
      { path: 'opd/queue', element: <OpdQueuePage /> },
      { path: 'opd/vitals', element: <OpdVitalsEntryPage /> },
      { path: 'opd/consultations', element: <OpdConsultationPage /> },
      { path: 'tasks', element: <TaskDashboard /> },
      { path: 'employees', element: <EmployeesPage /> },
      { path: 'attendance', element: <AttendancePage /> },
      { path: 'payroll', element: <PayrollPage /> },
      { path: 'leave', element: <LeavePage /> },
      { path: 'recruitment', element: <RecruitmentPage /> },
      { path: 'settings', element: <SettingsPage /> },
    ],
  },
  {
    path: '/clinical',
    element: (
      <ProtectedRoute>
        <DashboardLayout />
      </ProtectedRoute>
    ),
    children: [
      { path: 'patients', element: <PatientSearchPage /> },
      { path: 'patients/register', element: <PatientRegistrationPage /> },
      { path: 'patients/:id', element: <PatientProfilePage /> },
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
