export type Comment = {
    id: number;
    ticket_id: number;
    user_id: number;
    body: string;
    created_at: string;
    updated_at: string;
    user: {
        id: number;
        name: string;
    };
};