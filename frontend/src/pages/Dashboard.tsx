import { useEffect, useState } from 'react';
import { api } from '../lib/api';
import type { DashboardData } from '../types/dashboard';
import { Link } from 'react-router';
import styles from './Dashboard.module.css';
import { PriorityBadge } from '../components/PriorityBadge/PriorityBadge';
import { StatusBadge } from '../components/StatusBadge/StatusBadge';

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
        <section className={styles.dashboard}>
            <div className={styles.pageHeader}>
                <h2>Dashboard</h2>
                <p>Overview of your support tickets</p>
            </div>

            <div className={styles.statsGrid}>
                <div className={`${styles.statCard} ${styles.total}`}>
                    <span className={styles.statLabel}>Total tickets</span>
                    <strong className={styles.statValue}>{data.stats.total}</strong>
                </div>

                <div className={`${styles.statCard} ${styles.open}`}>
                    <span className={styles.statLabel}>Open</span>
                    <strong className={styles.statValue}>{data.stats.open}</strong>
                </div>

                <div className={`${styles.statCard} ${styles.inProgress}`}>
                    <span className={styles.statLabel}>In progress</span>
                    <strong className={styles.statValue}>{data.stats.in_progress}</strong>
                </div>

                <div className={`${styles.statCard} ${styles.closed}`}>
                    <span className={styles.statLabel}>Closed</span>
                    <strong className={styles.statValue}>{data.stats.closed}</strong>
                </div>
            </div>


            <section className={styles.recentTickets}>
                <div className={styles.sectionHeader}>
                    <h3>Recent tickets</h3>
                    <Link to="/tickets" className={styles.viewAllLink}>
                        View all tickets →
                    </Link>
                </div>

                {data.recent_tickets.length === 0 ? (
                    <p className={styles.emptyState}>
                        No recent tickets found.
                    </p>
                ) : (
                    <ul className={styles.ticketList}>
                        {data.recent_tickets.map((ticket) => (
                            <li key={ticket.id} className={styles.ticketItem}>
                                <Link
                                    to={`/tickets/${ticket.id}`}
                                    className={styles.ticketTitle}
                                >
                                    {ticket.title}
                                </Link>

                                <div className={styles.ticketMeta}>
                                    <StatusBadge status={ticket.status} />
                                    <PriorityBadge priority={ticket.priority} />
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </section>
        </section>
    );
}