<?php

namespace App\Mail;

use App\Models\User;
use App\Models\ContentPost;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContentPublishedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public ContentPost $contentPost
    ) {
        $this->afterCommit();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Content Published: ' . $this->contentPost->title,
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
            ->subject('Content Published: ' . $this->contentPost->title)
            ->view('emails.content.published')
            ->with([
                'user' => $this->user,
                'contentPost' => $this->contentPost,
                'categories' => collect([$this->contentPost->category]),
                'dashboardUrl' => route('dashboard'),
                'contentUrl' => route('content.web.show', $this->contentPost->id),
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
