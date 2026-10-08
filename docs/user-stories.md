# TicketFlow v0.1 – User Stories

## Business Rule: Closed Tickets

A closed ticket is considered final.

- A closed ticket cannot be modified.
- A closed ticket cannot receive new comments.
- A closed ticket cannot be reopened in TicketFlow v0.1.

---

## 1. Authentication

### US-01 – Login

**As a** User,  
**I want to** log in to the application,  
**so that** I can use the ticket system.

#### Acceptance Criteria

- A User can enter their email address and password.
- A User with valid credentials can log in successfully.
- A User with invalid credentials cannot log in.
- An unauthenticated User cannot access protected areas of the application.
- After a successful login, the User is redirected to the dashboard.

### US-02 – Logout

**As a** User,  
**I want to** log out of the application,  
**so that** I can securely end my session.

#### Acceptance Criteria

- An authenticated User can log out.
- After logging out, the User is redirected to the login page.
- After logging out, the User can no longer access protected areas of the application.

---

## 2. Customer

### US-03 – Create Ticket

**As a** Customer,  
**I want to** create a support ticket,  
**so that** I can report a problem to the support team.

#### Acceptance Criteria

- A Customer can enter a title and description.
- A Customer must select a category.
- A newly created ticket has the status `open`.
- A newly created ticket has the priority `medium`.
- The ticket is associated with the authenticated Customer.
- Invalid input is rejected.

### US-04 – View Own Tickets

**As a** Customer,  
**I want to** view my own tickets,  
**so that** I can track my support requests.

#### Acceptance Criteria

- A Customer can view a list of their own tickets.
- The list shows the relevant information for identifying and tracking each ticket.
- A Customer cannot see tickets belonging to other Customers.

### US-05 – View Ticket Details

**As a** Customer,  
**I want to** view the details of one of my tickets,  
**so that** I can review its current state and communication with support.

#### Acceptance Criteria

- A Customer can open one of their own tickets.
- The ticket details include its title, description, category, status and priority.
- The Customer can see the comments associated with the ticket.
- A Customer cannot access the details of another Customer's ticket.

### US-06 – Comment on Own Ticket

**As a** Customer,  
**I want to** comment on one of my tickets,  
**so that** I can provide additional information or respond to support.

#### Acceptance Criteria

- A Customer can add a comment to one of their own tickets.
- A comment must contain content.
- The comment is associated with the authenticated Customer and the ticket.
- The new comment is visible in the ticket's conversation.
- A Customer cannot comment on another Customer's ticket.
- A Customer cannot add comments to a closed ticket.

### US-07 – Close Own Ticket

**As a** Customer,  
**I want to** close one of my own tickets,  
**so that** I can indicate that I no longer need support.

#### Acceptance Criteria

- A Customer can close one of their own tickets.
- A Customer cannot close another Customer's ticket.
- A Customer cannot otherwise change the status of a ticket.
- A closed ticket cannot be reopened in v0.1.
- A closed ticket cannot receive new comments.

---

## 3. Support

### US-08 – View All Tickets

**As a** Support Agent,  
**I want to** view all support tickets,  
**so that** I can identify tickets that require support.

#### Acceptance Criteria

- A Support Agent can view tickets from all Customers.
- The ticket list shows the relevant information for identifying and prioritizing tickets.
- A Support Agent can open a ticket to view its details.

### US-09 – Update Ticket

**As a** Support Agent,  
**I want to** update a ticket's support information,  
**so that** I can reflect its current state and priority.

#### Acceptance Criteria

- A Support Agent can change a ticket's status.
- A Support Agent can change a ticket's priority.
- A Support Agent can change a ticket's category.
- Only valid status, priority and category values are accepted.
- A Customer cannot change a ticket's priority or category.
- A Customer cannot change a ticket's status except by closing their own ticket.
- A closed ticket cannot be modified.

### US-10 – Comment on Ticket

**As a** Support Agent,  
**I want to** comment on a support ticket,  
**so that** I can communicate with the Customer.

#### Acceptance Criteria

- A Support Agent can add a comment to any non-closed ticket.
- A comment must contain content.
- The comment is associated with the authenticated Support Agent and the ticket.
- The new comment is visible in the ticket's conversation.

---

## 4. Dashboard

### US-11 – View Dashboard

**As an** authenticated User,  
**I want to** see an overview of relevant tickets,  
**so that** I can quickly understand the current support situation.

#### Acceptance Criteria

- An authenticated User can access the dashboard.
- A Customer only sees information based on their own tickets.
- A Support Agent can see information based on all tickets.
- The dashboard shows the number of tickets by relevant status.
- The dashboard shows a list of recent tickets.
- A User can navigate from the dashboard to a displayed ticket.

---

## 5. Admin

For TicketFlow v0.1, an **Admin** has at least the same ticket-management permissions as a **Support Agent**.

Dedicated administration features such as user management and category management are **outside the scope of v0.1**.