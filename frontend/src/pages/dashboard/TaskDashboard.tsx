import { useState, useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { 
  Plus, CheckCircle2, Clock, AlertCircle, 
  Filter, MoreVertical, Calendar, User,
  LayoutGrid, List as ListIcon, Search,
  ArrowUpRight, Target, Activity
} from 'lucide-react'

type Task = {
  id: number
  title: string
  description: string
  priority: 'low' | 'medium' | 'high' | 'urgent'
  status: 'todo' | 'in_progress' | 'completed' | 'cancelled'
  due_date: string | null
  assigned_to?: { name: string }
}

export function TaskDashboard() {
  const [tasks, setTasks] = useState<Task[]>([])
  const [view, setView] = useState<'grid' | 'list'>('grid')
  const [filterStatus, setFilterStatus] = useState<string>('all')
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    // Simulate API fetch for now
    setTimeout(() => {
      setTasks([
        { id: 1, title: 'Morning Ward Rounds', description: 'Complete rounds for Ward A-102 and update patient vitals.', priority: 'high', status: 'todo', due_date: '2026-04-06', assigned_to: { name: 'Dr. Sarah' } },
        { id: 2, title: 'Pharmacy Inventory Check', description: 'Verify stock for critical antibiotics and update Meilisearch index.', priority: 'medium', status: 'in_progress', due_date: '2026-04-07' },
        { id: 3, title: 'Lab Report Validation', description: 'Validate pending results for biochemistry panel.', priority: 'urgent', status: 'todo', due_date: '2026-04-06', assigned_to: { name: 'Pathologist John' } },
        { id: 4, title: 'Patient Discharge Clearance', description: 'Review billing and discharge clearance for Room 305.', priority: 'medium', status: 'completed', due_date: '2026-04-05' },
      ])
      setIsLoading(false)
    }, 1200)
  }, [])

  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case 'urgent': return 'text-rose-500 bg-rose-500/10'
      case 'high': return 'text-orange-500 bg-orange-500/10'
      case 'medium': return 'text-emerald-500 bg-emerald-500/10'
      default: return 'text-slate-400 bg-slate-400/10'
    }
  }

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'completed': return <CheckCircle2 className="h-4 w-4 text-emerald-500" />
      case 'in_progress': return <Clock className="h-4 w-4 text-orange-500" />
      case 'cancelled': return <AlertCircle className="h-4 w-4 text-rose-500" />
      default: return <div className="h-4 w-4 rounded-full border-2 border-slate-600" />
    }
  }

  return (
    <div className="space-y-8 max-w-7xl mx-auto pb-20">
      {/* Header & Stats */}
      <div className="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
        <div>
          <h1 className="text-4xl font-extrabold tracking-tight text-white mb-2">Task Terminal</h1>
          <p className="text-slate-400">Synchronize your clinical workflows in real-time.</p>
        </div>
        
        <button className="flex items-center gap-2 rounded-2xl bg-emerald-500 px-6 py-3 font-bold text-white shadow-[0_0_20px_rgba(16,185,129,0.3)] transition-all hover:bg-emerald-400 hover:-translate-y-0.5 active:scale-95">
          <Plus className="h-5 w-5" />
          Initialize New Task
        </button>
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {[
          { label: 'Pending Missions', val: '12', icon: Target, color: 'emerald' },
          { label: 'Active Progress', val: '08', icon: Activity, color: 'orange' },
          { label: 'Missions Completed', val: '45', icon: CheckCircle2, color: 'blue' },
          { label: 'Critical Alerts', val: '03', icon: AlertCircle, color: 'rose' }
        ].map((stat, i) => (
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: i * 0.1 }}
            key={stat.label}
            className="group relative overflow-hidden rounded-3xl border border-white/5 bg-white/[0.02] p-6 backdrop-blur-xl transition-all hover:bg-white/[0.05]"
          >
            <div className={`p-2 rounded-xl w-fit mb-4 bg-${stat.color}-500/10 text-${stat.color}-500`}>
                <stat.icon className="h-6 w-6" />
            </div>
            <p className="text-sm font-medium text-slate-500">{stat.label}</p>
            <div className="flex items-end justify-between mt-1">
              <span className="text-3xl font-black text-white">{stat.val}</span>
              <div className="h-8 w-16 opacity-20 group-hover:opacity-40 transition-opacity">
                 {/* Sparkline Placeholder */}
                 <div className="flex items-end gap-1 h-full">
                    <div className="bg-emerald-500 flex-1 h-1/2" />
                    <div className="bg-emerald-500 flex-1 h-full" />
                    <div className="bg-emerald-500 flex-1 h-3/4" />
                    <div className="bg-emerald-500 flex-1 h-1/2" />
                 </div>
              </div>
            </div>
          </motion.div>
        ))}
      </div>

      {/* Control Bar */}
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-3xl border border-white/5 bg-white/[0.02] p-4 p-x-6 backdrop-blur-xl">
        <div className="flex items-center gap-6">
           <div className="relative group">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-500 group-focus-within:text-emerald-500 transition-colors" />
              <input 
                 type="text" 
                 placeholder="Search clinical tasks..."
                 className="bg-transparent border-none outline-none pl-10 text-sm text-white placeholder:text-slate-600 w-full sm:w-64"
              />
           </div>
           
           <div className="hidden lg:flex items-center gap-2 border-l border-white/10 pl-6">
              <Filter className="h-4 w-4 text-slate-500" />
              <select 
                 value={filterStatus} 
                 onChange={(e) => setFilterStatus(e.target.value)}
                 className="bg-transparent border-none outline-none text-sm text-slate-400 focus:text-white cursor-pointer"
              >
                <option value="all">All Ecosystems</option>
                <option value="todo">Pending Clinical</option>
                <option value="in_progress">Active Missions</option>
                <option value="completed">Archive</option>
              </select>
           </div>
        </div>

        <div className="flex items-center gap-2 rounded-2xl bg-white/5 p-1 border border-white/5">
           <button 
              onClick={() => setView('grid')}
              className={`p-2 rounded-xl transition-all ${view === 'grid' ? 'bg-emerald-500 text-white shadow-lg' : 'text-slate-500 hover:text-white'}`}
           >
              <LayoutGrid className="h-4 w-4" />
           </button>
           <button 
              onClick={() => setView('list')}
              className={`p-2 rounded-xl transition-all ${view === 'list' ? 'bg-emerald-500 text-white shadow-lg' : 'text-slate-500 hover:text-white'}`}
           >
              <ListIcon className="h-4 w-4" />
           </button>
        </div>
      </div>

      {/* Tasks Content */}
      <div className={view === 'grid' ? "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" : "space-y-4"}>
        <AnimatePresence mode="popLayout">
          {isLoading ? (
             Array.from({ length: 6 }).map((_, i) => (
                <div key={i} className="h-48 rounded-3xl bg-white/5 animate-pulse border border-white/5" />
             ))
          ) : (
            tasks.map((task, i) => (
              <motion.div
                layout
                initial={{ opacity: 0, scale: 0.9 }}
                animate={{ opacity: 1, scale: 1 }}
                exit={{ opacity: 0, scale: 0.9 }}
                key={task.id}
                className={`relative group flex flex-col justify-between rounded-3xl border border-white/5 bg-white/[0.03] p-6 transition-all hover:bg-white/[0.06] hover:border-emerald-500/30 ${view === 'list' ? 'flex-row items-center gap-6' : ''}`}
              >
                <div className={view === 'list' ? 'flex-1 flex items-center gap-8' : ''}>
                  <div className="flex items-start justify-between mb-4">
                    <span className={`text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-lg ${getPriorityColor(task.priority)}`}>
                       {task.priority} Priority
                    </span>
                    <button className="text-slate-600 hover:text-white transition-colors">
                       <MoreVertical className="h-4 w-4" />
                    </button>
                  </div>
                  
                  <div className="mb-6">
                    <h3 className="text-lg font-bold text-white group-hover:text-emerald-400 transition-colors mb-2">{task.title}</h3>
                    <p className="text-sm text-slate-500 line-clamp-2 leading-relaxed">{task.description}</p>
                  </div>
                </div>

                <div className={`mt-auto pt-6 border-t border-white/5 flex items-center justify-between ${view === 'list' ? 'mt-0 pt-0 border-none' : ''}`}>
                   <div className="flex items-center gap-2 text-[11px] text-slate-500">
                      <Calendar className="h-3 w-3" />
                      <span>{task.due_date || 'No Date'}</span>
                   </div>
                   
                   <div className="flex items-center -space-x-2">
                       {task.assigned_to ? (
                         <div className="h-7 w-7 rounded-full bg-slate-800 border-2 border-slate-950 flex items-center justify-center text-[10px] font-bold text-emerald-400" title={task.assigned_to.name}>
                            {task.assigned_to.name[0]}
                         </div>
                       ) : (
                         <div className="h-7 w-7 rounded-full bg-slate-950 border-2 border-white/5 flex items-center justify-center text-slate-700">
                            <User className="h-3 w-3" />
                         </div>
                       )}
                   </div>
                   
                   <div className="flex items-center gap-3">
                      <div className="h-full w-[1px] bg-white/5 mx-2" />
                      {getStatusIcon(task.status)}
                   </div>
                </div>
                
                {/* Visual Flair */}
                <div className="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                   <ArrowUpRight className="h-3 w-3 text-emerald-500" />
                </div>
              </motion.div>
            ))
          )}
        </AnimatePresence>
      </div>

      {!isLoading && tasks.length === 0 && (
         <div className="py-20 text-center">
            <div className="inline-flex h-20 w-20 items-center justify-center rounded-full bg-slate-900 mb-6 text-slate-700">
               <Target className="h-10 w-10" />
            </div>
            <h2 className="text-2xl font-bold text-white mb-2">Terminal Idle</h2>
            <p className="text-slate-500">All systems operational. No pending missions for this cycle.</p>
         </div>
      )}
    </div>
  )
}
