import { useEffect, useState } from 'react';
import { api } from '../lib/api';
import type { Ticket } from '../types/ticket';
import { Link, useSearchParams } from 'react-router';
import styles from './TicketList.module.css';
import { PriorityBadge } from '../components/PriorityBadge/PriorityBadge';
import { StatusBadge } from '../components/StatusBadge/StatusBadge';

type TicketListProps = {
    userRole: 'customer' | 'agent' | 'admin';
};

export function TicketList({ userRole }: TicketListProps) {
    const [tickets, setTickets] = useState<Ticket[]>([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState('');
    const [hasLoaded, setHasLoaded] = useState(false);
    const [searchParams, setSearchParams] = useSearchParams();

    const sort = searchParams.get('sort') === 'priority'
        ? 'priority'
        : 'newest';

    useEffect(() => {
        const controller = new AbortController();

        async function loadTickets() {
            setIsLoading(true);
            setError('');

            try {
                const response = await api.get<Ticket[]>(
                    '/api/tickets',
                    {
                        params: { sort },
                        signal: controller.signal,
                    },
                );

                setTickets(response.data);
                setHasLoaded(true);
            } catch (error) {
                if (controller.signal.aborted) {
                    return;
                }

                console.error('Failed to load tickets:', error);
                setError('Could not load tickets.');
            } finally {
                if (!controller.signal.aborted) {
                    setIsLoading(false);
                }
            }
        }

        void loadTickets();

        return () => controller.abort();
    }, [sort]);

    return (
        <section className={styles.ticketPage}>
            <div className={styles.pageHeader}>
                <div>
                    <h2>Tickets</h2>
                    <p>View and manage support tickets</p>
                </div>

                {userRole === 'customer' && (
                    <Link to="/tickets/new" className={styles.createButton}>
                        + Create ticket
                    </Link>
                )}
            </div>

            <div className={styles.sortBar}>
                <label htmlFor="ticket-sort">
                    Sort by
                </label>

                <select
                    id="ticket-sort"
                    value={sort}
                    disabled={isLoading}
                    onChange={(event) => {
                        const selectedSort = event.target.value;

                        setSearchParams(
                            selectedSort === 'priority'
                                ? { sort: 'priority' }
                                : {},
                        );
                    }}
                >
                    <option value="newest">Newest first</option>
                    <option value="priority">Priority: High to Low</option>
                </select>
            </div>

            <div className={styles.loadingStatus} role="status">
                {isLoading && hasLoaded ? 'Updating tickets...' : ''}
            </div>

            <div className={styles.ticketCard} aria-busy={isLoading}>
                {!hasLoaded && isLoading ? (
                    <p>Loading tickets...</p>
                ) : error ? (
                    <p role="alert">{error}</p>
                ) : tickets.length === 0 ? (
                    <p className={styles.emptyState}>
                        No tickets found.
                    </p>
                ) : (
                    <ul className={styles.ticketList}>
                        {tickets.map((ticket) => (
                            <li key={ticket.id} className={styles.ticketItem}>
                                <div className={styles.ticketInfo}>
                                    <Link
                                        to={`/tickets/${ticket.id}`}
                                        className={styles.ticketTitle}
                                    >
                                        {ticket.title}
                                    </Link>

                                    <span className={styles.ticketId}>
                                        #{ticket.id}
                                    </span>
                                </div>

                                <div className={styles.ticketBadges}>
                                    <StatusBadge status={ticket.status} />
                                    <PriorityBadge priority={ticket.priority} />
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </section>
    );
}