export function PageShell({
  title,
  subtitle,
  children,
}: {
  title: string
  subtitle: string
  children: React.ReactNode
}) {
  return (
    <section className="mx-auto w-full max-w-6xl px-5 py-16 md:py-20">
      <div className="mb-8 max-w-3xl">
        <p className="inline-flex rounded-full border border-sky-100 bg-sky-50 px-3 py-1 text-xs font-semibold tracking-wide text-sky-700">
          Ariba HMS - International Edition
        </p>
        <h1 className="text-3xl font-bold tracking-tight text-slate-900 md:text-4xl">{title}</h1>
        <p className="mt-3 text-slate-600">{subtitle}</p>
      </div>
      {children}
    </section>
  )
}