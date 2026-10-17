import { useEffect, useState, type SubmitEvent } from 'react';
import axios from 'axios';
import { api } from './lib/api';
import { TicketList } from './components/TicketList';

type User = {
  id: number;
  name: string;
  email: string;
  role: 'customer' | 'agent' | 'admin';
};

function App() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [user, setUser] = useState<User | null>(null);
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [isCheckingSession, setIsCheckingSession] = useState(true);

  useEffect(() => {
    async function checkSession() {
      try {
        const response = await api.get<User>('/api/user');

        setUser(response.data);
        setPassword('');
      } catch (error) {
        if (!axios.isAxiosError(error) || error.response?.status !== 401) {
          console.error('Failed to check session:', error);
        }
      } finally {
        setIsCheckingSession(false);
      }
    }

    void checkSession();
  }, []);

  async function handleLogin(event: SubmitEvent<HTMLFormElement>) {
    event.preventDefault();

    setError('');
    setIsLoading(true);

    try {
      await api.get('/sanctum/csrf-cookie');

      await api.post('/login', {
        email,
        password,
      });

      const response = await api.get<User>('/api/user');

      setUser(response.data);
    } catch (error) {
      if (axios.isAxiosError(error) && error.response?.status === 422) {
        setError('Invalid email or password.');
      } else {
        setError('An error occurred during login.');
        console.error(error);
      }
    } finally {
      setIsLoading(false);
    }
  }

  async function handleLogout() {
    setError('');
    setIsLoading(true);

    try {
      await api.post('/logout');

      setUser(null);
      setEmail('');
      setPassword('');
    } catch (error) {
      setError('An error occurred during logout.');
      console.error(error);
    } finally {
      setIsLoading(false);
    }
  }

  if (isCheckingSession) {
    return <p>Checking session...</p>;
  }

  return (
    <main>
      <h1>TicketFlow</h1>

      {error && <p role="alert">{error}</p>}

      {user ? (
        <section>
          <h2>Welcome, {user.name}!</h2>
          <p>Role: {user.role}</p>
          <p>Email: {user.email}</p>

          <button
            type="button"
            onClick={handleLogout}
            disabled={isLoading}
          >
            {isLoading ? 'Signing out...' : 'Sign out'}
          </button>

          <TicketList />
        </section>
      ) : (
        <form onSubmit={handleLogin}>
          <h2>Sign in</h2>

          <div>
            <label htmlFor="email">Email</label>
            <input
              id="email"
              type="email"
              value={email}
              onChange={(event) => setEmail(event.target.value)}
              required
            />
          </div>

          <div>
            <label htmlFor="password">Password</label>
            <input
              id="password"
              type="password"
              value={password}
              onChange={(event) => setPassword(event.target.value)}
              required
            />
          </div>

          <button type="submit" disabled={isLoading}>
            {isLoading ? 'Signing in...' : 'Sign in'}
          </button>
        </form>
      )}
    </main>
  );
}

export default App;