<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Expense;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExpenseApprovalMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public Expense $expense
    ) {
        $this->afterCommit();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $status = $this->expense->status === 'approved' ? 'Approved' : 'Rejected';
        return new Envelope(
            subject: "Expense {$status}: " . $this->expense->description,
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $status = $this->expense->status === 'approved' ? 'Approved' : 'Rejected';
        return $this
            ->subject("Expense {$status}: " . $this->expense->description)
            ->view('emails.expenses.approval')
            ->with([
                'user' => $this->user,
                'expense' => $this->expense,
                'category' => $this->expense->category,
                'dashboardUrl' => route('dashboard'),
                'expenseUrl' => route('expenses.show', $this->expense->id),
            ])
            ->withSymfonyMessage(function ($message) {
                $message->getHeaders()->addTextHeader('Content-Transfer-Encoding', '8bit');
                $message->getHeaders()->addTextHeader('Content-Type', 'text/html; charset=utf-8');
            });
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
