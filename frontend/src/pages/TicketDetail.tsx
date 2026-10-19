import axios from 'axios';
import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router';

import { api } from '../lib/api';
import type { Ticket } from '../types/ticket';
import { TicketComments } from '../components/TicketComments';

export function TicketDetail() {
    const { ticketId } = useParams<{ ticketId: string }>();

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
        <section>
            <Link to="/tickets">Back to tickets</Link>

            {isLoading ? (
                <p>Loading ticket...</p>
            ) : error ? (
                <p role="alert">{error}</p>
            ) : !ticket ? (
                <p>Ticket not found.</p>
            ) : (
                <>
                    <h2>{ticket.title}</h2>

                    <p>{ticket.description}</p>
                    <p>Status: {ticket.status}</p>
                    <p>Priority: {ticket.priority}</p>

                    <TicketComments
                        key={ticket.id}
                        ticketId={ticket.id}
                        isClosed={ticket.status === 'closed'}
                    />
                </>
            )}
        </section>
    );
}