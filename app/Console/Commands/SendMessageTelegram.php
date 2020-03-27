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

        $response = Http::get('https://covid19-brazil-api.now.sh/api/report/v1/brazil')->json();

        if (isset($response['data']['confirmed'])) {
            $confirmed       = $response['data']['confirmed'];
            if ($confirmed != Cache::get('confirmed', 0)) {
                Cache::put('confirmed', $confirmed, Carbon::now()->addYear(1));
                $this->sendMessage($response['data']);
            }
        }

        $this->info('Comando executado.');
    }

    private function sendMessage($response)
    {
        $text = "*Casos Suspeitos:* {$response['cases']}\n*Casos Confirmados:* {$response['confirmed']}\n*Mortes:* {$response['deaths']}";
        $this->telegram->sendMessage([
            'chat_id'    => '@corona_virus_br',
            'text'       => $text,
            'parse_mode' => 'Markdown'
        ]);
    }
}
