<?php
namespace braulioRam\dealsNotifier\Base;

use PHPMailer\PHPMailer\PHPMailer;
use Twig\Loader\FilesystemLoader;
use braulioRam\dealsNotifier\Base\Notifier;

Class EmailNotifier extends Notifier {
    protected $email;
    protected $sender = '';
    protected $twig;

    public function __construct(array $data, array $parameters)
    {
        parent::__construct($data, $parameters);

        $this->email = '';
        $loader = new \Twig_Loader_Filesystem(ROOT . '/src/Views');
        $this->twig = new \Twig_Environment($loader, ['debug' => true]);
        $this->twig->addExtension(new \Twig_Extension_Debug());
    }

    protected function setParameters(array $parameters)
    {
        if (!empty($parameters['email'])) {
            $this->email = $parameters['email'];
        }
    }


    protected function prepareData()
    {
        foreach ($this->data as $key => $dataset) {
            $first = array_shift($dataset['items']);
            $this->data[$key]['fields'] = array_keys($first);
        }
    }


    public function notify($type)
    {
        $parameters = [];
        $template = $this->twig->loadTemplate('/Email/Reports/'.$type.'.twig');
        $type = ucwords($type);

        if ($type == 'All') {
            $this->prepareData();
        } else {
            $first = reset($this->data);
            $fields = array_keys($first);
            $parameters['fields'] = $fields;
        }

        $parameters = array_merge($parameters, [
            'data' => $this->data,
            'title' => $type,
            'report_title' => $type,
            'report_type' => $type,
        ]);

        $subject  = $template->renderBlock('subject',   $parameters);
        $bodyHtml = $template->renderBlock('body_html', $parameters);

        $mail = new PHPMailer(true);
        
        try {
             //Server settings
            $mail->isSMTP();
            $mail->Host = 'ssl://smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $this->sender;
            $mail->SMTPSecure = 'tls';
            $mail->Password = '';
            $mail->Port = 465;
            //Recipients
            $mail->setFrom($this->sender, 'Mailer');
            $mail->addAddress($this->email, 'User');
            $mail->addReplyTo($this->sender, 'Mailer');

            //Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $bodyHtml;

            $mail->send();
            Logger::log('Report Email sent', 'notice');
        } catch (Exception $e) {
            Logger::log('Report Email could not be sent', 'error');
            Logger::log('Mailer Error: ' . $mail->ErrorInfo, 'error');
        }
    }
}
