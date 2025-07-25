<template>
    <div class="p-4 bg-white shadow rounded">

      <form @submit.prevent="submitForm">
        <h2 class="text-lg font-medium text-gray-900 flex items-center gap-4">

        </h2>
        <div v-if="errorMessage" class="mb-4 text-red-600 font-semibold">
          {{ errorMessage }}
        </div>

        <!-- イベント名 -->
        <div class="mb-4">
          <label class="block font-semibold mb-1">イベント名</label>
          <input v-model="form.name" type="text" class="w-full border p-2 rounded" required />
        </div>
  
        <!-- 日時 -->
        <div class="mb-4">
          <label class="block font-semibold mb-1">日時</label>
          <input v-model="form.event_date" type="datetime-local" class="w-full border p-2 rounded" required />
        </div>
  
        <!-- 終了日時 -->
        <div class="mb-4">
          <label class="block font-semibold mb-1">終了日時</label>
          <input v-model="form.event_end_date" type="datetime-local" class="w-full border p-2 rounded" />
        </div>

        <!-- メモ -->
        <div class="mb-4">
          <label class="block font-semibold mb-1">メモ</label>
          <textarea v-model="form.memo" class="w-full border p-2 rounded"></textarea>
        </div>

        <!-- 印象 -->
        <div class="mb-4">
          <label class="block font-semibold mb-1">印象</label>
          <textarea v-model="form.impression" class="w-full border p-2 rounded"></textarea>
        </div>
  
        <!-- アラート通知（動的行） -->
        <div class="mb-4">
          <label class="block font-semibold mb-1">通知設定（分前）</label>
          <div v-for="(interval, index) in form.alert_intervals" :key="index" class="flex items-center mb-2">
            <input
              type="number"
              inputmode="numeric"
              v-model="interval.minute_before_event"
              class="w-full border p-2 rounded"
              placeholder="例: 10"
            />
            <button type="button" class="ml-2 text-red-500" @click="removeAlertInterval(index)">×</button>
          </div>
          <button type="button" @click="addAlertInterval" class="text-blue-500">＋追加</button>
        </div>
  
        <!-- タグID（チェックボックス） -->
        <div class="mb-4">
          <label class="block font-semibold mb-1">タグ</label>
          <div v-for="tag in availableTags" :key="tag.id" class="flex items-center mb-1">
            <input type="checkbox" :value="tag.id" v-model="form.tag_ids" class="mr-2" />
            <span>{{ tag.name }}</span>
          </div>
        </div>
  
        <!-- 新しいタグ追加 -->
        <div class="mb-4">
          <label class="block font-semibold mb-1">新しいタグ</label>
          <div v-for="(tag, index) in form.new_tag_name" :key="'newtag'+index" class="flex items-center mb-2">
            <input v-model="form.new_tag_name[index]" type="text" class="w-full border p-2 rounded" />
            <button type="button" class="ml-2 text-red-500" @click="removeNewTag(index)">×</button>
          </div>
          <button type="button" @click="addNewTag" class="text-blue-500">＋新規タグ追加</button>
        </div>

        <!-- Google Calendar連携 -->
        <label>
          <input type="checkbox" v-model="form.sync_to_google"/>
          Google Calendarにも登録する
        </label>  
        <p v-if="isAlreadySynced" class="text-sm text-gray-500">
        ※このイベントはすでにGoogle Calendarと同期されています。チェックを外すとカレンダーから削除されます。
        </p>
        <!-- 送信 -->
        <div class="mt-6">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                {{ mode === 'edit' ? 'イベント編集' : 'イベント登録' }}する
            </button>
        </div>
      </form>
    </div>
  </template>
  
  <script>
  export default {
    props: {
      initialEvent: {
        type: Object,
        default: null
      },
      availableTags: {
        type: Array,
       default: () => []
      },
      mode: {
        type: String,
        default: 'create'
      }
    },
    data() {
      return {
        form: {
          id: null,
          name: '',
          event_date: this.formatNow(),
          event_end_date: '',
          memo: '',
          impression: '',
          alert_intervals: [{ minute_before_event: '' }],
          tag_ids: [],
          new_tag_name: [''],
          sync_to_google: false
        },
        isAlreadySynced: false,
        errorMessage: ''
      };
    },
    mounted() {
      //console.log("初期イベント:", this.initialEvent);
      if (this.initialEvent) {
        this.form = {
          id: this.initialEvent.id,
          name: this.initialEvent.name,
          event_date: this.initialEvent.eventDate.slice(0, 16),
          event_end_date: (this.initialEvent.eventEndDate) ? this.initialEvent.eventEndDate.slice(0, 16) : '',
          memo: this.initialEvent.memo || '',          
          impression: this.initialEvent.impression || '',
          alert_intervals: this.initialEvent.alertIntervals ?? [],
          tag_ids: this.initialEvent.tagIds ?? [],
          new_tag_name: [],
          sync_to_google: !!this.initialEvent.googleEventId
        };
        this.isAlreadySynced = !!this.initialEvent.googleEventId; // ← true なら連携済
      }
    },
    methods: {
      formatNow() {
        const now = new Date();
        const yyyy = now.getFullYear();
        const mm = String(now.getMonth() + 1).padStart(2, '0');
        const dd = String(now.getDate()).padStart(2, '0');
        const hh = String(now.getHours()).padStart(2, '0');
        const min = String(now.getMinutes()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd} ${hh}:${min}`; // ← タイムゾーン補正
      },
      addAlertInterval() {
        this.form.alert_intervals.push({ minute_before_event: '' });
      },
      removeAlertInterval(index) {
        this.form.alert_intervals.splice(index, 1);
      },
      addNewTag() {
        this.form.new_tag_name.push('');
      },
      removeNewTag(index) {
        this.form.new_tag_name.splice(index, 1);
      },
      submitForm() {
        if (this.form.event_date.includes('T')) {
          this.form.event_date = this.form.event_date.replace('T', ' ');
        }
        if (this.form.event_end_date.includes('T')) {
          this.form.event_end_date = this.form.event_end_date.replace('T', ' ');
        }
        // 通知時間の重複を排除
        /*
        const seen = new Set();
        this.form.alert_intervals = this.form.alert_intervals.filter(
          item => {
            if (seen.has(item.minute_before_event)) return false;
            seen.add(item.minute_before_event);
            return true;
          }
        );
        */
        const url = this.mode === 'edit'
          ? `/events/${this.form.id}`
          : '/events';
        const method = this.mode === 'edit' ? 'put' : 'post';
        axios[method](url, this.form)
          .then(() => {
            alert('保存しました');
            window.location.href = '/events';
          })
          .catch((error) => {
            console.error(error);
            this.errorMessage = error.response?.data?.error_msg || '登録エラー';
          });
      }
    }
  };
  </script>