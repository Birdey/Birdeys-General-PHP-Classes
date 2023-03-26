<?php

namespace Birdey;

use Converter\BBCodeConverter;

class DiscordWebhook
{

    private string $webhookurl = "https://discord.com/api/webhooks/1087419678156144710/dl8KtdBImg5qmqwkq60vbNKWOI20PgT1vz1lKMYyQNfAsmHtT9wd8lTIObu3-51aYSsa";
    private string $userName = 'Speletshus Styrelse';
    private string $imageUrl = 'http://speletshus.se/res/img/logo/sph_logo.png';
    private string $title = '';
    private string $message = '';
    private string $url = '';

    function __construct(string $title, string $message, string $url)
    {
        $this->title   = $title;
        $this->message = $message;
        $this->url     = $url;
    }

    public function getWebHookData()
    {
        $webhookUrl = "https://discord.com/api/webhooks/1087419678156144710/dl8KtdBImg5qmqwkq60vbNKWOI20PgT1vz1lKMYyQNfAsmHtT9wd8lTIObu3-51aYSsa";

    }

    public function generateEmbeds(): array
    {
        return [
            'username' => $this->userName,
            'avatar_url' => $this->imageUrl,
            'content' => '@everyone Nyhet på speletshus.se',
            'embeds' => [
                [
                    'title' => $this->getTitle(),
                    'description' => $this->getMessageAsMarkdown(),
                    'color' => hexdec("3366ff"),
                    'url' => $this->getURL(),
                    'thumbnail' => [
                        'url' => $this->imageUrl,
                    ],
                    'footer' => [
                        'text' => 'Mvh ' . $this->userName,
                        'icon_url' => $this->imageUrl,
                    ]
                ]
            ]
        ];
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getMessageAsMarkdown()
    {
        $convertedMessage = (new BBCodeConverter($this->message, 'Discord Webhook'))->toMarkdown();
        return $convertedMessage;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function SendMessage(): bool
    {
        $json_data = json_encode($this->generateEmbeds(), JSON_THROW_ON_ERROR);
        echo $json_data;
        echo '<hr>';

        $ch = curl_init($this->webhookurl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        curl_close($ch);
        // If you need to debug, or find out why you can't send message uncomment line below, and execute script.
        var_dump($response);
        if ($response == '') {
            return true;
        }

        echo '<h1>ERROR POSTING NEWS TO DISCORD</h1>';
        echo '<pre>';
        var_dump($response);
        echo '</pre>';
        echo '<span style="font-size: 32px; color: red; background: black; margin: 5px; padding: 5px;">Send a screenshot of this page to Birdey<span>';
        die();
    }
}