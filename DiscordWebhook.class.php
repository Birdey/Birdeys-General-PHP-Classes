<?php

namespace Birdey;

use Converter\BBCodeConverter;

class DiscordWebhook
{

    function __construct(
        private string $webhookUrl,
        private string $userName,
        private string $imageUrl
    ) {
    }

    public function generateEmbeds(string $title, string $message, string $url): array
    {
        return [
            'username' => $this->userName,
            'avatar_url' => $this->imageUrl,
            'content' => '@everyone Nyhet på speletshus.se',
            'embeds' => [
                [
                    'title' => $title,
                    'description' => $message,
                    'color' => hexdec("3366ff"),
                    'url' => $url,
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

    public function SendMessage(string $title, string $message, string $url): bool
    {
        $json_data = json_encode($this->generateEmbeds($title, $message, $url), JSON_THROW_ON_ERROR);
        echo $json_data;
        echo '<hr>';

        $ch = curl_init($this->webhookUrl);
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
