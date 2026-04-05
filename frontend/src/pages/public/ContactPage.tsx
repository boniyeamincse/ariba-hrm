import { useForm } from 'react-hook-form'
import { PageShell } from '../../components/ui/PageShell'

type ContactInput = {
  name: string
  email: string
  message: string
}

export function ContactPage() {
  const { register, handleSubmit, reset } = useForm<ContactInput>()

  const onSubmit = (values: ContactInput) => {
    console.log('contact form', values)
    reset()
  }

  return (
    <PageShell title="Contact Us" subtitle="Tell us about your HR process and we will help you set up the right workflow.">
      <form onSubmit={handleSubmit(onSubmit)} className="max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div className="grid gap-4">
          <input {...register('name', { required: true })} placeholder="Your name" className="rounded-lg border border-slate-300 px-3 py-2" />
          <input {...register('email', { required: true })} placeholder="Work email" className="rounded-lg border border-slate-300 px-3 py-2" />
          <textarea {...register('message', { required: true })} placeholder="How can we help?" rows={5} className="rounded-lg border border-slate-300 px-3 py-2" />
          <button type="submit" className="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-500">
            Send Message
          </button>
        </div>
      </form>
    </PageShell>
  )
}