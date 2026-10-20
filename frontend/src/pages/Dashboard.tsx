import { useEffect, useState } from 'react';

import { api } from '../lib/api';
import type { DashboardData } from '../types/dashboard';
import { Link } from 'react-router';

export function Dashboard() {
    const [data, setData] = useState<DashboardData | null>(null);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState('');

    useEffect(() => {
        const controller = new AbortController();

        async function loadDashboard() {
            setIsLoading(true);
            setError('');

            try {
                const response = await api.get<DashboardData>(
                    '/api/dashboard',
                    { signal: controller.signal },
                );

                setData(response.data);
            } catch (error) {
                if (controller.signal.aborted) {
                    return;
                }

                setError('Could not load dashboard.');
                console.error('Failed to load dashboard:', error);
            } finally {
                if (!controller.signal.aborted) {
                    setIsLoading(false);
                }
            }
        }

        void loadDashboard();

        return () => {
            controller.abort();
        };
    }, []);

    if (isLoading) {
        return <p>Loading dashboard...</p>;
    }

    if (error) {
        return <p role="alert">{error}</p>;
    }

    if (!data) {
        return <p>No dashboard data available.</p>;
    }

    return (
        <section>
            <h2>Dashboard</h2>

            <p>Total tickets: {data.stats.total}</p>
            <p>Open: {data.stats.open}</p>
            <p>In progress: {data.stats.in_progress}</p>
            <p>Closed: {data.stats.closed}</p>

            <h3>Recent tickets</h3>

            {data.recent_tickets.length === 0 ? (
                <p>No recent tickets found.</p>
            ) : (
                <ul>
                    {data.recent_tickets.map((ticket) => (
                        <li key={ticket.id}>
                            <Link to={`/tickets/${ticket.id}`}>
                                {ticket.title}
                            </Link>
                            <span> — {ticket.status}</span>
                            <span> — {ticket.priority}</span>
                        </li>
                    ))}
                </ul>
            )}

            <Link to="/tickets">View all tickets</Link>
        </section>
    );
}