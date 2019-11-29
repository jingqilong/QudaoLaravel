<?php

namespace App\Console\Commands;

use App\Repositories\CommonPvRepository;
use App\Services\Common\PvService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RecordPv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'record:pv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '把缓存中的访问量数据写入数据库';

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
        $key = 'record_pv';
        if (!Cache::has($key)){
            print '没有可存储数据';
            return true;
        }
        $data = Cache::get($key);
        Cache::forget($key);
        print '有'.count($data).'条可存储数据  ';
        DB::beginTransaction();
        foreach ($data as $value){
            if (!CommonPvRepository::exists(['created_at' => $value['created_at']])){
                if (!CommonPvRepository::getAddId($value)){
                    DB::rollBack();
                    PvService::returnData($key,$data);
                    return false;
                }
                continue;
            }
            if (!CommonPvRepository::increment(['created_at' => $value['created_at']],'count')){
                DB::rollBack();
                PvService::returnData($key,$data);
                return false;
            }
        }
        print '存储完成';
        DB::commit();
        return true;
    }
}
