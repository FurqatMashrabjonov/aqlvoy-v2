<?php

require_once 'vendor/autoload.php';
require_once 'db.php';

$db = new DB();

const ADMIN_ID = 778912691;

try {
    $bot = new \TelegramBot\Api\Client('1149970807:AAHtAbkXNBpgJTZBpGqxbQnI-B9bxGZWjYI');
    $client = OpenAI::client('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');

    $bot->command('start', function ($message) use ($bot, $db) {
        $chat_id = $message->getChat()->getId();
        if (!$db->checkUser($chat_id)) {
            $db->insert($message);
        }
        $bot->sendMessage($chat_id, 'Salom, Botga hush kelibsiz.');
    });

    $bot->command('users', function ($message) use ($bot, $db) {
        $chat_id = $message->getChat()->getId();
        if ($chat_id == ADMIN_ID) {
            $users = $db->getAllUsers();
            $msg = '';
            foreach ($users as $user) {
                $msg .= <<<EOF
<b>Username: </b> <i>{$user['username']}</i>
<b>First Name: </b> <i>{$user['first_name']}</i>
<b>Last Name: </b> <i>{$user['last_name']}</i>
<b>Attempts: </b> <i>{$user['attempts']}</i>
-----------------------------------------------------------------\n
EOF;
            }
            $bot->sendMessage($message->getChat()->getId(), $msg, 'HTML');
        }
    });

    $bot->on(function (\TelegramBot\Api\Types\Update $update) use ($bot, $client) {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();

        if (strlen($message->getText()) <= 2000) {

            $bot->sendChatAction($id, 'typing');

            $result = $client->completions()->create([
                'model' => 'text-davinci-003', //model nomi
                'prompt' => $message->getText(),
                'max_tokens' => 4000 //maximum javob uzunligi
            ]);

            $bot->sendChatAction($id, 'typing');

            $msg = '';
            foreach ($result['choices'] as $choice) {
                $msg .= $choice['text'];
            }

            $bot->sendMessage($id, $msg);

        } else {
            $bot->sendMessage($id, 'Iltimos qisqaroq so\'rovni amalga oshiring');
        }

    }, function () {
        return true;
    });

    $bot->run();

} catch (\TelegramBot\Api\Exception $e) {
    $e->getMessage();
}