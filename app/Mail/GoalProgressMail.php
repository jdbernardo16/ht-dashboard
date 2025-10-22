<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Goal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GoalProgressMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public Goal $goal,
        public float $progressPercentage
    ) {
        $this->afterCommit();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $milestone = $this->getMilestoneMessage();
        return new Envelope(
            subject: "Goal Progress Update: {$milestone} - " . $this->goal->title,
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $milestone = $this->getMilestoneMessage();
        return $this
            ->subject("Goal Progress Update: {$milestone} - " . $this->goal->title)
            ->view('emails.goals.progress')
            ->with([
                'user' => $this->user,
                'goal' => $this->goal,
                'progressPercentage' => $this->progressPercentage,
                'milestone' => $this->getMilestoneMessage(),
                'dashboardUrl' => route('dashboard'),
                'goalUrl' => route('goals.show', $this->goal->id),
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

    /**
     * Get milestone message based on progress percentage
     */
    private function getMilestoneMessage(): string
    {
        if ($this->progressPercentage >= 100) {
            return 'Completed';
        } elseif ($this->progressPercentage >= 75) {
            return '75% Milestone';
        } elseif ($this->progressPercentage >= 50) {
            return '50% Milestone';
        } elseif ($this->progressPercentage >= 25) {
            return '25% Milestone';
        } else {
            return 'Progress Update';
        }
    }
}
