type ModulePlaceholderPageProps = {
  title: string
  subtitle: string
}

export function ModulePlaceholderPage({ title, subtitle }: ModulePlaceholderPageProps) {
  return (
    <div className="rounded-3xl border border-slate-200 bg-white p-8">
      <h1 className="text-2xl font-semibold text-slate-900">{title}</h1>
      <p className="mt-2 max-w-2xl text-slate-600">{subtitle}</p>
      <div className="mt-8 rounded-2xl border border-dashed border-slate-300 p-6 text-sm text-slate-500">
        This module is ready for role-aware feature implementation.
      </div>
    </div>
  )
}
