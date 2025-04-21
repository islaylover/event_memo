@extends('layouts.app')

@section('header')
    <h2 class="text-lg font-medium text-gray-900">イベント一覧</h2>
@endsection

@section('content')
<div class="max-w-7xl mx-auto p-4">

    @if (session('status'))
        <div class="mb-4 text-green-600 font-semibold">
            {{ session('status') }}
        </div>
    @endif

    {{-- イベント登録リンク --}}
    <div class="flex justify-end mb-4">
        <a href="{{ route('events.create') }}"
           class="inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            ＋ イベントを登録
        </a>
    </div>


    @foreach ($events as $event)
        <div class="bg-white rounded shadow p-4 mb-4">
            <h3 class="text-xl font-bold">{{ $event->name }}</h3>
            <p class="text-sm text-gray-600">日時: {{ $event->eventDate }}</p>
            <p class="text-sm text-gray-600">印象: {{ $event->impression ?? 'なし' }}</p>

            <p class="mt-2 font-semibold">通知（分前）:</p>
            <ul class="ml-4 list-disc text-sm">
                @forelse ($event->alertIntervals as $interval)
                    <li>{{ $interval }} 分前</li>
                @empty
                    <li>なし</li>
                @endforelse
            </ul>

            <p class="mt-2 font-semibold">タグ:</p>
            <div class="flex flex-wrap gap-2">
                @forelse ($event->tags as $tag)
                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs">{{ $tag }}</span>
                @empty
                    <span class="text-gray-500 text-sm">なし</span>
                @endforelse
            </div>

            <div class="flex justify-end mt-4 space-x-4">
                <a href="{{ route('events.edit', $event->id) }}"
                   class="text-blue-600 hover:underline">編集</a>

                <form action="{{ route('events.destroy', $event->id) }}"
                      method="POST"
                      onsubmit="return confirm('本当に削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:underline">削除</button>
                </form>
            </div>
        </div>
    @endforeach

    @if (empty($events))
        <p class="text-gray-500">登録されたイベントはありません。</p>
    @endif

</div>
@endsection