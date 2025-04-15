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
  
        <!-- タグID（仮：チェックボックス） -->
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
          impression: '',
          alert_intervals: [{ minute_before_event: '' }],
          tag_ids: [],
          new_tag_name: ['']
        },
        errorMessage: ''
      };
    },
    mounted() {
      console.log("初期イベント:", this.initialEvent);
      console.log("タグ:", this.availableTags);
      if (this.initialEvent) {
        this.form = {
          id: this.initialEvent.id,
          name: this.initialEvent.name,
          event_date: this.initialEvent.event_date.slice(0, 16),
          impression: this.initialEvent.impression || '',
          alert_intervals: this.initialEvent.alert_intervals ?? [],
          tag_ids: this.initialEvent.tags.map(t => t.id),
          new_tag_name: []
        };
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
        return `${yyyy}-${mm}-${dd}T${hh}:${min}`; // ← タイムゾーン補正済み
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