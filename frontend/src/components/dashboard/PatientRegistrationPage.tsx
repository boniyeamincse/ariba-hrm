import { useState } from 'react'
import { useNavigate, useSearchParams } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { motion } from 'framer-motion'
import { AlertCircle, ArrowLeft, Upload, Check } from 'lucide-react'
import { api } from '../../lib/api'
import { PageShell } from '../../components/ui/PageShell'
import { PhotoUploadModal } from '../../components/clinical/PhotoUploadModal'
import { DuplicatePatientModal } from '../../components/clinical/DuplicatePatientModal'

type RegistrationFormData = {
  first_name: string
  middle_name?: string
  last_name?: string
  date_of_birth?: string
  gender?: string
  blood_group?: string
  phone: string
  email?: string
  national_id_no?: string
  passport_no?: string
  address?: string
  city?: string
  state?: string
  postal_code?: string
  country?: string
  marital_status?: string
  occupation?: string
  religion?: string
  emergency_contact_name?: string
  emergency_contact_phone?: string
  emergency_contact_relation?: string
}

type TabType = 'walk-in' | 'pre-registered'

export function PatientRegistrationPage() {
  const navigate = useNavigate()
  const [searchParams] = useSearchParams()
  const [activeTab, setActiveTab] = useState<TabType>((searchParams.get('tab') as TabType) || 'walk-in')
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [duplicateWarning, setDuplicateWarning] = useState<any>(null)
  const [showPhotoModal, setShowPhotoModal] = useState(false)
  const [photoFile, setPhotoFile] = useState<File | null>(null)
  const [photoPreview, setPhotoPreview] = useState('')
  const [patientUhid, setPatientUhid] = useState('')
  const [submitSuccess, setSubmitSuccess] = useState(false)
  const [submitError, setSubmitError] = useState('')

  const { register, handleSubmit, watch, formState: { errors } } = useForm<RegistrationFormData>({
    defaultValues: {
      country: 'Bangladesh',
    },
  })

  const onSubmit = async (data: RegistrationFormData) => {
    setIsSubmitting(true)
    setSubmitError('')
    
    try {
      // Submit patient registration
      const response = await api.post('/clinical/patients', {
        ...data,
        date_of_birth: data.date_of_birth ? new Date(data.date_of_birth).toISOString().split('T')[0] : null,
      })

      const newPatient = response.data.patient

      // Check for duplicate warning
      if (response.data.duplicate_detected && response.data.duplicate_match) {
        setDuplicateWarning({
          patient: newPatient,
          duplicate: response.data.duplicate_match,
        })
      } else {
        // Auto upload photo if provided
        if (photoFile) {
          const formData = new FormData()
          formData.append('photo', photoFile)
          
          try {
            await api.post(`/clinical/patients/${newPatient.id}/photo`, formData, {
              headers: { 'Content-Type': 'multipart/form-data' },
            })
          } catch (error) {
            console.error('Photo upload failed:', error)
          }
        }

        setPatientUhid(newPatient.uhid)
        setSubmitSuccess(true)
      }
    } catch (error: any) {
      setSubmitError(error.response?.data?.message || 'Registration failed. Please try again.')
    } finally {
      setIsSubmitting(false)
    }
  }

  if (submitSuccess) {
    return (
      <PageShell title="Registration Complete" subtitle="Patient has been successfully registered">
        <motion.div
          initial={{ opacity: 0, scale: 0.9 }}
          animate={{ opacity: 1, scale: 1 }}
          className="mx-auto max-w-2xl"
        >
          <div className="rounded-3xl border border-emerald-500/20 bg-emerald-500/5 p-12 text-center">
            <div className="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500/20">
              <Check className="h-8 w-8 text-emerald-500" />
            </div>
            <h2 className="mb-2 text-2xl font-bold text-slate-900">Patient Registered</h2>
            <p className="mb-6 text-slate-500">New patient UHID: <span className="font-mono font-semibold text-emerald-400">{patientUhid}</span></p>
            <div className="flex flex-col gap-3 sm:flex-row sm:justify-center">
              <button
                onClick={() => navigate(`/clinical/patients`)}
                className="rounded-xl bg-emerald-500 px-6 py-2 font-semibold text-white transition-all hover:bg-emerald-600"
              >
                View Patient Profile
              </button>
              <button
                onClick={() => navigate(`/clinical/patients`)}
                className="rounded-xl border border-slate-200 px-6 py-2 font-semibold text-slate-900 transition-all hover:bg-slate-50"
              >
                Back to Search
              </button>
            </div>
          </div>
        </motion.div>
      </PageShell>
    )
  }

  return (
    <PageShell
      title="Register Patient"
      subtitle="Create a new patient record in the system"
    >
      <DuplicatePatientModal
        isOpen={!!duplicateWarning}
        duplicate={duplicateWarning?.duplicate}
        newPatient={duplicateWarning?.patient}
        onContinue={() => {
          setPatientUhid(duplicateWarning.patient.uhid)
          setSubmitSuccess(true)
          setDuplicateWarning(null)
        }}
        onMerge={() => {
          // TODO: Implement merge logic
          setPatientUhid(duplicateWarning.patient.uhid)
          setSubmitSuccess(true)
          setDuplicateWarning(null)
        }}
        onCancel={() => setDuplicateWarning(null)}
      />

      <PhotoUploadModal
        isOpen={showPhotoModal}
        onClose={() => setShowPhotoModal(false)}
        onPhotoCapture={(file) => {
          setPhotoFile(file)
          setPhotoPreview(URL.createObjectURL(file))
          setShowPhotoModal(false)
        }}
      />

      <div className="mx-auto max-w-4xl">
        {/* Tabs */}
        <div className="mb-8 flex gap-2 border-b border-slate-200">
          {['walk-in', 'pre-registered'].map((tab) => (
            <button
              key={tab}
              onClick={() => setActiveTab(tab as TabType)}
              className={`px-4 py-3 font-semibold transition-all ${
                activeTab === tab
                  ? 'border-b-2 border-emerald-500 text-slate-900'
                  : 'text-slate-500 hover:text-slate-900'
              }`}
            >
              {tab === 'walk-in' ? 'Walk-in Patient' : 'Pre-Registered Patient'}
            </button>
          ))}
        </div>

        {submitError && (
          <motion.div
            initial={{ opacity: 0, y: -10 }}
            animate={{ opacity: 1, y: 0 }}
            className="mb-6 flex gap-3 rounded-xl border border-rose-500/20 bg-rose-500/10 p-4"
          >
            <AlertCircle className="h-5 w-5 flex-shrink-0 text-rose-500 mt-0.5" />
            <div>
              <p className="font-semibold text-slate-900">Registration Error</p>
              <p className="text-sm text-slate-600">{submitError}</p>
            </div>
          </motion.div>
        )}

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
          {/* Basic Information */}
          <div className="rounded-2xl border border-slate-200 bg-slate-50 p-6">
            <h3 className="mb-4 font-semibold text-slate-900">Basic Information</h3>
            <div className="grid gap-4 sm:grid-cols-2">
              <div>
                <label className="block text-sm font-medium text-slate-600 mb-2">
                  First Name *
                </label>
                <input
                  {...register('first_name', { required: 'First name is required' })}
                  type="text"
                  className={`w-full rounded-lg border ${
                    errors.first_name ? 'border-rose-500' : 'border-slate-200'
                  } bg-slate-50 px-4 py-2 text-slate-900 placeholder-slate-500 transition-all hover:bg-slate-100 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50`}
                  placeholder="John"
                />
                {errors.first_name && (
                  <p className="mt-1 text-xs text-rose-500">{errors.first_name.message}</p>
                )}
              </div>

              <div>
                <label className="block text-sm font-medium text-slate-600 mb-2">
                  Middle Name
                </label>
                <input
                  {...register('middle_name')}
                  type="text"
                  className="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 placeholder-slate-500 transition-all hover:bg-slate-100 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                  placeholder="Middle"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-slate-600 mb-2">
                  Last Name
                </label>
                <input
                  {...register('last_name')}
                  type="text"
                  className="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 placeholder-slate-500 transition-all hover:bg-slate-100 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                  placeholder="Doe"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-slate-600 mb-2">
                  Date of Birth
                </label>
                <input
                  {...register('date_of_birth')}
                  type="date"
                  className="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 placeholder-slate-500 transition-all hover:bg-slate-100 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-slate-600 mb-2">
                  Phone *
                </label>
                <input
                  {...register('phone', { required: 'Phone is required' })}
                  type="tel"
                  className={`w-full rounded-lg border ${
                    errors.phone ? 'border-rose-500' : 'border-slate-200'
                  } bg-slate-50 px-4 py-2 text-slate-900 placeholder-slate-500 transition-all hover:bg-slate-100 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50`}
                  placeholder="+880 1700 000000"
                />
                {errors.phone && (
                  <p className="mt-1 text-xs text-rose-500">{errors.phone.message}</p>
                )}
              </div>

              <div>
                <label className="block text-sm font-medium text-slate-600 mb-2">
                  Email
                </label>
                <input
                  {...register('email')}
                  type="email"
                  className="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 placeholder-slate-500 transition-all hover:bg-slate-100 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                  placeholder="john@example.com"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-slate-600 mb-2">
                  Gender
                </label>
                <select
                  {...register('gender')}
                  className="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 transition-all hover:bg-slate-100 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                >
                  <option value="">Select Gender</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-slate-600 mb-2">
                  Blood Group
                </label>
                <select
                  {...register('blood_group')}
                  className="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 transition-all hover:bg-slate-100 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                >
                  <option value="">Select Blood Group</option>
                  <option value="O+">O+</option>
                  <option value="O-">O-</option>
                  <option value="A+">A+</option>
                  <option value="A-">A-</option>
                  <option value="B+">B+</option>
                  <option value="B-">B-</option>
                  <option value="AB+">AB+</option>
                  <option value="AB-">AB-</option>
                </select>
              </div>
            </div>
          </div>

          {/* Identification */}
          <div className="rounded-2xl border border-slate-200 bg-slate-50 p-6">
            <h3 className="mb-4 font-semibold text-slate-900">Identification</h3>
            <div className="grid gap-4 sm:grid-cols-2">
              <div>
                <label className="block text-sm font-medium text-slate-600 mb-2">
                  National ID Number
                </label>
                <input
                  {...register('national_id_no')}
                  type="text"
                  className="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 placeholder-slate-500 transition-all hover:bg-slate-100 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                  placeholder="1234567890"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-slate-600 mb-2">
                  Passport Number
                </label>
                <input
                  {...register('passport_no')}
                  type="text"
                  className="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 placeholder-slate-500 transition-all hover:bg-slate-100 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                  placeholder="AB123456"
                />
              </div>
            </div>
          </div>

          {/* Address */}
          <div className="rounded-2xl border border-slate-200 bg-slate-50 p-6">
            <h3 className="mb-4 font-semibold text-slate-900">Address Information</h3>
            <div className="space-y-4">
              <input
                {...register('address')}
                type="text"
                className="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 placeholder-slate-500 transition-all hover:bg-slate-100 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                placeholder="Street Address"
              />
              <div className="grid gap-4 sm:grid-cols-3">
                <input
                  {...register('city')}
                  type="text"
                  className="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 placeholder-slate-500 transition-all hover:bg-slate-100 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                  placeholder="City"
                />
                <input
                  {...register('state')}
                  type="text"
                  className="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 placeholder-slate-500 transition-all hover:bg-slate-100 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                  placeholder="State/Province"
                />
                <input
                  {...register('postal_code')}
                  type="text"
                  className="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 placeholder-slate-500 transition-all hover:bg-slate-100 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                  placeholder="Postal Code"
                />
              </div>
              <select
                {...register('country')}
                className="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 transition-all hover:bg-slate-100 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
              >
                <option value="Bangladesh">Bangladesh</option>
                <option value="India">India</option>
                <option value="Pakistan">Pakistan</option>
                <option value="Nepal">Nepal</option>
                <option value="Other">Other</option>
              </select>
            </div>
          </div>

          {/* Emergency Contact */}
          <div className="rounded-2xl border border-slate-200 bg-slate-50 p-6">
            <h3 className="mb-4 font-semibold text-slate-900">Emergency Contact</h3>
            <div className="grid gap-4 sm:grid-cols-3">
              <input
                {...register('emergency_contact_name')}
                type="text"
                className="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 placeholder-slate-500 transition-all hover:bg-slate-100 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                placeholder="Full Name"
              />
              <input
                {...register('emergency_contact_phone')}
                type="tel"
                className="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 placeholder-slate-500 transition-all hover:bg-slate-100 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                placeholder="Phone Number"
              />
              <input
                {...register('emergency_contact_relation')}
                type="text"
                className="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-slate-900 placeholder-slate-500 transition-all hover:bg-slate-100 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
                placeholder="Relation (e.g., Spouse)"
              />
            </div>
          </div>

          {/* Photo Upload */}
          <div className="rounded-2xl border border-slate-200 bg-slate-50 p-6">
            <h3 className="mb-4 font-semibold text-slate-900">Patient Photo</h3>
            {photoPreview ? (
              <div className="flex flex-col gap-4 sm:flex-row sm:items-center">
                <img
                  src={photoPreview}
                  alt="Patient"
                  className="h-24 w-24 rounded-lg object-cover"
                />
                <div className="flex flex-col gap-2">
                  <p className="text-sm text-slate-600">Photo captured</p>
                  <button
                    type="button"
                    onClick={() => {
                      setPhotoFile(null)
                      setPhotoPreview('')
                    }}
                    className="w-fit rounded-lg border border-rose-500/20 px-3 py-1.5 text-sm text-rose-400 transition-all hover:bg-rose-500/10"
                  >
                    Remove Photo
                  </button>
                </div>
              </div>
            ) : (
              <button
                type="button"
                onClick={() => setShowPhotoModal(true)}
                className="w-full rounded-lg border-2 border-dashed border-slate-200 px-6 py-8 transition-all hover:border-emerald-500/30 hover:bg-slate-50"
              >
                <Upload className="mx-auto mb-2 h-8 w-8 text-slate-500" />
                <p className="font-semibold text-slate-900">Capture or upload photo</p>
                <p className="mt-1 text-sm text-slate-500">Click to use webcam or upload from file</p>
              </button>
            )}
          </div>

          {/* Submit */}
          <div className="flex gap-4">
            <button
              type="button"
              onClick={() => navigate(-1)}
              className="flex items-center gap-2 rounded-xl border border-slate-200 px-6 py-3 font-semibold text-slate-900 transition-all hover:bg-slate-50"
            >
              <ArrowLeft className="h-4 w-4" />
              Cancel
            </button>
            <button
              type="submit"
              disabled={isSubmitting}
              className="flex-1 rounded-xl bg-emerald-500 px-6 py-3 font-semibold text-white transition-all hover:bg-emerald-600 disabled:opacity-50"
            >
              {isSubmitting ? 'Registering...' : 'Register Patient'}
            </button>
          </div>
        </form>
      </div>
    </PageShell>
  )
}
