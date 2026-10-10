import { useEffect, useState } from 'react'

function App() {
  const [message, setMessage] = useState<string>('Lade API-Antwort...')

  useEffect(() => {
    fetch('/api/health')
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP-Fehler: ${response.status}`)
        }

        return response.json()
      })
      .then(data => setMessage(data.message))
      .catch(() => setMessage('API nicht erreichbar'))
  }, [])

  return (
    <main>
      <h1>TicketFlow</h1>
      <p>{message}</p>
    </main>
  )
}

export default App