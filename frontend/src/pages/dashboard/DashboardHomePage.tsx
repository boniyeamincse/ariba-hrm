import { useAuth } from '../../context/useAuth'
import { motion } from 'framer-motion'
import { 
  Users, Activity, Calendar, Shield, 
  ArrowUpRight, TrendingUp, AlertCircle,
  Stethoscope, Bed, ClipboardList
} from 'lucide-react'

export function DashboardHomePage() {
  const { user } = useAuth()

  const getRoleBadge = (role: string) => {
    const roles: Record<string, string> = {
      'super-admin': 'bg-rose-500/10 text-rose-500 border-rose-500/20',
      'hospital-admin': 'bg-blue-500/10 text-blue-500 border-blue-500/20',
      'doctor': 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
      'nurse': 'bg-orange-500/10 text-orange-500 border-orange-500/20'
    }
    return roles[role?.toLowerCase()] || 'bg-slate-500/10 text-slate-500 border-slate-500/20'
  }

  const renderStats = (role: string) => {
    const defaultStats = [
      { label: 'Total Patients', value: '1,248', icon: Users, trend: '+12%', color: 'emerald' },
      { label: 'Active Admissions', value: '84', icon: Bed, trend: '+5%', color: 'blue' },
      { label: 'Daily Consultations', value: '42', icon: Activity, trend: '-2%', color: 'rose' },
      { label: 'Pending Reports', value: '19', icon: ClipboardList, trend: '+8%', color: 'orange' },
    ]

    // We can swap these based on role if needed
    return defaultStats
  }

  return (
    <div className="space-y-10 max-w-7xl mx-auto pb-10">
      {/* Welcome Header */}
      <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <div className="flex items-center gap-3 mb-2">
            <h1 className="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
              Hello, <span className="text-emerald-400">{user?.name}</span>
            </h1>
            <span className={`px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest border ${getRoleBadge(user?.role || 'user')}`}>
               {user?.role || 'Guest'}
            </span>
          </div>
          <p className="text-slate-400">Welcome to your clinical command center. All systems are operational.</p>
        </div>
        
        <div className="flex items-center gap-3">
           <div className="flex flex-col items-end">
              <p className="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">System Pulse</p>
              <p className="text-sm font-bold text-emerald-500">99.9% Uptime</p>
           </div>
           <div className="h-10 w-[1px] bg-white/5" />
           <div className="text-right">
              <p className="text-xs text-slate-400 font-medium">April 06, 2026</p>
              <p className="text-xs text-slate-500">12:50 PM Session</p>
           </div>
        </div>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        {renderStats(user?.role || '').map((stat, i) => (
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: i * 0.1 }}
            key={stat.label}
            className="group relative overflow-hidden rounded-3xl border border-white/5 bg-white/[0.02] p-6 transition-all hover:bg-white/[0.05] hover:border-emerald-500/20"
          >
            <div className="flex items-center justify-between mb-4">
               <div className={`p-2 rounded-xl bg-${stat.color}-500/10 text-${stat.color}-500`}>
                  <stat.icon className="h-5 w-5" />
               </div>
               <span className={`text-[10px] font-bold ${stat.trend.startsWith('+') ? 'text-emerald-400' : 'text-rose-400'}`}>
                  {stat.trend} <TrendingUp className="inline h-3 w-3 ml-0.5" />
               </span>
            </div>
            <p className="text-sm font-medium text-slate-500">{stat.label}</p>
            <p className="text-3xl font-black text-white mt-1">{stat.value}</p>
          </motion.div>
        ))}
      </div>

      {/* Main Insights Sections */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Left Column - Large View */}
        <div className="lg:col-span-2 space-y-8">
           <article className="rounded-3xl border border-white/5 bg-white/[0.02] p-8 backdrop-blur-xl">
              <div className="flex items-center justify-between mb-8">
                 <div>
                    <h2 className="text-xl font-bold text-white flex items-center gap-2">
                       <Activity className="h-5 w-5 text-emerald-400" />
                       Clinical Overview
                    </h2>
                    <p className="text-sm text-slate-500">Intelligent patient flow analysis</p>
                 </div>
                 <button className="text-xs font-bold text-emerald-500 hover:text-emerald-400 transition-colors uppercase tracking-widest">
                    View Reports
                 </button>
              </div>

              {/* Chart Placeholder */}
              <div className="h-64 flex items-end gap-3 px-4">
                 {[40, 70, 45, 90, 65, 80, 55, 75, 45, 60, 85, 40].map((h, i) => (
                    <div key={i} className="group relative flex-1">
                       <motion.div 
                          initial={{ height: 0 }}
                          animate={{ height: `${h}%` }}
                          className={`w-full rounded-t-lg bg-emerald-500/20 group-hover:bg-emerald-500 transition-all duration-500`} 
                       />
                       <div className="absolute -top-8 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 bg-slate-900 text-[10px] text-white px-2 py-1 rounded-md transition-opacity">
                          {h*10}
                       </div>
                    </div>
                 ))}
              </div>
              <div className="mt-4 flex justify-between px-4 text-[10px] text-slate-600 font-bold tracking-widest">
                 <span>08:00</span>
                 <span>12:00</span>
                 <span>16:00</span>
                 <span>20:00</span>
              </div>
           </article>

           <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <article className="rounded-3xl border border-white/5 bg-white/[0.02] p-6 hover:bg-white/[0.04] transition-all group">
                 <h3 className="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <Stethoscope className="h-5 w-5 text-emerald-500" />
                    Pending Consultations
                 </h3>
                 <div className="space-y-4">
                    {[1, 2, 3].map(i => (
                       <div key={i} className="flex items-center gap-4 group/item">
                          <div className="h-10 w-10 rounded-xl bg-slate-900 flex items-center justify-center text-xs font-bold text-slate-400">P{i}</div>
                          <div className="flex-1">
                             <p className="text-sm font-semibold text-white">Patient UHID-00{i*12}</p>
                             <p className="text-xs text-slate-500">Awaiting Vitals Update</p>
                          </div>
                          <ArrowUpRight className="h-4 w-4 text-slate-700 group-hover/item:text-emerald-500 transition-colors" />
                       </div>
                    ))}
                 </div>
              </article>

              <article className="rounded-3xl border border-white/5 bg-white/[0.02] p-6 hover:bg-white/[0.04] transition-all group">
                 <h3 className="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <Shield className="h-5 w-5 text-rose-500" />
                    Security Baseline
                 </h3>
                 <div className="p-4 rounded-2xl bg-rose-500/5 border border-rose-500/10 mb-4">
                    <div className="flex items-center gap-2 text-rose-500 mb-1">
                       <AlertCircle className="h-4 w-4" />
                       <span className="text-xs font-bold uppercase tracking-widest">Action Required</span>
                    </div>
                    <p className="text-xs text-slate-400 leading-relaxed">3 users currently have legacy credentials. Request password rotation across the clinic.</p>
                 </div>
                 <button className="w-full py-2 rounded-xl bg-white/5 text-xs font-bold text-slate-300 hover:bg-white/10 transition-all">
                    Initiate Audit
                 </button>
              </article>
           </div>
        </div>

        {/* Right Column - Sidebar Widgets */}
        <div className="space-y-8">
           <article className="rounded-3xl border border-white/5 bg-white/[0.02] p-6">
              <h3 className="text-lg font-bold text-white mb-6">Staff Connectivity</h3>
              <div className="space-y-6">
                 {[
                    { name: 'Dr. Sarah Jenkins', dept: 'Cardiology', status: 'Active' },
                    { name: 'Alice Thompson', dept: 'Nursing Ops', status: 'On Break' },
                    { name: 'Dr. Michael Chen', dept: 'Radiology', status: 'Offline' }
                 ].map((staff, i) => (
                    <div key={i} className="flex items-center gap-4">
                       <div className="relative">
                          <div className="h-10 w-10 rounded-xl bg-slate-800 flex items-center justify-center font-bold text-emerald-500">{staff.name[0]}</div>
                          <div className={`absolute -bottom-1 -right-1 h-3 w-3 rounded-full border-2 border-slate-950 ${staff.status === 'Active' ? 'bg-emerald-500' : staff.status === 'On Break' ? 'bg-orange-500' : 'bg-slate-600'}`} />
                       </div>
                       <div className="flex-1">
                          <p className="text-sm font-semibold text-white">{staff.name}</p>
                          <p className="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{staff.dept}</p>
                       </div>
                    </div>
                 ))}
              </div>
              <button className="w-full mt-6 py-3 rounded-2xl border border-white/5 text-xs font-bold text-slate-400 hover:text-white hover:bg-white/5 transition-all uppercase tracking-widest">
                 View All Personnel
              </button>
           </article>

           <article className="rounded-3xl bg-gradient-to-br from-emerald-500 to-teal-600 p-6 text-white shadow-[0_20px_40px_rgba(16,185,129,0.2)]">
              <div className="flex items-center justify-between mb-8">
                 <Calendar className="h-8 w-8 opacity-40" />
                 <span className="text-[10px] font-bold uppercase tracking-[0.2em] bg-white/20 px-2 py-0.5 rounded">Terminal Status</span>
              </div>
              <h4 className="text-xl font-bold mb-2 leading-tight">Monthly Clinical Sync Completed.</h4>
              <p className="text-xs text-white/80 leading-relaxed mb-6">Your HMS database has been synchronized across all regional nodes. UHID counters reset successfully.</p>
              <button className="w-full py-3 rounded-2xl bg-white text-emerald-600 text-xs font-black uppercase tracking-widest shadow-xl transition-all hover:scale-[1.02] active:scale-95">
                 Get Detailed Log
              </button>
           </article>
        </div>
      </div>
    </div>
  )
}