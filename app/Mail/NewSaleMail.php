<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewSaleMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public Sale $sale
    ) {
        $this->afterCommit();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Sale Recorded: ' . $this->sale->client->name,
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
            ->subject('New Sale Recorded: ' . $this->sale->client->name)
            ->view('emails.sales.new-sale')
            ->with([
                'user' => $this->user,
                'sale' => $this->sale,
                'client' => $this->sale->client,
                'dashboardUrl' => route('dashboard'),
                'saleUrl' => route('sales.show', $this->sale->id),
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
