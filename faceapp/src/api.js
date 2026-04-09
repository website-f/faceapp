const apiBaseUrl = (import.meta.env.VITE_API_BASE_URL || '').replace(/\/$/, '')

function buildApiUrl(path) {
  return `${apiBaseUrl}${path}`
}

export async function enrollFace(payload) {
  const response = await fetch(buildApiUrl('/api/enrollments'), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify(payload),
  })

  const data = await response.json().catch(() => ({}))

  if (!response.ok || !data.ok) {
    throw new Error(data.error || data.message || 'Face enrollment failed.')
  }

  return data
}
