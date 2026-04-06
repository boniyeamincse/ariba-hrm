import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { Activity, ArrowRight, Loader2, RefreshCcw, SkipForward, TicketPlus, UserRound } from 'lucide-react'
import { Link } from 'react-router-dom'
import { api } from '../../lib/api'

type QueueItem = {
  id: number
  token_no: number
  patient_id: number
  priority: number
  status: string
  queued_at: string
}

type QueueStateResponse = {
  current: QueueItem | null
  waiting: QueueItem[]
  stats: {
    waiting_count: number
    served_today: number
  }
}

type QueueEventPayload = {
  tenant_id: string | number | null
  action: string
  payload: {
    queue_id?: number
    token_no?: number
    status?: string
  }
  timestamp: string
}

export function OpdQueuePage() {
  const [state, setState] = useState<QueueStateResponse>({
    current: null,
    waiting: [],
    stats: {
      waiting_count: 0,
      served_today: 0,
    },
  })
  const [patientId, setPatientId] = useState('')
  const [priority, setPriority] = useState('0')
  const [loading, setLoading] = useState(true)
  const [actionLoading, setActionLoading] = useState<string | null>(null)
  const [error, setError] = useState<string | null>(null)
  const [liveStatus, setLiveStatus] = useState<'ws' | 'polling' | 'offline'>('offline')

  const pollRef = useRef<number | null>(null)
  const wsRef = useRef<WebSocket | null>(null)

  const loadState = useCallback(async () => {
    try {
      const response = await api.get<QueueStateResponse>('/clinical/opd/queue/state')
      setState(response.data)
      setError(null)
    } catch {
      setError('Unable to load OPD queue state.')
    } finally {
      setLoading(false)
    }
  }, [])

  const queueHealth = useMemo(() => {
    if (state.stats.waiting_count >= 20) return 'critical'
    if (state.stats.waiting_count >= 10) return 'busy'
    return 'stable'
  }, [state.stats.waiting_count])

  const addToken = async () => {
    if (!patientId.trim()) {
      setError('Patient ID is required.')
      return
    }

    setActionLoading('token')
    try {
      await api.post('/clinical/opd/queue/tokens', {
        patient_id: Number(patientId),
        priority: Number(priority),
      })
      setPatientId('')
      setPriority('0')
      await loadState()
    } catch {
      setError('Failed to generate OPD token. Check patient ID and try again.')
    } finally {
      setActionLoading(null)
    }
  }

  const callNext = async () => {
    setActionLoading('next')
    try {
      await api.post('/clinical/opd/queue/call-next')
      await loadState()
    } catch {
      setError('Failed to call next patient.')
    } finally {
      setActionLoading(null)
    }
  }

  const skipToken = async (queueId: number) => {
    setActionLoading(`skip-${queueId}`)
    try {
      await api.post(`/clinical/opd/queue/${queueId}/skip`)
      await loadState()
    } catch {
      setError('Failed to skip selected token.')
    } finally {
      setActionLoading(null)
    }
  }

  const connectLiveUpdates = useCallback(() => {
    const wsBase = import.meta.env.VITE_OPD_WS_URL as string | undefined
    const tenant = localStorage.getItem('ariba_tenant') ?? 'default'

    if (!wsBase) {
      setLiveStatus('polling')
      return
    }

    try {
      const socket = new WebSocket(`${wsBase}?channel=opd.queue.${tenant}`)
      wsRef.current = socket

      socket.onopen = () => {
        setLiveStatus('ws')
      }

      socket.onmessage = (event) => {
        try {
          const data = JSON.parse(event.data) as QueueEventPayload
          if (data.action) {
            loadState()
          }
        } catch {
          loadState()
        }
      }

      socket.onerror = () => {
        setLiveStatus('polling')
        socket.close()
      }

      socket.onclose = () => {
        setLiveStatus('polling')
      }
    } catch {
      setLiveStatus('polling')
    }
  }, [loadState])

  useEffect(() => {
    loadState()
    connectLiveUpdates()

    pollRef.current = window.setInterval(() => {
      if (liveStatus !== 'ws') {
        loadState()
      }
    }, 15000)

    return () => {
      if (pollRef.current) {
        window.clearInterval(pollRef.current)
      }

      if (wsRef.current) {
        wsRef.current.close()
      }
    }
  }, [connectLiveUpdates, liveStatus, loadState])

  return (
    <div className="mx-auto max-w-7xl space-y-6">
      <section className="rounded-3xl border border-slate-200 bg-gradient-to-r from-emerald-100 via-white to-cyan-100 p-6">
        <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div>
            <p className="mb-2 text-xs uppercase tracking-[0.2em] text-cyan-400">OPD Live Operations</p>
            <h1 className="text-3xl font-bold text-slate-900">Queue Command Board</h1>
            <p className="mt-2 text-slate-600">Real-time token monitoring and triage flow control.</p>
          </div>
          <div className="flex items-center gap-2">
            <Link
              to="/dashboard/opd/vitals"
              className="inline-flex items-center gap-2 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-500/20"
            >
              <UserRound className="h-4 w-4" />
              Open Vitals Form
            </Link>
            <Link
              to="/dashboard/opd/consultations"
              className="inline-flex items-center gap-2 rounded-2xl border border-teal-500/30 bg-teal-500/10 px-3 py-2 text-xs font-semibold text-teal-700 hover:bg-teal-500/20"
            >
              <Activity className="h-4 w-4" />
              Open SOAP Editor
            </Link>
            <div className="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs">
              <Activity className="h-4 w-4 text-cyan-700" />
              <span className="text-slate-600">Mode:</span>
              <span className="font-semibold uppercase text-slate-900">{liveStatus}</span>
            </div>
          </div>
        </div>
      </section>

      <section className="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <article className="rounded-2xl border border-slate-200 bg-white p-5">
          <p className="text-xs uppercase tracking-wider text-slate-500">Current Token</p>
          <p className="mt-2 text-4xl font-black text-slate-900">{state.current ? `#${state.current.token_no}` : '--'}</p>
          <p className="mt-2 text-sm text-slate-600">Patient ID: {state.current?.patient_id ?? 'N/A'}</p>
        </article>

        <article className="rounded-2xl border border-slate-200 bg-white p-5">
          <p className="text-xs uppercase tracking-wider text-slate-500">Waiting Patients</p>
          <p className="mt-2 text-4xl font-black text-slate-900">{state.stats.waiting_count}</p>
          <p className="mt-2 text-sm capitalize text-slate-600">Queue health: {queueHealth}</p>
        </article>

        <article className="rounded-2xl border border-slate-200 bg-white p-5">
          <p className="text-xs uppercase tracking-wider text-slate-500">Served Today</p>
          <p className="mt-2 text-4xl font-black text-slate-900">{state.stats.served_today}</p>
          <button
            onClick={loadState}
            className="mt-4 inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-50"
          >
            <RefreshCcw className="h-3.5 w-3.5" />
            Refresh
          </button>
        </article>
      </section>

      <section className="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <article className="rounded-2xl border border-slate-200 bg-white p-5 xl:col-span-2">
          <div className="mb-4 flex items-center justify-between">
            <h2 className="text-lg font-semibold text-slate-900">Waiting Queue</h2>
            <button
              onClick={callNext}
              disabled={Boolean(actionLoading) || state.waiting.length === 0}
              className="inline-flex items-center gap-2 rounded-xl bg-cyan-500 px-3 py-2 text-xs font-bold text-white disabled:cursor-not-allowed disabled:opacity-60"
            >
              {actionLoading === 'next' ? <Loader2 className="h-4 w-4 animate-spin" /> : <ArrowRight className="h-4 w-4" />}
              Call Next
            </button>
          </div>

          {loading ? (
            <div className="rounded-xl border border-slate-200 p-6 text-slate-500">Loading queue...</div>
          ) : state.waiting.length === 0 ? (
            <div className="rounded-xl border border-slate-200 p-6 text-slate-500">No waiting tokens.</div>
          ) : (
            <div className="space-y-3">
              {state.waiting.map((item) => (
                <div key={item.id} className="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-4">
                  <div className="flex items-center gap-3">
                    <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-500/15 text-cyan-700">
                      <UserRound className="h-4 w-4" />
                    </div>
                    <div>
                      <p className="font-semibold text-slate-900">Token #{item.token_no}</p>
                      <p className="text-xs text-slate-500">Patient ID: {item.patient_id} | Priority: {item.priority}</p>
                    </div>
                  </div>

                  <button
                    onClick={() => skipToken(item.id)}
                    disabled={Boolean(actionLoading)}
                    className="inline-flex items-center gap-2 rounded-lg border border-amber-400/30 bg-amber-400/10 px-3 py-1.5 text-xs font-semibold text-amber-300 disabled:cursor-not-allowed disabled:opacity-60"
                  >
                    {actionLoading === `skip-${item.id}` ? <Loader2 className="h-3.5 w-3.5 animate-spin" /> : <SkipForward className="h-3.5 w-3.5" />}
                    Skip
                  </button>
                </div>
              ))}
            </div>
          )}
        </article>

        <article className="rounded-2xl border border-slate-200 bg-white p-5">
          <h2 className="text-lg font-semibold text-slate-900">Generate Token</h2>
          <p className="mt-1 text-xs text-slate-500">Register patient into live OPD queue.</p>

          <div className="mt-4 space-y-3">
            <label className="block">
              <span className="mb-1 block text-xs text-slate-600">Patient ID</span>
              <input
                value={patientId}
                onChange={(e) => setPatientId(e.target.value)}
                className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-cyan-400/50"
                placeholder="e.g. 1024"
              />
            </label>

            <label className="block">
              <span className="mb-1 block text-xs text-slate-600">Priority (0-9)</span>
              <input
                value={priority}
                onChange={(e) => setPriority(e.target.value)}
                type="number"
                min={0}
                max={9}
                className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-cyan-400/50"
              />
            </label>

            <button
              onClick={addToken}
              disabled={actionLoading === 'token'}
              className="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 px-3 py-2.5 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-60"
            >
              {actionLoading === 'token' ? <Loader2 className="h-4 w-4 animate-spin" /> : <TicketPlus className="h-4 w-4" />}
              Generate Token
            </button>
          </div>

          {error && <p className="mt-3 rounded-lg border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-xs text-rose-300">{error}</p>}
        </article>
      </section>
    </div>
  )
}
