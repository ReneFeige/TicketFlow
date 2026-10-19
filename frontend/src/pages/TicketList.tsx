import { useEffect, useState } from 'react';
import { api } from '../lib/api';
import type { Ticket } from '../types/ticket';
import { Link } from 'react-router';

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

    if (tickets.length === 0) {
        return <p>No tickets found.</p>;
    }

    return (
        <section>
            <h2>Tickets</h2>

            <ul>
                {tickets.map((ticket) => (
                    <li key={ticket.id}>
                        <Link to={`/tickets/${ticket.id}`}>
                            {ticket.title}
                        </Link>
                        <span> — {ticket.status}</span>
                        <span> — {ticket.priority}</span>
                    </li>
                ))}
            </ul>

            {userRole === 'customer' && (
                <Link to="/tickets/new">Create ticket</Link>
            )}
        </section>
    );
}