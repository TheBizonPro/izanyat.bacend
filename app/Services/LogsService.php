<?php
namespace App\Services;
use Illuminate\Support\Facades\Log;

class LogsService {

    public function userLog(string $title, int $userId, array $data=[])
    {
        $this->log('userlog', $title, $userId, $data);
    }

    public function tinkoffLog(string $title, int $userId, array $data=[])
    {
        $this->log('tinkofflog', $title, $userId, $data);
    }

    public function fnsLog(string $title, int $userId, array $data=[])
    {
        $this->log('fnslog', $title, $userId, $data);
    }

    public function mobiLog(string $title, int $userId, array $data=[])
    {
        $this->log('mobilog', $title, $userId, $data);
    }

    public function signmeLog(string $title, int $userId, array $data=[])
    {
        $this->log('signmelog', $title, $userId, $data);
    }

    protected function log(string $type, string $title, int $userId, array $data=[])
    {
        Log::channel('fs')->info($title, [
            'data' => $data,
            'type' => $type,
            'user_id' => $userId
        ]);
    }
}
