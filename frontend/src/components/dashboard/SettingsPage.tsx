import { useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import {
  User,
  Settings,
  Lock,
  Bell,
  PlugZap,
  Zap,
  Save,
  Copy,
  Eye,
  EyeOff,
} from 'lucide-react'
import { useAuth } from '../../context/useAuth'

type TabType = 'profile' | 'general' | 'security' | 'notifications' | 'integrations' | 'feature-toggles'

export function SettingsPage() {
  const { user } = useAuth()
  const [searchParams, setSearchParams] = useSearchParams()
  const tabParam = searchParams.get('tab') || 'profile'
  const [activeTab, setActiveTab] = useState<TabType>(tabParam as TabType || 'profile')
  const [isSaving, setIsSaving] = useState(false)
  const [showApiKey, setShowApiKey] = useState(false)

  // Profile form state
  const [profileData, setProfileData] = useState({
    name: user?.name || '',
    email: user?.email || '',
    phone: '+1 (555) 123-4567',
    timezone: 'UTC-5 (Eastern Time)',
    language: 'English',
  })

  // General settings form state
  const [generalData, setGeneralData] = useState({
    siteName: 'MedCore Hospital',
    siteUrl: 'https://medcore.example.com',
    maintenanceMode: false,
  })

  // Security settings state
  const [securityData, setSecurityData] = useState({
    twoFactorEnabled: false,
    lastPasswordChange: '2026-03-15',
    sessions: 1,
  })

  const handleTabChange = (tab: TabType) => {
    setActiveTab(tab)
    setSearchParams({ tab })
  }

  const handleSave = async () => {
    setIsSaving(true)
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    setIsSaving(false)
  }

  const tabs: { id: TabType; label: string; icon: React.ReactNode }[] = [
    { id: 'profile', label: 'Profile', icon: <User size={18} /> },
    { id: 'general', label: 'General', icon: <Settings size={18} /> },
    { id: 'security', label: 'Security', icon: <Lock size={18} /> },
    { id: 'notifications', label: 'Notifications', icon: <Bell size={18} /> },
    { id: 'integrations', label: 'Integrations', icon: <PlugZap size={18} /> },
    { id: 'feature-toggles', label: 'Feature Toggles', icon: <Zap size={18} /> },
  ]

  return (
    <div className="space-y-6">
      {/* Header */}
      <section className="rounded-3xl border border-slate-200 bg-gradient-to-r from-emerald-100 via-white to-cyan-100 p-6">
        <h1 className="text-3xl font-bold text-slate-900">Settings</h1>
        <p className="mt-2 text-slate-600">Manage your account preferences, security, and application settings.</p>
      </section>

      {/* Tabs Navigation */}
      <div className="rounded-2xl border border-slate-200 bg-white p-1">
        <div className="flex flex-wrap gap-1">
          {tabs.map(tab => (
            <button
              key={tab.id}
              onClick={() => handleTabChange(tab.id)}
              className={`flex items-center gap-2 rounded-xl px-4 py-2.5 font-medium transition-all ${
                activeTab === tab.id
                  ? 'border-b-2 border-emerald-500 bg-emerald-50 text-emerald-900'
                  : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'
              }`}
            >
              {tab.icon}
              <span>{tab.label}</span>
            </button>
          ))}
        </div>
      </div>

      {/* Tab Content */}
      <AnimatePresence mode="wait">
        {/* Profile Tab */}
        {activeTab === 'profile' && (
          <motion.div
            key="profile"
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -10 }}
            transition={{ duration: 0.2 }}
            className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6"
          >
            <div>
              <h2 className="text-xl font-bold text-slate-900">Profile Information</h2>
              <p className="mt-1 text-sm text-slate-500">Update your personal details</p>
            </div>

            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-slate-700">Full Name</label>
                <input
                  type="text"
                  value={profileData.name}
                  onChange={e => setProfileData({ ...profileData, name: e.target.value })}
                  className="mt-1.5 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                  placeholder="Enter your name"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-slate-700">Email Address</label>
                <input
                  type="email"
                  value={profileData.email}
                  onChange={e => setProfileData({ ...profileData, email: e.target.value })}
                  className="mt-1.5 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                  placeholder="Enter your email"
                />
              </div>

              <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                  <label className="block text-sm font-medium text-slate-700">Phone Number</label>
                  <input
                    type="tel"
                    value={profileData.phone}
                    onChange={e => setProfileData({ ...profileData, phone: e.target.value })}
                    className="mt-1.5 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-slate-700">Timezone</label>
                  <select
                    value={profileData.timezone}
                    onChange={e => setProfileData({ ...profileData, timezone: e.target.value })}
                    className="mt-1.5 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                  >
                    <option>UTC-8 (Pacific Time)</option>
                    <option>UTC-5 (Eastern Time)</option>
                    <option>UTC+0 (GMT)</option>
                    <option>UTC+1 (Central European Time)</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-slate-700">Language</label>
                <select
                  value={profileData.language}
                  onChange={e => setProfileData({ ...profileData, language: e.target.value })}
                  className="mt-1.5 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                >
                  <option>English</option>
                  <option>Spanish</option>
                  <option>French</option>
                  <option>German</option>
                </select>
              </div>
            </div>

            <div className="flex justify-end gap-3 border-t border-slate-100 pt-4">
              <button className="rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 font-medium text-slate-600 transition-colors hover:bg-slate-100">
                Cancel
              </button>
              <button
                onClick={handleSave}
                className="flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 font-medium text-white transition-colors hover:bg-emerald-700 disabled:opacity-50"
                disabled={isSaving}
              >
                <Save size={16} />
                {isSaving ? 'Saving...' : 'Save Changes'}
              </button>
            </div>
          </motion.div>
        )}

        {/* General Settings Tab */}
        {activeTab === 'general' && (
          <motion.div
            key="general"
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -10 }}
            transition={{ duration: 0.2 }}
            className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6"
          >
            <div>
              <h2 className="text-xl font-bold text-slate-900">General Settings</h2>
              <p className="mt-1 text-sm text-slate-500">Configure site-wide settings</p>
            </div>

            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-slate-700">Site Name</label>
                <input
                  type="text"
                  value={generalData.siteName}
                  onChange={e => setGeneralData({ ...generalData, siteName: e.target.value })}
                  className="mt-1.5 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-slate-700">Site URL</label>
                <input
                  type="url"
                  value={generalData.siteUrl}
                  onChange={e => setGeneralData({ ...generalData, siteUrl: e.target.value })}
                  className="mt-1.5 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-slate-900 outline-none transition-colors focus:border-emerald-400/50 focus:ring-2 focus:ring-emerald-100"
                />
              </div>

              <div className="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4">
                <input
                  type="checkbox"
                  id="maintenance"
                  checked={generalData.maintenanceMode}
                  onChange={e => setGeneralData({ ...generalData, maintenanceMode: e.target.checked })}
                  className="h-4 w-4 cursor-pointer rounded border-slate-300 text-emerald-600"
                />
                <label htmlFor="maintenance" className="flex cursor-pointer flex-col">
                  <span className="font-medium text-slate-900">Maintenance Mode</span>
                  <span className="text-sm text-slate-500">Take the site offline for maintenance</span>
                </label>
              </div>
            </div>

            <div className="flex justify-end gap-3 border-t border-slate-100 pt-4">
              <button className="rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 font-medium text-slate-600 transition-colors hover:bg-slate-100">
                Cancel
              </button>
              <button
                onClick={handleSave}
                className="flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 font-medium text-white transition-colors hover:bg-emerald-700"
              >
                <Save size={16} />
                Save Changes
              </button>
            </div>
          </motion.div>
        )}

        {/* Security Tab */}
        {activeTab === 'security' && (
          <motion.div
            key="security"
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -10 }}
            transition={{ duration: 0.2 }}
            className="space-y-4"
          >
            <div className="rounded-2xl border border-slate-200 bg-white p-6">
              <div>
                <h2 className="text-xl font-bold text-slate-900">Security Settings</h2>
                <p className="mt-1 text-sm text-slate-500">Manage password and authentication</p>
              </div>

              <div className="mt-6 space-y-4">
                <div className="rounded-lg border border-slate-200 bg-slate-50 p-4">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="font-medium text-slate-900">Two-Factor Authentication</p>
                      <p className="text-sm text-slate-500">Add an extra layer of security</p>
                    </div>
                    <input
                      type="checkbox"
                      checked={securityData.twoFactorEnabled}
                      onChange={e => setSecurityData({ ...securityData, twoFactorEnabled: e.target.checked })}
                      className="h-4 w-4 cursor-pointer rounded border-slate-300 text-emerald-600"
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-sm font-medium text-slate-700">Last Password Change</label>
                  <input
                    type="date"
                    value={securityData.lastPasswordChange}
                    disabled
                    className="mt-1.5 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-500 outline-none"
                  />
                </div>

                <button className="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 font-medium text-slate-900 transition-colors hover:bg-slate-50">
                  Change Password
                </button>

                <div className="border-t border-slate-100 pt-4">
                  <p className="mb-3 font-medium text-slate-900">Active Sessions</p>
                  <div className="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <p className="text-sm text-slate-600">You have {securityData.sessions} active session(s)</p>
                    <button className="mt-3 text-sm text-red-600 font-medium hover:text-red-700">
                      Sign Out All Other Sessions
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </motion.div>
        )}

        {/* Notifications Tab */}
        {activeTab === 'notifications' && (
          <motion.div
            key="notifications"
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -10 }}
            transition={{ duration: 0.2 }}
            className="rounded-2xl border border-slate-200 bg-white p-6"
          >
            <div>
              <h2 className="text-xl font-bold text-slate-900">Notification Preferences</h2>
              <p className="mt-1 text-sm text-slate-500">Choose how you receive notifications</p>
            </div>

            <div className="mt-6 space-y-3">
              {[
                { label: 'Email Notifications', desc: 'Receive updates via email' },
                { label: 'Push Notifications', desc: 'Get notifications on your devices' },
                { label: 'SMS Alerts', desc: 'Critical alerts via SMS' },
              ].map((item, idx) => (
                <div key={idx} className="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4">
                  <input
                    type="checkbox"
                    defaultChecked
                    className="h-4 w-4 cursor-pointer rounded border-slate-300 text-emerald-600"
                  />
                  <label className="flex cursor-pointer flex-col flex-1">
                    <span className="font-medium text-slate-900">{item.label}</span>
                    <span className="text-sm text-slate-500">{item.desc}</span>
                  </label>
                </div>
              ))}
            </div>

            <div className="flex justify-end gap-3 border-t border-slate-100 pt-4 mt-6">
              <button className="rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 font-medium text-slate-600 transition-colors hover:bg-slate-100">
                Cancel
              </button>
              <button className="flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 font-medium text-white transition-colors hover:bg-emerald-700">
                <Save size={16} />
                Save Preferences
              </button>
            </div>
          </motion.div>
        )}

        {/* Integrations Tab */}
        {activeTab === 'integrations' && (
          <motion.div
            key="integrations"
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -10 }}
            transition={{ duration: 0.2 }}
            className="rounded-2xl border border-slate-200 bg-white p-6"
          >
            <div>
              <h2 className="text-xl font-bold text-slate-900">API & Integrations</h2>
              <p className="mt-1 text-sm text-slate-500">Manage third-party integrations and API access</p>
            </div>

            <div className="mt-6 space-y-4">
              <div className="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <p className="font-medium text-slate-900">API Key</p>
                <div className="mt-3 flex items-center gap-2">
                  <input
                    type={showApiKey ? 'text' : 'password'}
                    value="sk_live_1234567890abcdef"
                    disabled
                    className="flex-1 rounded-lg border border-slate-200 bg-white px-3 py-2 font-mono text-sm text-slate-900 outline-none"
                  />
                  <button
                    onClick={() => setShowApiKey(!showApiKey)}
                    className="p-2 hover:bg-slate-200 rounded"
                  >
                    {showApiKey ? <EyeOff size={18} /> : <Eye size={18} />}
                  </button>
                  <button className="p-2 hover:bg-slate-200 rounded">
                    <Copy size={18} />
                  </button>
                </div>
              </div>

              <div className="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <p className="font-medium text-slate-900 mb-3">Connected Applications</p>
                <div className="space-y-2">
                  <p className="text-sm text-slate-500">No integrations connected yet</p>
                  <button className="text-sm font-medium text-emerald-600 hover:text-emerald-700">
                    Browse Available Integrations →
                  </button>
                </div>
              </div>
            </div>
          </motion.div>
        )}

        {/* Feature Toggles Tab */}
        {activeTab === 'feature-toggles' && (
          <motion.div
            key="feature-toggles"
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -10 }}
            transition={{ duration: 0.2 }}
            className="rounded-2xl border border-slate-200 bg-white p-6"
          >
            <div>
              <h2 className="text-xl font-bold text-slate-900">Feature Toggles</h2>
              <p className="mt-1 text-sm text-slate-500">Enable or disable experimental features</p>
            </div>

            <div className="mt-6 space-y-3">
              {[
                { label: 'Advanced Analytics', desc: 'Access advanced reporting features', enabled: true },
                { label: 'Dark Mode', desc: 'Use dark theme across the application', enabled: false },
                { label: 'AI Assistant', desc: 'Enable AI-powered suggestions', enabled: true },
                { label: 'Custom Reports', desc: 'Create and save custom reports', enabled: false },
              ].map((item, idx) => (
                <div key={idx} className="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4">
                  <input
                    type="checkbox"
                    defaultChecked={item.enabled}
                    className="h-4 w-4 cursor-pointer rounded border-slate-300 text-emerald-600"
                  />
                  <label className="flex cursor-pointer flex-col flex-1">
                    <span className="font-medium text-slate-900">{item.label}</span>
                    <span className="text-sm text-slate-500">{item.desc}</span>
                  </label>
                  <span className={`text-xs font-semibold px-2 py-1 rounded ${
                    item.enabled 
                      ? 'bg-emerald-100 text-emerald-700' 
                      : 'bg-slate-200 text-slate-600'
                  }`}>
                    {item.enabled ? 'Active' : 'Inactive'}
                  </span>
                </div>
              ))}
            </div>

            <div className="flex justify-end gap-3 border-t border-slate-100 pt-4 mt-6">
              <button className="rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 font-medium text-slate-600 transition-colors hover:bg-slate-100">
                Cancel
              </button>
              <button className="flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 font-medium text-white transition-colors hover:bg-emerald-700">
                <Save size={16} />
                Save Features
              </button>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  )
}