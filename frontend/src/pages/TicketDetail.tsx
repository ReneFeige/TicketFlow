import axios from 'axios';
import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router';

import { api } from '../lib/api';
import type { Ticket } from '../types/ticket';
import { TicketComments } from '../components/TicketComments';
import { TicketEditForm } from '../components/TicketEditForm';
import styles from './TicketDetail.module.css';
import { PriorityBadge } from '../components/PriorityBadge/PriorityBadge';
import { StatusBadge } from '../components/StatusBadge/StatusBadge';

type TicketDetailProps = {
    userRole: 'customer' | 'agent' | 'admin';
};

export function TicketDetail({ userRole }: TicketDetailProps) {
    const { ticketId } = useParams<{ ticketId: string }>();
    const canEdit = userRole === 'agent' || userRole === 'admin';

    const [ticket, setTicket] = useState<Ticket | null>(null);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState('');

    useEffect(() => {
        const controller = new AbortController();

        async function loadTicket() {
            setIsLoading(true);
            setError('');
            setTicket(null);

            try {
                const response = await api.get<Ticket>(
                    `/api/tickets/${ticketId}`,
                    { signal: controller.signal },
                );

                setTicket(response.data);
            } catch (error) {
                if (controller.signal.aborted) {
                    return;
                }

                if (axios.isAxiosError(error)) {
                    switch (error.response?.status) {
                        case 403:
                            setError('You do not have permission to view this ticket.');
                            break;
                        case 404:
                            setError('Ticket not found.');
                            break;
                        default:
                            setError('Could not load ticket.');
                            console.error('Failed to load ticket:', error);
                    }
                } else {
                    setError('Could not load ticket.');
                    console.error('Failed to load ticket:', error);
                }
            } finally {
                if (!controller.signal.aborted) {
                    setIsLoading(false);
                }
            }
        }

        void loadTicket();

        return () => {
            controller.abort();
        };
    }, [ticketId]);

    return (
        <section className={styles.ticketPage}>
            <Link to="/tickets" className={styles.backLink}>
                ← Back to tickets
            </Link>

            {isLoading ? (
                <p>Loading ticket...</p>
            ) : error ? (
                <p role="alert">{error}</p>
            ) : !ticket ? (
                <p>Ticket not found.</p>
            ) : (
                <>
                    <div className={styles.pageHeader}>
                        <h2>{ticket.title}</h2>
                        <span>Ticket #{ticket.id}</span>
                    </div>

                    <div className={styles.ticketLayout}>
                        <section className={`${styles.card} ${styles.detailsCard}`}>
                            <h3>Ticket details</h3>

                            <div className={styles.metaGrid}>
                                <div className={styles.metaItem}>
                                    <span className={styles.metaLabel}>Status</span>
                                    <StatusBadge status={ticket.status} />
                                </div>

                                <div className={styles.metaItem}>
                                    <span className={styles.metaLabel}>Priority</span>
                                    <PriorityBadge priority={ticket.priority} />
                                </div>
                            </div>
                        </section>

                        <section className={`${styles.card} ${styles.descriptionCard}`}>
                            <h3>Description</h3>
                            <p className={styles.description}>{ticket.description}</p>
                        </section>

                        <section className={`${styles.card} ${styles.commentsCard}`}>
                            <TicketComments
                                key={ticket.id}
                                ticketId={ticket.id}
                                isClosed={ticket.status === 'closed'}
                            />
                        </section>

                        {canEdit && (
                            <section className={`${styles.card} ${styles.editCard}`}>
                                <TicketEditForm ticket={ticket} onUpdated={setTicket} />
                            </section>
                        )}
                    </div>
                </>
            )}
        </section>
    );
}