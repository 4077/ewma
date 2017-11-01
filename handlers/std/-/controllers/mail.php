<?php namespace ewma\handlers\std\controllers;

class Mail extends \Controller
{
    public function send()
    {
        foreach (l2a($this->data('recipients')) as $recipient) {
            /**
             * @var $mailer \std\mailer\Mailer
             */
            $mailer = $this->c('\std\mailer~:get');

            $mailer->AddAddress($recipient);

            $mailer->From = $this->data('sender/email');
            $mailer->FromName = $this->data('sender/name');
            $mailer->Subject =  $this->data('subject');
            $mailer->Body = $this->data('body');

            $mailer->send();
        }
    }
}
