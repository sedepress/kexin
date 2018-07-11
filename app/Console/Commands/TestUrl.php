<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Website;
use App\Models\Literature;

class TestUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:url';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach (Literature::all() as $website) {
            if (!$this->httpcode($website->url)) {
                $website->status = 0;
                $website->save();
            }
        }
    }

    public function httpcode($url){
        ini_set('max_execution_time',120);
        $ch = curl_init();
        $timeout = 3;
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_exec($ch);
        return $httpcode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
    }
}
