<?php

namespace DavideCasiraghi\LaravelEventsCalendar\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportMisuse extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The report instance.
     *
     * @var array
     */
    protected $report;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $report)
    {
        $this->report = $report;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Configure email parameters in .env file
        switch ($this->report['reason']) {

             /* Send email to the user that has created the event */
             case 'It is not translated in english':

                return $this->markdown('laravel-events-calendar::emails.misuse.organizer-event-not-english')
                 ->to($this->report['creatorEmail'])
                 ->from($this->report['senderEmail'], 'Global CI Calendar')
                 ->replyTo($this->report['senderEmail'], 'Global CI Calendar')
                 ->subject($this->report['subject'])
                 ->with([
                     'event_title' => $this->report['event_title'],
                     'event_id' => $this->report['event_id'],
                     'event_slug' => $this->report['event_slug'],
                     'reason' => $this->report['reason'],
                     'msg' => $this->report['message_misuse'],
                 ]);

                 break;

             /* Send email to the administrator */
             default:

                return $this->markdown('laravel-events-calendar::emails.misuse.administrator-report-misuse')
                    ->from('noreply@globalcalendar.com', 'Global CI Calendar')
                    ->replyTo('noreply@globalcalendar.com', 'Global CI Calendar')
                    ->subject($this->report['subject'])
                    ->with([
                        'event_title' => $this->report['event_title'],
                        'event_id' => $this->report['event_id'],
                        'event_slug' => $this->report['event_slug'],
                        'reason' => $this->report['reason'],
                        'msg' => $this->report['message_misuse'],
                    ]);

                 break;
         }
    }
}
