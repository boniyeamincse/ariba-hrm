import { useRef, useEffect, useState } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { X, Camera, Upload as UploadIcon } from 'lucide-react'

type PhotoUploadModalProps = {
  isOpen: boolean
  onClose: () => void
  onPhotoCapture: (file: File) => void
}

export function PhotoUploadModal({ isOpen, onClose, onPhotoCapture }: PhotoUploadModalProps) {
  const videoRef = useRef<HTMLVideoElement>(null)
  const canvasRef = useRef<HTMLCanvasElement>(null)
  const fileInputRef = useRef<HTMLInputElement>(null)
  const [hasCamera, setHasCamera] = useState(false)
  const [cameraActive, setCameraActive] = useState(false)

  useEffect(() => {
    if (!isOpen) return

    const checkCamera = async () => {
      try {
        const devices = await navigator.mediaDevices.enumerateDevices()
        const hasVideoInput = devices.some(device => device.kind === 'videoinput')
        setHasCamera(hasVideoInput)
      } catch {
        setHasCamera(false)
      }
    }

    checkCamera()
  }, [isOpen])

  const startCamera = async () => {
    try {
      const stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'user' },
      })
      if (videoRef.current) {
        videoRef.current.srcObject = stream
        setCameraActive(true)
      }
    } catch (error) {
      console.error('Camera access denied:', error)
    }
  }

  const capturePhoto = () => {
    const video = videoRef.current
    const canvas = canvasRef.current

    if (!video || !canvas) return

    const ctx = canvas.getContext('2d')
    if (!ctx) return

    canvas.width = video.videoWidth
    canvas.height = video.videoHeight
    ctx.drawImage(video, 0, 0)

    canvas.toBlob((blob) => {
      if (blob) {
        const file = new File([blob], 'patient-photo.jpg', { type: 'image/jpeg' })
        onPhotoCapture(file)
        stopCamera()
      }
    }, 'image/jpeg', 0.95)
  }

  const stopCamera = () => {
    if (videoRef.current?.srcObject) {
      const stream = videoRef.current.srcObject as MediaStream
      stream.getTracks().forEach(track => track.stop())
      setCameraActive(false)
    }
  }

  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (file) {
      onPhotoCapture(file)
    }
  }

  return (
    <AnimatePresence>
      {isOpen && (
        <>
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            onClick={onClose}
            className="fixed inset-0 z-40 bg-black/80"
          />
          <motion.div
            initial={{ opacity: 0, scale: 0.9 }}
            animate={{ opacity: 1, scale: 1 }}
            exit={{ opacity: 0, scale: 0.9 }}
            className="fixed inset-0 z-50 flex items-center justify-center p-4"
          >
            <div className="relative w-full max-w-md rounded-3xl border border-white/10 bg-slate-950 p-6">
              <button
                onClick={onClose}
                className="absolute right-4 top-4 rounded-lg p-1 hover:bg-white/10"
              >
                <X className="h-5 w-5 text-slate-400" />
              </button>

              <h3 className="mb-6 text-xl font-bold text-white">Upload Patient Photo</h3>

              {cameraActive ? (
                <div className="space-y-4">
                  <video
                    ref={videoRef}
                    autoPlay
                    playsInline
                    className="w-full rounded-xl bg-black"
                  />
                  <div className="flex gap-3">
                    <button
                      onClick={capturePhoto}
                      className="flex-1 rounded-lg bg-emerald-500 px-4 py-2 font-semibold text-white hover:bg-emerald-600"
                    >
                      Capture Photo
                    </button>
                    <button
                      onClick={stopCamera}
                      className="flex-1 rounded-lg border border-white/10 px-4 py-2 font-semibold text-white hover:bg-white/5"
                    >
                      Cancel
                    </button>
                  </div>
                </div>
              ) : (
                <div className="space-y-4">
                  {hasCamera && (
                    <button
                      onClick={startCamera}
                      className="w-full flex items-center justify-center gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/5 px-4 py-3 font-semibold text-emerald-400 transition-all hover:border-emerald-500/40 hover:bg-emerald-500/10"
                    >
                      <Camera className="h-5 w-5" />
                      Use Webcam
                    </button>
                  )}

                  <button
                    onClick={() => fileInputRef.current?.click()}
                    className="w-full flex items-center justify-center gap-3 rounded-xl border border-blue-500/20 bg-blue-500/5 px-4 py-3 font-semibold text-blue-400 transition-all hover:border-blue-500/40 hover:bg-blue-500/10"
                  >
                    <UploadIcon className="h-5 w-5" />
                    Upload from File
                  </button>

                  <input
                    ref={fileInputRef}
                    type="file"
                    accept="image/*"
                    onChange={handleFileUpload}
                    className="hidden"
                  />

                  {!hasCamera && (
                    <p className="text-center text-sm text-slate-400">
                      Webcam not available. Please upload a photo from file.
                    </p>
                  )}
                </div>
              )}

              <canvas ref={canvasRef} className="hidden" />
            </div>
          </motion.div>
        </>
      )}
    </AnimatePresence>
  )
}
