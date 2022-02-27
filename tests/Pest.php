<?php

/** @noinspection LaravelFunctionsInspection */

use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Tests\Support\TestWebhookHandler;
use DefStudio\Telegraph\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

uses(TestCase::class)->in(__DIR__);


/**
 * @return Collection<TelegraphBot>
 */
function bots(int $count): Collection
{
    return TelegraphBot::factory()->count($count)->create();
}

function sandbox_bot(): TelegraphBot
{
    return bot(env('SANDOBOX_TELEGRAM_BOT_TOKEN'));
}

function bot(string $token = '3f3814e1-5836-3d77-904e-60f64b15df36', string $chatId = '-123456789'): TelegraphBot
{
    /** @var TelegraphBot $bot */
    $bot = TelegraphBot::factory(['token' => $token])
        ->create();

    $bot->chats()->save(TelegraphChat::factory(['chat_id' => $chatId, 'telegraph_bot_id' => null])->make());

    return $bot->refresh();
}

function make_bot(): TelegraphBot
{
    $bot = TelegraphBot::factory(['token' => '3f3814e1-5836-3d77-904e-60f64b15df36'])->make();
    $bot->setRelation('chats', Collection::make([TelegraphChat::factory(['chat_id' => '-123456789'])->make()]));

    return $bot;
}

function chat(): TelegraphChat
{
    return TelegraphChat::factory(['chat_id' => '-123456789'])->create();
}

function make_chat(): TelegraphChat
{
    $bot = make_bot();
    $chat = TelegraphChat::factory([
        'chat_id' => '-123456789',
        'telegraph_bot_id' => null,
    ])->make();

    $chat->setRelation('bot', $bot);
    $bot->setRelation('chats', Collection::make([$chat]));

    return $chat;
}

function register_webhook_handler(string $handler = TestWebhookHandler::class): void
{
    if ($handler == TestWebhookHandler::class) {
        TestWebhookHandler::reset();
    }
    config()->set('telegraph.webhook_handler', $handler);
}

function webhook_request($action = 'invalid', $handler = TestWebhookHandler::class): Request
{
    register_webhook_handler($handler);

    return Request::create('', 'POST', [
        'callback_query' => [
            'id' => 159753,
            'message' => [
                'message_id' => 123456,
                'chat' => [
                    'id' => -123456789,
                ],
                'reply_markup' => [
                    'inline_keyboard' => [
                        [
                            ["text" => "test", "callback_data" => "action:test;id:1"],
                            ["text" => "delete", "callback_data" => "action:delete;id:2"],
                        ],
                        [
                            ["text" => "👀 Apri", "url" => 'https://test.it'],
                        ],
                    ],
                ],
            ],
            'data' => "action:$action",
        ],
    ]);
}

function webhook_command($command, $handler = TestWebhookHandler::class): Request
{
    register_webhook_handler($handler);

    return Request::create('', 'POST', [
        'message' => [
            'message_id' => 123456,
            'chat' => [
                'id' => -123456789,
            ],
            'text' => $command,
        ],
    ]);
}
