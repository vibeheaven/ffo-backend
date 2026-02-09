# Aethron API - Proje Mimarisi ve Geliştirme Rehberi

## 📋 Genel Bakış

Bu proje **Laravel 12** tabanlı bir REST API projesidir ve **Domain-Driven Design (DDD)** mimarisi kullanmaktadır. JWT authentication, credit sistemi, payment entegrasyonu ve request logging özellikleri içerir.

## 🏗️ Mimari Yapı

### 1. Domain-Driven Design (DDD) Yapısı

```
app/
├── Domain/                    # Domain Logic (Business Rules)
│   ├── {DomainName}/
│   │   ├── Actions/          # Business Logic (Use Cases)
│   │   ├── DataTransferObjects/  # DTOs (Data Transfer Objects)
│   │   ├── Models/           # Eloquent Models
│   │   └── Repositories/     # Repository Interfaces
│   └── ...
├── Infrastructure/            # Infrastructure Layer
│   └── Repositories/         # Repository Implementations
├── Http/                      # Presentation Layer
│   ├── Controllers/          # API Controllers
│   ├── Middleware/           # HTTP Middleware
│   └── Requests/             # Form Request Validators
└── Services/                  # External Services
```

### 2. Pattern'ler

- **Action Pattern**: Business logic `Domain/{Domain}/Actions/` klasöründe
- **DTO Pattern**: Veri transferi için `DataTransferObjects` kullanılıyor
- **Repository Pattern**: Veritabanı erişimi için (opsiyonel, Post domain'inde örnek var)
- **Service Layer**: Harici servisler için (LemonSqueezy, OTP vb.)

## 📁 Dosya Yapısı ve Standartlar

### Controller Yapısı

```php
<?php

namespace App\Http\Controllers\Api\{Domain};

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use OpenApi\Annotations as OA;

class {Domain}Controller extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *     path="/api/{endpoint}",
     *     summary="Endpoint Description",
     *     tags={"TagName"},
     *     security={{"bearerAuth":{}}},  // Eğer auth gerekiyorsa
     *     @OA\Response(...)
     * )
     */
    public function methodName(Request $request): JsonResponse
    {
        // Implementation
    }
}
```

### Action Yapısı

```php
<?php

namespace App\Domain\{Domain}\Actions;

class {ActionName}Action
{
    public function execute({DTO} $data): {ReturnType}
    {
        // Business logic burada
        return $result;
    }
}
```

### DTO Yapısı

```php
<?php

namespace App\Domain\{Domain}\DataTransferObjects;

class {Domain}DTO
{
    public function __construct(
        public readonly string $field1,
        public readonly ?string $field2 = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            field1: $data['field1'],
            field2: $data['field2'] ?? null,
        );
    }
}
```

### Request Validation Yapısı

```php
<?php

namespace App\Http\Requests\{Domain};

use Illuminate\Foundation\Http\FormRequest;

class {Action}Request extends FormRequest
{
    public function authorize(): bool
    {
        return true; // veya auth kontrolü
    }

    public function rules(): array
    {
        return [
            'field' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'field.required' => 'Türkçe hata mesajı',
        ];
    }
}
```

## 🔐 Authentication & Authorization

### JWT Authentication
- Guard: `auth:api`
- Token'lar JWT ile yönetiliyor
- Custom claim: `u_hash` (IP + UserAgent hash'i)

### Middleware Sırası
1. `ForceJsonResponse` - Tüm response'ları JSON'a zorlar
2. `LogHttpActivity` - Request'leri loglar
3. `SecureHeaders` - Güvenlik header'ları ekler
4. `SanitizeInput` - Input'ları temizler
5. `auth:api` - JWT authentication kontrolü
6. `EnsureJwtClaimsMatch` - Token'ın IP/UserAgent ile eşleşmesini kontrol eder
7. `CapacityLimitMiddleware` - Redis ile kapasite limiti (50 concurrent request)

## 📡 Response Formatı

### Başarılı Response
```json
{
    "status": "success",
    "data": {
        // Response data
    },
    "message": "Optional message"
}
```

### Hata Response
```json
{
    "status": "error",
    "message": "Hata mesajı",
    "exception": "Exception class (sadece debug mode)",
    "trace": [], // (sadece debug mode)
    "code": 400
}
```

## 🛣️ Route Yapısı

### Route Grupları

```php
// Public routes (throttle: 30 req/min)
Route::prefix('auth')->middleware(['throttle:30,1', CapacityLimitMiddleware::class])->group(function () {
    // Public auth routes
});

// Protected routes (JWT required)
Route::middleware('auth:api')->group(function () {
    Route::middleware([EnsureJwtClaimsMatch::class])->group(function () {
        // Protected routes
    });
});

// Webhook routes (public, signature verification)
Route::post('webhooks/{service}', [Controller::class, 'webhook']);
```

### Route Örnekleri

```php
// GET endpoint
Route::get('{resource}', [Controller::class, 'index']);

// POST endpoint
Route::post('{resource}', [Controller::class, 'store']);

// PUT/PATCH endpoint
Route::put('{resource}/{id}', [Controller::class, 'update']);

// DELETE endpoint
Route::delete('{resource}/{id}', [Controller::class, 'destroy']);
```

## 📊 Veritabanı Yapısı

### Mevcut Tablolar
- `users` - Kullanıcı bilgileri (credits kolonu var)
- `credit_transactions` - Kredi işlem geçmişi (UUID primary key)
- `request_logs` - API request logları (UUID primary key)
- `password_reset_tokens` - Şifre sıfırlama token'ları
- `sessions` - Session verileri

### Migration Pattern
```php
Schema::create('table_name', function (Blueprint $table) {
    $table->id(); // veya $table->uuid('id')->primary();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    // ... columns
    $table->timestamps();
});
```

## 🔧 Servisler

### LemonSqueezyService
- Payment checkout oluşturma
- Webhook signature verification
- Credit ekleme işlemleri

### OtpService
- OTP gönderme (webhook üzerinden)
- OTP doğrulama
- Cache tabanlı saklama (5 dakika TTL)

## 📝 Yeni Route Ekleme Adımları

### 1. Domain Oluşturma (Eğer yeni bir domain ise)

```bash
php artisan make:ddd {DomainName}
```

Bu komut şunları oluşturur:
- Domain klasör yapısı
- Model, DTO, Action, Controller, Repository

### 2. Manuel Olarak Ekleme (Önerilen)

#### Adım 1: Model Oluştur (Eğer gerekiyorsa)
```bash
php artisan make:model Domain/{Domain}/Models/{Model}
```

#### Adım 2: Migration Oluştur
```bash
php artisan make:migration create_{table_name}_table
```

#### Adım 3: DTO Oluştur
`app/Domain/{Domain}/DataTransferObjects/{Action}DTO.php`

#### Adım 4: Action Oluştur
`app/Domain/{Domain}/Actions/{Action}Action.php`

#### Adım 5: Request Validator Oluştur
```bash
php artisan make:request {Domain}/{Action}Request
```

#### Adım 6: Controller Method Ekle
`app/Http/Controllers/Api/{Domain}/{Domain}Controller.php`

#### Adım 7: Route Ekle
`routes/api.php` dosyasına uygun grup içine ekle

#### Adım 8: OpenAPI Dokümantasyonu Ekle
Controller method'una `@OA\*` annotation'ları ekle

## 🎯 Örnek: Yeni Route Ekleme Senaryosu

### Senaryo: Profile Update Endpoint'i

#### 1. DTO Oluştur
```php
// app/Domain/User/DataTransferObjects/UpdateProfileDTO.php
class UpdateProfileDTO {
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $phone = null,
        // ...
    ) {}
    
    public static function fromRequest(array $data): self {
        return new self(...);
    }
}
```

#### 2. Action Oluştur
```php
// app/Domain/User/Actions/UpdateProfileAction.php
class UpdateProfileAction {
    public function execute(User $user, UpdateProfileDTO $dto): User {
        $user->update($dto->toArray());
        return $user->fresh();
    }
}
```

#### 3. Request Oluştur
```php
// app/Http/Requests/User/UpdateProfileRequest.php
class UpdateProfileRequest extends FormRequest {
    public function rules(): array {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'unique:users,phone'],
        ];
    }
}
```

#### 4. Controller Method Ekle
```php
// app/Http/Controllers/Api/User/UserController.php
public function updateProfile(
    UpdateProfileRequest $request,
    UpdateProfileAction $action
): JsonResponse {
    $user = $action->execute(
        $request->user(),
        UpdateProfileDTO::fromRequest($request->validated())
    );
    
    return response()->json([
        'status' => 'success',
        'data' => ['user' => UserDTO::fromModel($user)->toArray()],
        'message' => 'Profile updated successfully.',
    ]);
}
```

#### 5. Route Ekle
```php
// routes/api.php
Route::middleware('auth:api')->group(function () {
    Route::middleware([EnsureJwtClaimsMatch::class])->group(function () {
        Route::put('profile', [UserController::class, 'updateProfile']);
    });
});
```

## 🔍 Önemli Notlar

1. **Tüm hatalar JSON döner** - `bootstrap/app.php` içinde ayarlanmış
2. **Request logging otomatik** - `LogHttpActivity` middleware ile
3. **Rate limiting** - Auth route'larında `throttle:30,1`
4. **Capacity limiting** - Redis ile 50 concurrent request
5. **JWT security** - IP/UserAgent hash kontrolü
6. **OpenAPI dokümantasyonu** - Swagger UI mevcut
7. **Türkçe hata mesajları** - Request validator'larda `messages()` metodu ile

## 📚 Kullanılan Paketler

- Laravel 12
- JWT Auth (`php-open-source-saver/jwt-auth`)
- Swagger/OpenAPI (`darkaonline/l5-swagger`)
- Laravel Sanctum (kurulu ama kullanılmıyor, JWT kullanılıyor)

## 🚀 Geliştirme Komutları

```bash
# Domain oluştur
php artisan make:ddd {DomainName}

# Migration oluştur
php artisan make:migration create_{table}_table

# Request oluştur
php artisan make:request {Domain}/{RequestName}

# Swagger dokümantasyonu güncelle
php artisan l5-swagger:generate
```

## 📋 Checklist: Yeni Route Ekleme

- [ ] Domain klasör yapısı oluşturuldu mu?
- [ ] Model oluşturuldu mu? (gerekirse)
- [ ] Migration oluşturuldu ve çalıştırıldı mı?
- [ ] DTO oluşturuldu mu?
- [ ] Action oluşturuldu mu?
- [ ] Request validator oluşturuldu mu?
- [ ] Controller method eklendi mi?
- [ ] Route eklendi mi? (doğru middleware grubunda mı?)
- [ ] OpenAPI annotation'ları eklendi mi?
- [ ] Türkçe hata mesajları eklendi mi?
- [ ] Response formatı standartlara uygun mu?
