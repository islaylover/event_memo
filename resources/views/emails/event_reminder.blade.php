<p>{{ $dto->user_name }} 様</p>
<p>以下のイベントが {{ $dto->minute_before_event }} 分後に始まります。</p>

<ul>
    <li><strong>イベント名：</strong>{{ $dto->event_name }}</li>
    <li><strong>開催日時：</strong>{{ $dto->event_date }}</li>
    <li><strong>タグ：</strong>{{ implode(', ', $dto->tags) }}</li>
</ul>

<p>お忘れなくご準備ください。</p>