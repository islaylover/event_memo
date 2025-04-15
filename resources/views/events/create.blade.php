@extends('layouts.app')

@section('header')
    <h2 class="text-lg font-medium text-gray-900">
    <a href="{{ route('events.index') }}" class="text-blue-600 hover:underline">← 一覧へ戻る</a>
    【イベント登録】
    </h2>
@endsection

@section('content')
<div id="app">
    <event-form
        :available-tags='@json($tags)'
    ></event-form>
</div>
@endsection