<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DemoTicketSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::whereIn('email', [
            'customer@ticketflow.demo',
            'customer2@ticketflow.demo',
        ])->get()->keyBy('email');

        if ($customers->count() !== 2) {
            throw new RuntimeException('Demo customers are missing. Run DemoUserSeeder first.');
        }

        $categories = [];

        foreach (['Hardware', 'Software', 'Account'] as $name) {
            $categories[$name] = Category::firstOrCreate([
                'name' => $name,
            ]);
        }

        $tickets = [
            [
                'title' => 'Laptop does not start',
                'description' => 'My laptop does not turn on after the latest update.',
                'category' => 'Hardware',
                'customer' => 'customer@ticketflow.demo',
                'status' => 'open',
                'priority' => 'high',
            ],
            [
                'title' => 'Cannot install software',
                'description' => 'The installation fails with an error message.',
                'category' => 'Software',
                'customer' => 'customer@ticketflow.demo',
                'status' => 'in_progress',
                'priority' => 'medium',
            ],
            [
                'title' => 'Password reset request',
                'description' => 'I need help resetting my account password.',
                'category' => 'Account',
                'customer' => 'customer@ticketflow.demo',
                'status' => 'closed',
                'priority' => 'low',
            ],
            [
                'title' => 'External monitor not detected',
                'description' => 'My second monitor is no longer recognized.',
                'category' => 'Hardware',
                'customer' => 'customer2@ticketflow.demo',
                'status' => 'open',
                'priority' => 'medium',
            ],
            [
                'title' => 'Application crashes on startup',
                'description' => 'The application closes immediately after launching.',
                'category' => 'Software',
                'customer' => 'customer2@ticketflow.demo',
                'status' => 'in_progress',
                'priority' => 'high',
            ],
        ];

        foreach ($tickets as $ticketData) {
            $customer = $customers->get($ticketData['customer']);

            if (! $customer) {
                throw new RuntimeException('Demo customer does not exist.');
            }

            Ticket::firstOrCreate(
                [
                    'customer_id' => $customer->id,
                    'title' => $ticketData['title'],
                ],
                [
                    'category_id' => $categories[$ticketData['category']]->id,
                    'description' => $ticketData['description'],
                    'status' => $ticketData['status'],
                    'priority' => $ticketData['priority'],
                ],
            );
        }
    }
}
