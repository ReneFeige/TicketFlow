import { useEffect, useState, type SubmitEvent } from 'react';

import { api } from '../lib/api';
import type { Comment } from '../types/comment';

type TicketCommentsProps = {
    ticketId: number;
    isClosed: boolean;
};

export function TicketComments({ ticketId, isClosed }: TicketCommentsProps) {
    const [comments, setComments] = useState<Comment[]>([]);
    const [body, setBody] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submitError, setSubmitError] = useState('');

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

    async function handleSubmit(event: SubmitEvent<HTMLFormElement>) {
        event.preventDefault();

        if (isSubmitting || isClosed || !body.trim()) {
            return;
        }

        setIsSubmitting(true);
        setSubmitError('');

        try {
            const response = await api.post<Comment>(
                `/api/tickets/${ticketId}/comments`,
                { body: body.trim() },
            );

            setComments((previousComments) => [
                ...previousComments,
                response.data,
            ]);

            setBody('');
        } catch (error) {
            console.error('Failed to create comment:', error);
            setSubmitError('Could not post comment.');
        } finally {
            setIsSubmitting(false);
        }
    }

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

            {!isClosed && !isLoading && !error && (
                <form onSubmit={handleSubmit}>
                    <h4>Add comment</h4>

                    <div>
                        <label htmlFor="comment-body">Comment</label>
                        <textarea
                            id="comment-body"
                            value={body}
                            onChange={(event) => setBody(event.target.value)}
                            maxLength={5000}
                            required
                        />
                    </div>

                    {submitError && <p role="alert">{submitError}</p>}

                    <button type="submit" disabled={isSubmitting || !body.trim()}>
                        {isSubmitting ? 'Posting...' : 'Post comment'}
                    </button>
                </form>
            )}
        </section>
    );
}