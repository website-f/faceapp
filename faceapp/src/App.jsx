import { useState, useCallback } from 'react'
import Dashboard from './Dashboard'
import Camera from './Camera'
import Preview from './Preview'
import { enrollFace } from './api'
import './App.css'

// ─── Mock user data ───────────────────────────────────────────────────────────
const INITIAL_USER = {
  name: 'Alexandra Chen',
  role: 'Senior Engineer',
  department: 'Platform Infrastructure',
  employeeId: 'EMP4829',
  joined: 'Mar 2022',
  accessLevel: 'Level 3',
  status: 'active',
  faceId: null,
  facePhoto: null,
  enrolledAt: null,
  activity: [
    { label: 'Profile updated', time: 'Today, 09:14 AM', type: 'info', tag: 'Profile' },
    { label: 'Access granted — Server Room B', time: 'Yesterday, 06:52 PM', type: 'success', tag: 'Access' },
    { label: 'Login from new device', time: 'Apr 6, 11:30 AM', type: 'warning', tag: 'Security' },
    { label: 'Password changed', time: 'Apr 5, 02:17 PM', type: 'info', tag: 'Security' },
  ],
}

// ─── App States ───────────────────────────────────────────────────────────────
const VIEW = { DASHBOARD: 'dashboard', CAMERA: 'camera', PREVIEW: 'preview' }

export default function App() {
  const [user, setUser]         = useState(INITIAL_USER)
  const [view, setView]         = useState(VIEW.DASHBOARD)
  const [capturedPhoto, setCapturedPhoto] = useState(null)
  const [saving, setSaving]     = useState(false)
  const [toast, setToast]       = useState(null)

  const showToast = useCallback((msg, type = 'success') => {
    setToast({ msg, type })
    setTimeout(() => setToast(null), 3500)
  }, [])

  // Camera captured a frame
  const handleCapture = useCallback((dataUrl) => {
    setCapturedPhoto(dataUrl)
    setView(VIEW.PREVIEW)
  }, [])

  // Save / enroll the face
  const handleSave = useCallback(async () => {
    if (!capturedPhoto) return

    setSaving(true)

    try {
      const result = await enrollFace({
        employee_id: user.employeeId,
        name: user.name,
        photo_data_url: capturedPhoto,
      })

      const enrolledAt = result.enrollment.enrolled_at
        ? new Date(result.enrollment.enrolled_at)
        : new Date()

      const formattedDate = enrolledAt.toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric',
      })

      setUser(prev => ({
        ...prev,
        faceId: result.enrollment.public_id,
        facePhoto: capturedPhoto,
        enrolledAt: formattedDate,
        status: 'active',
        activity: [
          {
            label: 'Face enrolled successfully',
            time: `Today, ${enrolledAt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}`,
            type: 'success',
            tag: 'FaceID',
          },
          ...prev.activity,
        ],
      }))

      setCapturedPhoto(null)
      setView(VIEW.DASHBOARD)
      showToast('Face enrolled successfully!')
    } catch (error) {
      console.error(error)
      showToast(error.message || 'Face enrollment failed.', 'error')
    } finally {
      setSaving(false)
    }
  }, [capturedPhoto, showToast, user.employeeId, user.name])

  const handleRetake = useCallback(() => {
    setCapturedPhoto(null)
    setView(VIEW.CAMERA)
  }, [])

  const handleCloseCamera = useCallback(() => {
    setCapturedPhoto(null)
    setView(VIEW.DASHBOARD)
  }, [])

  return (
    <div className="app-root">
      <Dashboard
        user={user}
        onOpenCamera={() => setView(VIEW.CAMERA)}
      />

      {view === VIEW.CAMERA && (
        <Camera
          onCapture={handleCapture}
          onClose={handleCloseCamera}
        />
      )}

      {view === VIEW.PREVIEW && capturedPhoto && (
        <Preview
          photo={capturedPhoto}
          onSave={handleSave}
          onRetake={handleRetake}
          saving={saving}
        />
      )}

      {/* Toast */}
      {toast && (
        <div className={`toast toast-${toast.type} animate-fadeUp`}>
          <div className="toast-icon">
            {toast.type === 'success' ? (
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                <polyline points="20 6 9 17 4 12" />
              </svg>
            ) : (
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
              </svg>
            )}
          </div>
          <span>{toast.msg}</span>
        </div>
      )}
    </div>
  )
}
