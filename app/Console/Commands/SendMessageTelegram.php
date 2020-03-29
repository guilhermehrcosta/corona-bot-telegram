<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Telegram\Bot\Api;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SendMessageTelegram extends Command
{
    protected $signature = 'telegram:send-message';

    protected $description = 'Envia mensagem bot via telegram.';

    protected $telegram;

    public function __construct()
    {
        parent::__construct();
        $this->telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
    }

    public function handle()
    {
        $response = Http::get('https://coronavirus-19-api.herokuapp.com/countries/brazil')->json();

        if (isset($response['country'])) {
            $confirmed = $response['active'];
            if ($confirmed != Cache::get('confirmed', 0)) {
                Cache::put('confirmed', $confirmed, Carbon::now()->addYear(1));
                $this->sendMessage($response['active']);
            }
        }

        $this->info('Comando executado.');
    }

    private function sendMessage($response)
    {
        $text = "*Casos Suspeitos:* {$response['cases']}\n*Casos Confirmados:* {$response['active']}\n*Mortes:* {$response['deaths']}";
        $this->telegram->sendMessage([
            'chat_id'    => '@corona_virus_br',
            'text'       => $text,
            'parse_mode' => 'Markdown'
        ]);
    }
}
