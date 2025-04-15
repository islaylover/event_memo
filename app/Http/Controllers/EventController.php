<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\EventService;
use App\Infrastructure\Eloquent\AlertIntervalEloquent;
use App\Infrastructure\Eloquent\TagEloquent;
use Exception;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    protected EventService $EventService;

    public function __construct(EventService $EventService)
    {
        $this->EventService = $EventService;
    }

    public function index()
    {
        $events = $this->EventService->getAllEventsWithRelations();
        return view('events.index', compact('events'));   
    }

    public function create()
    {
        // タグ一覧を渡す（将来的に）
        $tags = TagEloquent::all();
        return view('events.create', compact('tags'));
    }

    public function edit($id)
    {
        Log::info("== edit mode ==");
        $event = $this->EventService->getEventWithRelations($id);
        $tags = TagEloquent::all();
        /*
        Log::info("-- event data --");
        Log::info(var_export($event, true));
        Log::info("-- all tags --");
        Log::info(var_export($tags, true));
        */
        return view('events.edit', compact('event', 'tags'));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all(); // バリデーションまだならここでOK
            $data['user_id'] = Auth::id();
            if (!empty($data['new_tag_name']) && is_array($data['new_tag_name'])) {
                foreach ($data['new_tag_name'] as $newTagName) {
                    $trimmed = trim($newTagName);
                    if ($trimmed === '') continue;
                    $tag = TagEloquent::firstOrCreate(['name' => $trimmed]);
                    // tag_ids に追加（なければ配列初期化）
                    $data['tag_ids'][] = $tag->id;
                }
            }
            $this->EventService->createEvent($data);
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
            Log::info("updte post data...");
            Log::info(var_export($data, true));
            // 新規タグがある場合は処理（登録＋tag_idsに追加）
            if (!empty($data['new_tag_name']) && is_array($data['new_tag_name'])) {
                foreach ($data['new_tag_name'] as $newTagName) {
                    $trimmed = trim($newTagName);
                    if ($trimmed === '') continue;
                    $tag = TagEloquent::firstOrCreate(['name' => $trimmed]);
                    $data['tag_ids'][] = $tag->id;
                }
            }

            $this->EventService->updateEvent($data);

            return response()->json(['status' => 'updated']);
        } catch (InvalidArgumentException $e) {
            Log::error("バリデーションエラー: {$e->getMessage()}");
            return response()->json(['error_msg' => $e->getMessage()], 422);
        } catch (Exception $e) {
            \Log::error("更新エラー: {$e->getMessage()}");
            return response()->json(['error_msg' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Log::info("delete post data...");
            Log::info("id:".$id);
            $this->EventService->deleteEvent($id);
            return redirect()->route('events.index')->with('success', 'イベントを削除しました');
        } catch (Exception $e) {
            \Log::error("削除エラー: {$e->getMessage()}");
            return redirect()->back()->with('error', '削除に失敗しました');
        }
    }
}