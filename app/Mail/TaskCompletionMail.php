<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskCompletionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public Task $task
    ) {
        $this->afterCommit();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Task Completed: ' . $this->task->title,
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Task Completed: ' . $this->task->title)
            ->view('emails.tasks.completion')
            ->with([
                'user' => $this->user,
                'task' => $this->task,
                'completedBy' => $this->task->assignedTo,
                'dashboardUrl' => route('dashboard'),
                'taskUrl' => route('tasks.show', $this->task->id),
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
