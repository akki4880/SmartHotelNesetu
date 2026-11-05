<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GlobalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $messageContent;
    public $attachmentsData;

    public function __construct($subject, $messageContent, $attachmentsData = [])
    {
        $this->subject = $subject;
        $this->messageContent = $messageContent;
        $this->attachmentsData = $attachmentsData;
    }

    public function build()
    {
        $email = $this->subject($this->subject)
            ->html($this->messageContent);

        foreach ($this->attachmentsData as $file) {

            // 🔥 RAW PDF (NO STORAGE)
            if (isset($file['data'])) {
                $email->attachData(
                    $file['data'],
                    $file['name'] ?? 'file.pdf',
                    ['mime' => $file['mime'] ?? 'application/pdf']
                );
            }

            // 🔁 FILE PATH (fallback)
            elseif (isset($file['path']) && file_exists($file['path'])) {
                $email->attach($file['path'], [
                    'as' => $file['as'] ?? basename($file['path']),
                    'mime' => $file['mime'] ?? mime_content_type($file['path']),
                ]);
            }
        }

        return $email;
    }
}
