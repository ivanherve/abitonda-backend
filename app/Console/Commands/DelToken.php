<?php

namespace App\Console\Commands;

use App\Token;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DelToken extends Command
{
    protected $signature = 'command:disconnect';

    protected $description = 'Delete tokens after 48 hours of non-use';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // for loop on all visits, check date
        date_default_timezone_set("Africa/Kigali");
        $token = Token::all();
        foreach ($token as $k => $t) {
            # delete all of them
            if (date("Y-m-d H:i", strtotime("+2 day", strtotime($t->updated_at))) === date("Y-m-d H:i"))
                if (strlen($t->api_token) > 0) {
                    DB::select('call sign_out(?);', [$t->Token_Id]);
                    Log::info("$t->Token_Id deleted");
                }
        }
    }
}
