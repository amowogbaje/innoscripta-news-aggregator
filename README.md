# 📰 Laravel News Aggregator

A modern, modular **News Aggregation API** built with Laravel that fetches, normalizes, and stores real-time news from multiple trusted sources like **The Guardian**, **The New York Times**, and **NewsAPI**.

> Designed for scalability, clean architecture, and extensibility — this project exemplifies premium Laravel engineering principles.

---

## 🚀 Features

* 🔁 Scheduled synchronization from multiple news sources
* 🧩 Adapter pattern for source-specific integrations
* 🧠 Intelligent duplicate detection
* 🗂 Category, Author, and Publisher management
* 🧵 Modular, service-driven architecture (`NewsSyncService`)
* ⚡ RESTful API with filtering, pagination, and rate limiting
* 💾 Robust error handling and transactional consistency
* 🧱 Eloquent Resources for clean, structured JSON responses

---

## 🧰 Tech Stack

| Component           | Technology               |
| ------------------- | ------------------------ |
| Framework           | Laravel 11.x             |
| Language            | PHP 8.2+                 |
| HTTP Client         | Guzzle                   |
| Database            | MySQL / PostgreSQL       |
| Scheduler           | Laravel Scheduler / Cron |
| Testing             | PHPUnit                  |
| API Auth (optional) | Laravel Sanctum          |

---

## 🧩 Architecture Overview

```
app/
 ├── Console/
 │   └── Commands/NewsSyncCommand.php
 ├── Http/
 │   ├── Controllers/Api/ArticleController.php
 │   └── Resources/
 │       ├── ArticleResource.php
 │       ├── SourceResource.php
 │       └── PublisherResource.php
 ├── Models/
 │   ├── Article.php
 │   ├── Author.php
 │   ├── Category.php
 │   ├── Publisher.php
 │   └── Source.php
 ├── Services/
 │   └── News/
 │       ├── Adapters/
 │       │   ├── GuardianAdapter.php
 │       │   ├── NewsApiAdapter.php
 │       │   └── NytimesAdapter.php
 │       ├── Contracts/NewsSourceInterface.php
 │       └── NewsSyncService.php
 └── ...
```

---

## ⚙️ Installation

```bash
# 1️⃣ Clone the repository
git clone https://github.com/amowogbaje/innoscripta-news-aggregator.git
cd innoscripta-news-aggregator

# 2️⃣ Install dependencies
composer install

# 3️⃣ Configure environment
cp .env.example .env
php artisan key:generate

# 4️⃣ Set up your database
php artisan migrate --seed
```

---

## 🔑 Environment Configuration

In your `.env` file, add API credentials for your news providers:

```env
NEWSAPI_KEY=your_guardian_api_key
GUARDIAN_KEY=your_nytimes_api_key
NYTIMES_KEY=your_newsapi_key
```

And in `config/services.php`:

```php
'guardian' => ['key' => env('NEWS_GUARDIAN_KEY')],
'nytimes' => ['key' => env('NEWS_NYTIMES_KEY')],
'newsapi' => ['key' => env('NEWS_NEWSAPI_KEY')],
```

---

## 🧭 Usage

### ✅ Run a Sync Manually

```bash
php artisan news:sync guardian
php artisan news:sync nytimes
php artisan news:sync newsapi
```



### 🕒 Automatic Sync (Every 5 Minutes)

Add this to your `app/Console/Kernel.php`:

```php
$schedule->command('news:sync guardian')->everyFiveMinutes();
$schedule->command('news:sync nytimes')->everyFiveMinutes();
$schedule->command('news:sync newsapi')->everyFiveMinutes();
```

Or simply put

```php
$schedule->command('news:sync')->everyFiveMinutes();
```

---

## 🔍 API Endpoints

| Method | Endpoint             | Description                    |
| ------ | -------------------- | ------------------------------ |
| `GET`  | `/api/articles`      | List all articles (filterable) |
| `GET`  | `/api/articles/{id}` | View a single article          |
| `GET`  | `/api/sources`       | Get available sources          |
| `GET`  | `/api/categories`    | Get all categories             |

### Query Filters

```
/api/articles?q=apple&source=guardian&category=technology&author=john&from=2025-10-28&to=2025-10-29
```

Supports:

* `q` — Search in title, content, description
* `source` — Filter by source slug
* `category` — Filter by category slug
* `author` — Filter by author name
* `preferred_sources[]` — Filter by multiple sources/publishers

---

## 🔒 Rate Limiting

Configured via Laravel’s throttle middleware:

```php
Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('articles', [ArticleController::class, 'index']);
    Route::get('articles/{article}', [ArticleController::class, 'show']);
    Route::get('sources', [ArticleController::class, 'sources']);
    Route::get('categories', [ArticleController::class, 'categories']);
});
```

This limits requests to **60 per minute per IP**.

---

## 📈 Future Enhancements

* 🗞 Caching Layer for API responses
* 📬 Webhook / Event broadcasting
* 🕵🏽 Full-text search integration (e.g. Meilisearch / Scout)
* 🧾 Advanced analytics dashboard
* 🌍 Frontend dashboard with Next.js or Vue

---

## 👨🏽‍💻 Author

**Gideon Amowogbaje**
*Engineer | Laravel • NestJS*