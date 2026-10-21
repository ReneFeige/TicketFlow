import { useEffect, useState, type SubmitEvent } from 'react';
import axios from 'axios';
import { api } from './lib/api';
import { TicketList } from './pages/TicketList';
import { TicketDetail } from './pages/TicketDetail';
import { NavLink, Navigate, Route, Routes } from 'react-router';
import { Dashboard } from './pages/Dashboard';
import { TicketCreate } from './pages/TicketCreate';
import styles from './App.module.css';

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
    <>
      {user ? (
        <div className={styles.app}>
          <header className={styles.header}>
            <div className={styles.headerInner}>
              <h1 className={styles.brand}>TicketFlow</h1>

              <nav
                className={styles.navigation}
                aria-label="Main navigation"
              >
                <NavLink
                  to="/"
                  end
                  className={({ isActive }) =>
                    isActive ? styles.activeLink : styles.navLink
                  }
                >
                  Dashboard
                </NavLink>

                <NavLink
                  to="/tickets"
                  className={({ isActive }) =>
                    isActive ? styles.activeLink : styles.navLink
                  }
                >
                  Tickets
                </NavLink>
              </nav>

              <div className={styles.userArea}>
                <div className={styles.userInfo}>
                  <span className={styles.userName}>{user.name}</span>
                  <span className={styles.userRole}>{user.role}</span>
                </div>

                <button
                  className={styles.logoutButton}
                  type="button"
                  onClick={handleLogout}
                  disabled={isLoading}
                >
                  {isLoading ? 'Signing out...' : 'Sign out'}
                </button>
              </div>
            </div>
          </header>

          <main className={styles.content}>
            <Routes>
              <Route path="/" element={<Dashboard />} />

              <Route
                path="/tickets"
                element={<TicketList userRole={user.role} />}
              />

              <Route
                path="/tickets/:ticketId"
                element={<TicketDetail userRole={user.role} />}
              />

              <Route
                path="/tickets/new"
                element={
                  user.role === 'customer' ? (
                    <TicketCreate />
                  ) : (
                    <Navigate to="/tickets" replace />
                  )
                }
              />
              <Route path="*" element={<p>Page not found.</p>} />
            </Routes>
          </main>
        </div>
      ) : (
        <div className={styles.loginPage}>
          <main className={styles.loginContainer}>
            <div className={styles.loginBrand}>
              <h1>TicketFlow</h1>
              <p>A simple helpdesk ticket management system</p>
            </div>

            <form onSubmit={handleLogin} className={styles.loginForm}>
              <h2>Sign in</h2>

              <div className={styles.loginField}>
                <label htmlFor="email">Email</label>
                <input
                  id="email"
                  type="email"
                  value={email}
                  onChange={(event) => setEmail(event.target.value)}
                  placeholder="you@example.com"
                  autoComplete="username"
                  required
                />
              </div>

              <div className={styles.loginField}>
                <label htmlFor="password">Password</label>
                <input
                  id="password"
                  type="password"
                  value={password}
                  onChange={(event) => setPassword(event.target.value)}
                  autoComplete="current-password"
                  required
                />
              </div>

              {error && (
                <p role="alert" className={styles.loginError}>
                  {error}
                </p>
              )}

              <button
                type="submit"
                className={styles.loginButton}
                disabled={isLoading}
              >
                {isLoading ? 'Signing in...' : 'Sign in'}
              </button>
            </form>

            <p className={styles.loginHint}>
              Demo accounts will be available here.
            </p>
          </main>
        </div>
      )}
    </>
  );
}

export default App;