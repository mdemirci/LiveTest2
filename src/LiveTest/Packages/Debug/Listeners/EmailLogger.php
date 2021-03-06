<?php

/*
* This file is part of the LiveTest package. For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace LiveTest\Packages\Debug\Listeners;
use phmLabs\Components\Annovent\Annotation\Event;
use LiveTest\Listener\Base;

use Zend\Mime\Mime;
use Zend\Mime\Part;
use Zend\Mail\Mail;

class EmailLogger extends Base
{
    private $template;
    private $standardTemplate = 'templates/email_attachment.tpl';
    private $to;
    private $attachmentName = 'LiveTest Report';
    private $from;
    private $subject;

    public function init($to, $from, $subject, $emailTemplate = null)
    {
        $this->to = $to;
        $this->from = $from;
        $this->subject = $subject;
        if (!is_null($emailTemplate)) {
            $this->template = $emailTemplate;
        } else {
            $this->template = __DIR__ . '/' . $this->standardTemplate;
        }
    }

    /**
     * @Event("LiveTest.Configuration.Exception")
     *
     * @param \Exception $e
     */
    public function handleConfigurationException(\Exception $exception, Event $event)
    {
        $this->writeMail($exception->getMessage() . '\n ' . $exception->getTraceAsString());
    }

    /**
     * @Event("LiveTest.Runner.Error")
     *
     * @param \Exception $e
     */
    public function handleException(\Exception $exception, Event $event)
    {
        $this->writeMail($exception->getMessage() . '\n ' . $exception->getTraceAsString());
    }

    private function writeMail($bodyText, $atText = null)
    {
        $mail = new Mail();

        $mail->addTo($this->to);
        $mail->setFrom($this->from);
        $mail->setSubject($this->subject);
        $mail->setBodyHtml(file_get_contents($this->template) . $bodyText);
        if ($at !== null) {
            $at = new Part($atText);
            $at->type = 'text/html';
            $at->disposition = Mime::DISPOSITION_INLINE;
            $at->encoding = Mime::ENCODING_BASE64;
            $at->filename = $this->attachmentName;
            $at->description = 'LiveTest Attachment';
            $mail->addAttachment($at);
        }
        $mail->send();
    }

}

