import { useEffect, useState } from 'react';

import { api } from '../lib/api';
import type { Comment } from '../types/comment';

type TicketCommentsProps = {
    ticketId: number;
};

export function TicketComments({ ticketId }: TicketCommentsProps) {
    const [comments, setComments] = useState<Comment[]>([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState('');

    useEffect(() => {
        const controller = new AbortController();

        async function loadComments() {
            setIsLoading(true);
            setError('');

            try {
                const response = await api.get<Comment[]>(
                    `/api/tickets/${ticketId}/comments`,
                    { signal: controller.signal },
                );

                setComments(response.data);
            } catch (error) {
                if (controller.signal.aborted) {
                    return;
                }

                console.error('Failed to load comments:', error);
                setError('Could not load comments.');
            } finally {
                if (!controller.signal.aborted) {
                    setIsLoading(false);
                }
            }
        }

        void loadComments();

        return () => {
            controller.abort();
        };
    }, [ticketId]);

    return (
        <section>
            <h3>Comments</h3>

            {isLoading ? (
                <p>Loading comments...</p>
            ) : error ? (
                <p role="alert">{error}</p>
            ) : comments.length === 0 ? (
                <p>No comments yet.</p>
            ) : (
                <ul>
                    {comments.map((comment) => (
                        <li key={comment.id}>
                            <strong>{comment.user.name}</strong>
                            <p>{comment.body}</p>
                        </li>
                    ))}
                </ul>
            )}
        </section>
    );
}