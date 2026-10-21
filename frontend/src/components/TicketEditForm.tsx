import { useState, type SubmitEvent } from 'react';
import { api } from '../lib/api';

import type { Ticket } from '../types/ticket';
import styles from './TicketEditForm.module.css';

type TicketEditFormProps = {
    ticket: Ticket;
    onUpdated: (ticket: Ticket) => void;
};

export function TicketEditForm({
    ticket,
    onUpdated,
}: TicketEditFormProps) {
    const [status, setStatus] = useState<Ticket['status']>(ticket.status);
    const [priority, setPriority] = useState<Ticket['priority']>(ticket.priority);

    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submitError, setSubmitError] = useState('');
    const hasChanges = status !== ticket.status || priority !== ticket.priority;

    async function handleSubmit(event: SubmitEvent<HTMLFormElement>) {
        event.preventDefault();

        if (isSubmitting) {
            return;
        }

        setIsSubmitting(true);
        setSubmitError('');

        try {
            const response = await api.patch<Ticket>(
                `/api/tickets/${ticket.id}`,
                {
                    status,
                    priority,
                },
            );

            onUpdated(response.data);
        } catch (error) {
            console.error('Failed to update ticket:', error);
            setSubmitError('Could not update ticket.');
        } finally {
            setIsSubmitting(false);
        }
    }

    return (
        <form onSubmit={handleSubmit} className={styles.form}>
            <h3>Edit ticket</h3>

            <div className={styles.field}>
                <label htmlFor="ticket-status">Status</label>
                <select
                    id="ticket-status"
                    value={status}
                    disabled={isSubmitting}
                    onChange={(event) =>
                        setStatus(event.target.value as Ticket['status'])
                    }
                >
                    <option value="open">Open</option>
                    <option value="in_progress">In progress</option>
                    <option value="closed">Closed</option>
                </select>
            </div>

            <div className={styles.field}>
                <label htmlFor="ticket-priority">Priority</label>
                <select
                    id="ticket-priority"
                    value={priority}
                    disabled={isSubmitting}
                    onChange={(event) =>
                        setPriority(event.target.value as Ticket['priority'])
                    }
                >
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
            </div>

            {submitError && <p role="alert">{submitError}</p>}

            <button
                type="submit"
                className={styles.submitButton}
                disabled={isSubmitting || !hasChanges}
            >
                {isSubmitting ? 'Saving...' : 'Save changes'}
            </button>
        </form>
    );
}