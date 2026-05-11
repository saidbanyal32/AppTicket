# Tutorial Instalasi APP Ticketing System

Panduan ini menjelaskan cara menjalankan aplikasi ticketing berbasis Laravel di komputer lokal atau server development.

## 1. Kebutuhan Sistem

Pastikan perangkat sudah memiliki:

- PHP 8.3 atau lebih baru
- Composer
- Node.js dan npm
- PostgreSQL
- Ekstensi PHP umum untuk Laravel: `pdo`, `pdo_pgsql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `curl`

Cek versi:

```bash
php -v
composer --version
node -v
npm -v
psql --version
```

## 2. Ambil Source Code

Masuk ke folder web/development, lalu clone atau salin project ini.

```bash
cd /path/ke/folder/www
git clone <url-repository> AppTicket
cd AppTicket
```

Jika source code sudah tersedia, cukup masuk ke folder project:

```bash
cd AppTicket
```

## 3. Install Dependency Backend

```bash
composer install
```

Untuk server production, gunakan:

```bash
composer install --no-dev --optimize-autoloader
```

## 4. Install Dependency Frontend

```bash
npm install
```

## 5. Buat Database PostgreSQL

Login ke PostgreSQL, lalu buat database baru.

```bash
psql -U postgres
```

Di dalam prompt PostgreSQL:

```sql
CREATE DATABASE "TiketDB";
\q
```

Nama database boleh diganti, tetapi harus disesuaikan juga di file `.env`.

## 6. Buat File Environment

Project ini membutuhkan file `.env`. Jika sudah ada `.env` dari server lama, salin file tersebut ke root project. Jika belum ada, buat file `.env` baru dengan isi dasar berikut:

```env
APP_NAME="APP Ticketing System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=TiketDB
DB_USERNAME=postgres
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

Sesuaikan bagian berikut dengan konfigurasi komputer atau server:

- `APP_URL`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

## 7. Generate Application Key

```bash
php artisan key:generate
```

## 8. Jalankan Migrasi dan Seeder

Migrasi akan membuat tabel aplikasi, sedangkan seeder akan membuat data master, role, permission, SLA, kategori ticket, setting awal, dan akun administrator.

```bash
php artisan migrate --seed
```

Jika ingin mengulang database dari awal di environment development:

```bash
php artisan migrate:fresh --seed
```

Perintah `migrate:fresh` akan menghapus semua tabel dan data lama.

## 9. Buat Storage Link

Aplikasi menggunakan folder storage untuk file upload, attachment ticket, avatar, dan asset perusahaan.

```bash
php artisan storage:link
```

Jika link sudah ada, perintah ini boleh dilewati.

## 10. Build Asset Frontend

Untuk development:

```bash
npm run dev
```

Untuk production atau testing tanpa Vite dev server:

```bash
npm run build
```

## 11. Jalankan Aplikasi

Buka terminal pertama untuk Laravel:

```bash
php artisan serve
```

Secara default aplikasi berjalan di:

```text
http://127.0.0.1:8000
```

Jika ingin memakai port lain:

```bash
php artisan serve --host=127.0.0.1 --port=8001
```

Jika menggunakan `npm run dev`, biarkan terminal Vite tetap berjalan selama development.

## 12. Jalankan Queue Worker

Project menggunakan konfigurasi queue database. Jalankan worker di terminal terpisah:

```bash
php artisan queue:work
```

Untuk development, bisa juga memakai script bawaan Composer yang menjalankan server, queue, log, dan Vite bersamaan:

```bash
composer run dev
```

## 13. Login Pertama

Setelah seeder berhasil dijalankan, gunakan akun administrator berikut:

```text
Username: admin
Email: admin@zainerp.local
Password: password
```

Setelah berhasil login, segera ubah password dari menu profile atau manajemen user.

## 14. Perintah Maintenance

Clear cache konfigurasi:

```bash
php artisan optimize:clear
```

Cache ulang konfigurasi untuk production:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Jalankan test:

```bash
php artisan test
```

Format kode PHP:

```bash
./vendor/bin/pint
```

## 15. Catatan Deployment Production

Untuk production, gunakan konfigurasi berikut:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-aplikasi-anda.com
```

Lalu jalankan:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Pastikan web server mengarah ke folder `public`, bukan root project.

Contoh document root:

```text
/path/ke/AppTicket/public
```

Folder berikut harus bisa ditulis oleh web server:

```text
storage
bootstrap/cache
```

## 16. Troubleshooting

Jika muncul error database connection:

- Pastikan PostgreSQL aktif.
- Pastikan database sudah dibuat.
- Pastikan `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` benar.
- Jalankan `php artisan config:clear` setelah mengubah `.env`.

Jika asset CSS atau JavaScript tidak muncul:

- Jalankan `npm install`.
- Jalankan `npm run dev` untuk development atau `npm run build` untuk production.
- Pastikan `APP_URL` sesuai alamat aplikasi.

Jika upload file tidak bisa diakses:

- Jalankan `php artisan storage:link`.
- Pastikan folder `storage` bisa ditulis oleh web server.

Jika login admin gagal:

- Pastikan sudah menjalankan `php artisan migrate --seed`.
- Coba ulangi seeder dengan `php artisan db:seed`.
- Pastikan akun menggunakan username `admin` atau email `admin@zainerp.local`.
