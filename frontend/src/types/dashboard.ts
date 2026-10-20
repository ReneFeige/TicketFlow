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
        status: 'open' | 'in_progress' | 'closed';
        priority: 'low' | 'medium' | 'high';
        created_at: string;
    }[];
};