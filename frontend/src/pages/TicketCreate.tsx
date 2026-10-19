import { useEffect, useState, type SubmitEvent } from 'react';
import { Link, useNavigate } from 'react-router';

import { api } from '../lib/api';
import type { Category } from '../types/category';
import type { Ticket } from '../types/ticket';

export function TicketCreate() {
    const [categories, setCategories] = useState<Category[]>([]);
    const [title, setTitle] = useState('');
    const [description, setDescription] = useState('');
    const [categoryId, setCategoryId] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submitError, setSubmitError] = useState('');

    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState('');

    const navigate = useNavigate();

    useEffect(() => {
        const controller = new AbortController();

        async function loadCategories() {
            try {
                const response = await api.get<Category[]>(
                    '/api/categories',
                    { signal: controller.signal },
                );

                setCategories(response.data);
            } catch (error) {
                if (controller.signal.aborted) {
                    return;
                }

                console.error('Failed to load categories:', error);
                setError('Could not load categories.');
            } finally {
                if (!controller.signal.aborted) {
                    setIsLoading(false);
                }
            }
        }

        void loadCategories();

        return () => controller.abort();
    }, []);

    async function handleSubmit(event: SubmitEvent<HTMLFormElement>) {
        event.preventDefault();

        const selectedCategory = categories.find(
            (category) => category.id === Number(categoryId),
        );

        if (
            isSubmitting ||
            !title.trim() ||
            !description.trim() ||
            !selectedCategory
        ) {
            return;
        }

        setIsSubmitting(true);
        setSubmitError('');

        try {
            const response = await api.post<Ticket>('/api/tickets', {
                title: title.trim(),
                description: description.trim(),
                category_id: Number(categoryId),
            });

            navigate(`/tickets/${response.data.id}`);
        } catch (error) {
            console.error('Failed to create ticket:', error);
            setSubmitError('Could not create ticket.');
        } finally {
            setIsSubmitting(false);
        }
    }

    return (
        <section>
            <Link to="/tickets">Back to tickets</Link>
            <h2>Create ticket</h2>

            {isLoading ? (
                <p>Loading categories...</p>
            ) : error ? (
                <p role="alert">{error}</p>
            ) : categories.length === 0 ? (
                <p>No categories available.</p>
            ) : (
                <form onSubmit={handleSubmit}>
                    <div>
                        <label htmlFor="ticket-title">Title</label>
                        <input
                            id="ticket-title"
                            type="text"
                            value={title}
                            onChange={(event) => setTitle(event.target.value)}
                            maxLength={255}
                            required
                        />
                    </div>

                    <div>
                        <label htmlFor="ticket-description">Description</label>
                        <textarea
                            id="ticket-description"
                            value={description}
                            onChange={(event) => setDescription(event.target.value)}
                            required
                        />
                    </div>

                    <div>
                        <label htmlFor="ticket-category">Category</label>
                        <select
                            id="ticket-category"
                            value={categoryId}
                            onChange={(event) => setCategoryId(event.target.value)}
                            required
                        >
                            <option value="">Select a category</option>

                            {categories.map((category) => (
                                <option key={category.id} value={category.id}>
                                    {category.name}
                                </option>
                            ))}
                        </select>
                    </div>

                    {submitError && <p role="alert">{submitError}</p>}

                    <button
                        type="submit"
                        disabled={
                            isSubmitting ||
                            !title.trim() ||
                            !description.trim() ||
                            !categoryId
                        }
                    >
                        {isSubmitting ? 'Creating...' : 'Create ticket'}
                    </button>
                </form>
            )}
        </section>
    );
}
