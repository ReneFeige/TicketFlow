import type { TicketStatus } from '../../types/ticket';
import styles from './StatusBadge.module.css';

type StatusBadgeProps = {
    status: TicketStatus;
};

const statusLabels: Record<TicketStatus, string> = {
    open: 'Open',
    in_progress: 'In progress',
    closed: 'Closed',
};

export function StatusBadge({ status }: StatusBadgeProps) {
    return (
        <span className={`${styles.badge} ${styles[status]}`}>
            {statusLabels[status]}
        </span>
    );
}