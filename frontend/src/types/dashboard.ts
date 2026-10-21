import type { TicketPriority, TicketStatus } from "./ticket";

export type DashboardData = {
    stats: {
        total: number;
        open: number;
        in_progress: number;
        closed: number;
    };
    recent_tickets: {
        id: number;
        title: string;
        status: TicketStatus;
        priority: TicketPriority;
        created_at: string;
    }[];
};