<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;
use App\vLocDev;
use App\IotData;

class sendAlertEmail extends Notification
{
    use Queueable;

    private $iotdata;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(IotData $iotdata)
    {
        $this->$iotdata = $iotdata;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return($this->iotdata->deviceid);
        $loc = vLocDev::where('serial_no','=',$this->iotdata->deviceid)
            ->select('name')
            ->first();

        return (new MailMessage)
                    ->subject('Data Alert')
                    ->from('noreply@ko-aaham.com')
                    ->line('Parameters captured at '. Carbon.now()->format('Y-m-d h:i:s'))
                    ->line('Temperature: '.$this->iotdata->temp.', SPO2: '.$this->iotdata->spo2)
                    //->action('Notification Action', url('/'))
                    ->line('Thank you for using permit me!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
