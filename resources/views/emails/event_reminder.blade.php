<p>{{ $dto->userName }} 様</p>
<p>以下のイベントが {{ $dto->minuteBeforeEvent }} 分後に始まります。</p>

<ul>
    <li><strong>イベント名：</strong>{{ $dto->eventName }}</li>
    <li><strong>開始日時：</strong>{{ $dto->eventDate }}</li>
    <li><strong>終了日時：</strong>{{ $dto->eventEndDate }}</li>
    <li><strong>メモ</strong>{{ $dto->memo }}</li>
    <li><strong>タグ：</strong>{{ implode(', ', $dto->tags) }}</li>
</ul>

<p>お忘れなくご準備ください。</p>