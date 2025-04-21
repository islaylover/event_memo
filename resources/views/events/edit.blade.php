@extends('layouts.app')

@section('header')
    <h2 class="text-lg font-medium text-gray-900">
    <a href="{{ route('events.index') }}" class="text-blue-600 hover:underline">← 一覧へ戻る</a>
    【イベント編集】
    </h2>
@endsection

@section('content')
<div id="app">
    <event-form
        :initial-event='@json($event)'
        :available-tags='@json($tags)'
        mode="edit"
    ></event-form>
</div>
@endsection
