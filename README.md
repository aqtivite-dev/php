# Aqtivite PHP SDK

Aqtivite REST API için resmi PHP SDK'sı.

## Gereksinimler

- PHP 8.4+
- Composer

## Kurulum

```bash
composer require aqtivite/php-sdk
```

## Hızlı Başlangıç

```php
use Aqtivite\Php\Aqtivite;

$client = new Aqtivite('client-id', 'client-secret');
$client->setAccount('kullanici@example.com', 'sifre');
$client->login();

$response = $client->user()->users()->get();
```

## Yapılandırma

### Test Modu

```php
$client = new Aqtivite('client-id', 'client-secret');
$client->testMode(); // api.test.aqtivite.com.tr adresine bağlanır
```

### Özel API Adresi

```php
$client->setBaseUrl('https://custom-api.example.com');
```

### Özel HTTP Transport

SDK varsayılan olarak Guzzle kullanır. İsterseniz `setTransport()` ile kendi HTTP transport'unuzu kullanabilirsiniz (ör: Laravel Http).

```php
use Aqtivite\Php\Contracts\HttpTransportInterface;
use Aqtivite\Php\Http\TransportResponse;

class LaravelTransport implements HttpTransportInterface
{
    public function send(string $method, string $url, array $options = []): TransportResponse
    {
        $response = Http::withHeaders($options['headers'] ?? [])
            ->send($method, $url, $options);

        return new TransportResponse(
            statusCode: $response->status(),
            body: $response->json() ?? [],
        );
    }
}

$client = new Aqtivite('client-id', 'client-secret');
$client->setTransport(new LaravelTransport());
```

## Kimlik Doğrulama

### Kullanıcı Adı / Şifre

```php
$client = new Aqtivite('client-id', 'client-secret');
$client->setAccount('kullanici@example.com', 'sifre');
$client->login();
```

### Mevcut Token ile Giriş

```php
$client = new Aqtivite('client-id', 'client-secret');
$client->setAccount('kullanici@example.com', 'sifre');
$client->setToken('access-token', 'refresh-token');
$client->login();
```

`login()` metodu sırasıyla:

1. Token geçerli mi kontrol eder (`GET /auth`)
2. Geçersizse `refresh_token` ile yeniler
3. Yenileme başarısızsa credential ile tekrar giriş yapar

### API Anahtarı (Yakında)

```php
$client->setApiKey('api-key', 'api-secret');
```

### Token Bilgisi

```php
$token = $client->getToken();
$token->accessToken;
$token->refreshToken;
$token->tokenType;
$token->expiresIn;
```

### Token Yenilendiğinde Bildirim

Token yenilendiğinde veya yeniden giriş yapıldığında callback tetiklenir. Bu sayede yeni token'ı kalıcı olarak saklayabilirsiniz.

```php
$client->onTokenRefresh(function (Token $token) {
    DB::table('oauth_tokens')->update([
        'access_token' => $token->accessToken,
        'refresh_token' => $token->refreshToken,
    ]);
});
```

## Kullanım

### Auth

```php
$client->me();      // Giriş yapan kullanıcı bilgisi
$client->logout();  // Oturumu kapat
```

### User Modülü

```php
// Kullanıcılar
$client->user()->users()->get();                                          // Listele
$client->user()->users()->get(filter: ['name' => 'mehmet']);              // Filtrele
$client->user()->users()->get(query: ['page' => 2, 'length' => 20]);    // Sayfalama
$client->user()->users()->find('mehmet.ogmen');                           // Detay
$client->user()->users()->events('mehmet.ogmen');                         // Kullanıcının etkinlikleri

// Etkinlikler
$client->user()->events()->get();                                         // Listele
$client->user()->events()->get(query: ['order' => 'price[desc]']);
$client->user()->events()->find('etkinlik-slug');        // Detay
$client->user()->events()->create([                      // Oluştur
    'event_category_id' => 1,
    'title' => 'Etkinlik Başlığı',
    'started_at' => '2025-01-08T23:00:00.000+03:00',
    'ended_at' => '2025-01-09T00:00:00.000+03:00',
    'is_online' => 'false',
    'location_id' => 1,
    'is_pricable' => 'false',
    'person_capacity' => 0,
    'is_commentable' => 'true',
]);

// Etkinlik Kategorileri
$client->user()->eventCategories()->get();
$client->user()->eventCategories()->find(1);

// Seanslar
$client->user()->occurrences()->get(filter: ['event_id' => 6999]);
$client->user()->occurrences()->find(166);

// Organizatörler
$client->user()->organizers()->get();
$client->user()->organizers()->find('aqtivite');

// Gönderiler
$client->user()->posts()->get();
$client->user()->posts()->create([
    'content' => 'Gönderi içeriği',
    'photos' => ['/path/to/photo.jpg'],
]);

// Arama & Ağ
$client->user()->search('aqtivite');
$client->user()->network();
```

### Common Modülü

```php
// Mekanlar
$client->common()->venues()->get();
$client->common()->venues()->find(1);

// Salonlar
$client->common()->halls()->get();
$client->common()->halls()->find(1);

// Para Birimleri
$client->common()->currencies()->get();

// Bölgeler
$client->common()->regions()->get();
$client->common()->regions()->find(1);

// Ülkeler
$client->common()->countries()->get();
$client->common()->countries()->find(1);

// İller
$client->common()->provinces()->get();
$client->common()->provinces()->find(1);

// İlçeler
$client->common()->districts()->get(filter: ['province_id' => 3, 'name' => 'ala']);
$client->common()->districts()->get(                    // Filter + sayfalama
    filter: ['province_id' => 3],
    query: ['page' => 1],
);
$client->common()->districts()->find(1);

// Mahalleler
$client->common()->neighborhoods()->get();
$client->common()->neighborhoods()->find(1);
```

## Response Yapısı

### Standart Response

```php
$response = $client->user()->users()->find('mehmet.ogmen');

$response->successful();       // true
$response->failed();           // false
$response->data;               // Veri (array veya object)
$response->elapsedTime;        // Süre
```

### Sayfalanmış Response

```php
$response = $client->user()->users()->get();

$response->data;               // Veri listesi
$response->total;              // Toplam kayıt
$response->currentPage;        // Mevcut sayfa
$response->lastPage;           // Son sayfa
$response->from;               // Başlangıç
$response->to;                 // Bitiş
$response->hasNextPage();      // Sonraki sayfa var mı?
$response->hasPreviousPage();  // Önceki sayfa var mı?
```

## Hata Yönetimi

```php
use Aqtivite\Php\Exceptions\AuthenticationException;
use Aqtivite\Php\Exceptions\ApiException;
use Aqtivite\Php\Exceptions\AqtiviteException;

try {
    $client->login();
    $response = $client->user()->users()->get();
} catch (AuthenticationException $e) {
    // Kimlik doğrulama hatası
    echo $e->getMessage();
} catch (ApiException $e) {
    // API hatası
    echo $e->getMessage();
    echo $e->getCode();       // HTTP hata kodu
    echo $e->errorType;       // Hata tipi (ör: "app")
} catch (AqtiviteException $e) {
    // Genel SDK hatası
    echo $e->getMessage();
}
```

## Lisans

MIT
