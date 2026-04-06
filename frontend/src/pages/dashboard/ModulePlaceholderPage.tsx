type ModulePlaceholderPageProps = {
  title: string
  subtitle: string
}

export function ModulePlaceholderPage({ title, subtitle }: ModulePlaceholderPageProps) {
  return (
    <div className="rounded-3xl border border-white/10 bg-white/[0.03] p-8">
      <h1 className="text-2xl font-semibold text-white">{title}</h1>
      <p className="mt-2 max-w-2xl text-slate-300">{subtitle}</p>
      <div className="mt-8 rounded-2xl border border-dashed border-white/20 p-6 text-sm text-slate-400">
        This module is ready for role-aware feature implementation.
      </div>
    </div>
  )
}
