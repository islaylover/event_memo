<?php

namespace App\Infrastructure\External;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GoogleCalendarApiClient
{
    public function createEvent(User $user, string $title, string $description, Carbon $start, Carbon $end): string
    {
        try {
            $service = $this->setupClient($user);
            $event = $this->buildGoogleEvent($title, $description, $start, $end);
            // 登録（ユーザーの primary カレンダーに）
            $createdEvent = $service->events->insert('primary', $event);
            return $createdEvent->getId(); // ← Google Event ID を返す
        } catch (\Throwable $e) {
            Log::error('Google Calendar 登録エラー: ' . $e->getMessage());
        }
    }

    public function updateEvent(User $user, string $googleEventId, string $title, string $description, Carbon $start, Carbon $end): void
    {
        try {
            $service = $this->setupClient($user);
            // イベントデータ更新
            $event = $service->events->get('primary', $googleEventId);
            $event->setSummary($title);
            $event->setDescription($description);
            $startEventDateTime = new Google_Service_Calendar_EventDateTime();
            $startEventDateTime->setDateTime($start->toRfc3339String());
            $startEventDateTime->setTimeZone('Asia/Tokyo');
            $endEventDateTime = new Google_Service_Calendar_EventDateTime();
            $endEventDateTime->setDateTime($end->toRfc3339String());
            $endEventDateTime->setTimeZone('Asia/Tokyo');
            $event->setStart($startEventDateTime);
            $event->setEnd($endEventDateTime);
            $service->events->update('primary', $event->getId(), $event);
        } catch (\Throwable $e) {
            Log::error('Google Calendar 更新エラー: ' . $e->getMessage());
        }
    }

    public function deleteEvent(User $user, string $googleEventId): void
    {
        try {
            $service = $this->setupClient($user);
            $service->events->delete('primary', $googleEventId);
        } catch (\Throwable $e) {
            Log::error('Google Calendar 削除エラー: ' . $e->getMessage());
        }
    }

    private function setupClient(User $user): Google_Service_Calendar
    {
        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setAccessToken([
            'access_token' => $user->google_access_token,
            'refresh_token' => $user->google_refresh_token,
            'expires_in' => 3600,
        ]);
        if ($client->isAccessTokenExpired()) {
            $newToken = $client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
            $client->setAccessToken($newToken);
            $user->google_access_token = $newToken['access_token'];
            $user->save();
        }

        return new Google_Service_Calendar($client);
    }

    private function buildGoogleEvent(string $title, string $description, Carbon $start, Carbon $end): Google_Service_Calendar_Event
    {
        return new Google_Service_Calendar_Event([
            'summary' => $title,
            'description' => $description,
            'start' => [
                'dateTime' => $start->toRfc3339String(),
                'timeZone' => 'Asia/Tokyo',
            ],
            'end' => [
                'dateTime' => $end->toRfc3339String(),
                'timeZone' => 'Asia/Tokyo',
            ],
        ]);
    }
}