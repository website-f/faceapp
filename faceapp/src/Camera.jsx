import { useState, useRef, useCallback, useEffect } from 'react'
import './Camera.css'

const FACING_MODES = { USER: 'user', ENVIRONMENT: 'environment' }

export default function Camera({ onCapture, onClose }) {
  const videoRef = useRef(null)
  const canvasRef = useRef(null)
  const streamRef = useRef(null)

  const [facing, setFacing] = useState(FACING_MODES.USER)
  const [ready, setReady] = useState(false)
  const [countdown, setCountdown] = useState(null)
  const [flash, setFlash] = useState(false)
  const [error, setError] = useState(null)
  const [scanning, setScanning] = useState(false)

  const startCamera = useCallback(async (mode) => {
    setReady(false)
    setError(null)
    if (streamRef.current) {
      streamRef.current.getTracks().forEach(t => t.stop())
    }
    try {
      const stream = await navigator.mediaDevices.getUserMedia({
        video: {
          facingMode: mode,
          width: { ideal: 1280 },
          height: { ideal: 720 },
        },
        audio: false,
      })
      streamRef.current = stream
      if (videoRef.current) {
        videoRef.current.srcObject = stream
        videoRef.current.onloadedmetadata = () => {
          videoRef.current.play()
          setReady(true)
        }
      }
    } catch (err) {
      setError('Camera access denied. Please allow camera permissions.')
      console.error(err)
    }
  }, [])

  useEffect(() => {
    const timeoutId = setTimeout(() => {
      startCamera(FACING_MODES.USER)
    }, 0)

    return () => {
      clearTimeout(timeoutId)
      if (streamRef.current) {
        streamRef.current.getTracks().forEach(t => t.stop())
      }
    }
  }, [startCamera])

  const flipCamera = () => {
    const newMode = facing === FACING_MODES.USER ? FACING_MODES.ENVIRONMENT : FACING_MODES.USER
    setFacing(newMode)
    startCamera(newMode)
  }

  const triggerCapture = () => {
    if (!ready || countdown !== null) return
    setCountdown(3)
    let c = 3
    const interval = setInterval(() => {
      c -= 1
      setCountdown(c)
      if (c === 0) {
        clearInterval(interval)
        setCountdown(null)
        capturePhoto()
      }
    }, 1000)
  }

  const capturePhoto = () => {
    if (!videoRef.current || !canvasRef.current) return
    const video = videoRef.current
    const canvas = canvasRef.current
    canvas.width = video.videoWidth
    canvas.height = video.videoHeight
    const ctx = canvas.getContext('2d')

    // Mirror if front cam
    if (facing === FACING_MODES.USER) {
      ctx.translate(canvas.width, 0)
      ctx.scale(-1, 1)
    }
    ctx.drawImage(video, 0, 0)

    setFlash(true)
    setTimeout(() => setFlash(false), 300)

    setScanning(true)
    setTimeout(() => {
      setScanning(false)
      const dataUrl = canvas.toDataURL('image/jpeg', 0.92)
      onCapture(dataUrl)
    }, 2200)
  }

  return (
    <div className="camera-overlay animate-slideUp">
      {/* Header */}
      <div className="camera-header glass">
        <button className="cam-icon-btn" onClick={onClose} aria-label="Close camera">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
            <path d="M18 6L6 18M6 6l12 12" />
          </svg>
        </button>
        <span className="cam-title">Face Capture</span>
        <button className="cam-icon-btn" onClick={flipCamera} aria-label="Flip camera" disabled={!ready}>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
            <path d="M1 4v6h6M23 20v-6h-6" />
            <path d="M20.49 9A9 9 0 005.64 5.64L1 10M23 14l-4.64 4.36A9 9 0 013.51 15" />
          </svg>
        </button>
      </div>

      {/* Video Viewport */}
      <div className="camera-viewport">
        {error ? (
          <div className="cam-error">
            <div className="cam-error-icon">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
                <circle cx="12" cy="12" r="10" />
                <path d="M12 8v4M12 16h.01" strokeWidth="2" strokeLinecap="round" />
              </svg>
            </div>
            <p>{error}</p>
            <button className="btn-primary" onClick={() => startCamera(facing)}>Try Again</button>
          </div>
        ) : (
          <>
            <video
              ref={videoRef}
              className={`cam-video ${facing === FACING_MODES.USER ? 'mirror' : ''}`}
              muted
              playsInline
              autoPlay
            />

            {/* Flash Overlay */}
            {flash && <div className="cam-flash" />}

            {/* Face Guide Frame */}
            <div className={`face-guide ${ready ? 'visible' : ''}`}>
              <div className="guide-corner tl" />
              <div className="guide-corner tr" />
              <div className="guide-corner bl" />
              <div className="guide-corner br" />
              {scanning && <div className="scan-line" />}
            </div>

            {/* Countdown Overlay */}
            {countdown !== null && (
              <div className="cam-countdown animate-fadeIn">
                <span>{countdown}</span>
              </div>
            )}

            {/* Scanning animation */}
            {scanning && (
              <div className="cam-scanning animate-fadeIn">
                <div className="scanning-dots">
                  <span /><span /><span />
                </div>
                <p>Analyzing face…</p>
              </div>
            )}

            {/* Ready indicator */}
            {!ready && !error && (
              <div className="cam-loading">
                <div className="cam-spinner" />
                <p>Initializing camera…</p>
              </div>
            )}
          </>
        )}
        <canvas ref={canvasRef} style={{ display: 'none' }} />
      </div>

      {/* Hint */}
      <p className="cam-hint">Position your face within the frame</p>

      {/* Shutter Button */}
      <div className="camera-controls">
        <button
          id="shutter-btn"
          className={`shutter-btn ${!ready || countdown !== null || scanning ? 'disabled' : ''}`}
          onClick={triggerCapture}
          disabled={!ready || countdown !== null || scanning}
          aria-label="Take photo"
        >
          <div className="shutter-inner" />
          <div className="shutter-ring" />
        </button>
      </div>
    </div>
  )
}
