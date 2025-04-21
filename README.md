## event_memo

Laravel 8（PHP 7.4） + Vue 2（2.7.x） + MySQL 構成で、クリーンアーキテクチャ／DDDを意識して構築したイベント管理アプリです。

学習目的で以下の要素を実装しています：

- Domain-Driven Design（DDD）の実践（Entity / ValueObject / Repository / DomainService等を導入）
- リポジトリパターンによる永続化の抽象化
- ユースケース層の分離（Service）
- DTOによるデータ伝達
- SendGridを用いたイベント前通知メール送信バッチ
- Google OAuth（Laravel Socialite）による認証機能
- PHPUnitによるテストコード

---

### ✅ 前提環境

- PHP 7.4.x
- MySQL 8.x
- Webサーバ（nginx / Apache）
- Node.js（v16推奨）
- SendGrid（またはPostfix/Sendmail等、メール送信が可能なMTA）

---

### 🔧 セットアップ手順

```bash
git clone https://github.com/yourname/event_memo.git
cd event_memo

cp .env.sample .env
php artisan key:generate

composer install

npm install
npm run dev

php artisan migrate


### 📬  メール通知バッチの実行

php artisan reminders:send

定期実行には以下のような crontab 登録が必要です：
* * * * * cd /path/to/project && php artisan reminders:send >> /dev/null 2>&1


### 🔐  Google OAuth 認証設定
.env に以下を追加してください：

GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=https://yourdomain.com/login/google/callback
