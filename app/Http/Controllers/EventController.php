<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Domain\Models\Event\EventUserId;
use App\Services\EventService;
use Exception;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    protected EventService $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function index()
    {
        $eventUserId = new EventUserId(Auth::id());
        $events = $this->eventService->getAllEventSummaries($eventUserId);
        return view('events.index', compact('events'));   
    }

    public function create()
    {
        $tags = $this->eventService->getAllTags(Auth::id()); 
        return view('events.create', compact('tags'));
    }

    public function edit($id)
    {
        $event = $this->eventService->getEventDetail($id, Auth::id());
        $tags = $this->eventService->getAllTags(Auth::id()); 
        return view('events.edit', compact('event', 'tags'));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all(); // バリデーションまだならここでOK
            $data['user_id'] = Auth::id();
            $event = $this->eventService->createEvent($data);
            $this->eventService->syncWithGoogleCalendar(auth()->user(), $event, $request->boolean('sync_to_google'));
            return response()->json(['status' => 'ok']);
        } catch (InvalidArgumentException $e) {
            Log::error("バリデーションエラー: {$e->getMessage()}");
            return response()->json(['error_msg' => $e->getMessage()], 422);
        } catch (Exception $e) {
            Log::info("---- Error Handling start msg:{$e->getMessage()}------");
            return response()->json(['error_msg' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $request->all();
            $data['id'] = $id;
            $data['user_id'] = Auth::id();
            $event = $this->eventService->updateEvent($data);
            $this->eventService->syncWithGoogleCalendar(auth()->user(), $event, $request->boolean('sync_to_google'));
            return response()->json(['status' => 'updated']);
        } catch (InvalidArgumentException $e) {
            Log::error("バリデーションエラー: {$e->getMessage()}");
            return response()->json(['error_msg' => $e->getMessage()], 422);
        } catch (Exception $e) {
            Log::error("更新エラー: {$e->getMessage()}");
            return response()->json(['error_msg' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $event = $this->eventService->getEventById($id);
            // Google カレンダー連携削除[連携済みの場合]
            $this->eventService->deleteFromGoogleCalendar(auth()->user(), $event);
            $this->eventService->deleteEvent($id);
            return redirect()->route('events.index')->with('success', 'イベントを削除しました');
        } catch (Exception $e) {
            Log::error("削除エラー: {$e->getMessage()}");
            return redirect()->back()->with('error', '削除に失敗しました');
        }
    }
}