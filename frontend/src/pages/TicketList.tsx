import { useEffect, useState } from 'react';
import { api } from '../lib/api';
import type { Ticket } from '../types/ticket';
import { Link } from 'react-router';
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


    useEffect(() => {
        async function loadTickets() {
            try {
                const response = await api.get<Ticket[]>('/api/tickets');

                setTickets(response.data);
            } catch (error) {
                console.error('Failed to load tickets:', error);
                setError('Could not load tickets.');
            } finally {
                setIsLoading(false);
            }
        }

        void loadTickets();
    }, []);

    if (isLoading) {
        return <p>Loading tickets...</p>;
    }

    if (error) {
        return <p role="alert">{error}</p>;
    }

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

            <div className={styles.ticketCard}>
                {tickets.length === 0 ? (
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