import type { TicketPriority } from '../../types/ticket';
import styles from './PriorityBadge.module.css';

type PriorityBadgeProps = {
    priority: TicketPriority;
};

const priorityLabels: Record<TicketPriority, string> = {
    low: 'Low',
    medium: 'Medium',
    high: 'High',
};

export function PriorityBadge({ priority }: PriorityBadgeProps) {
    return (
        <span className={`${styles.badge} ${styles[priority]}`}>
            {priorityLabels[priority]}
        </span>
    );
}