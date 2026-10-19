# Aqtivite PHP SDK

Aqtivite REST API için resmi PHP SDK'sı.

## Gereksinimler

- PHP 8.4+
- Composer

## Kurulum

```bash
composer require aqtivite/php
```

## Hızlı Başlangıç

```php
use Aqtivite\Php\Aqtivite;

$client = new Aqtivite('client-id', 'client-secret');

$response = $client->user()->events()->get();
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
            headers: array_map(fn (array $values) => implode(', ', $values), $response->headers()),
        );
    }
}

$client = new Aqtivite('client-id', 'client-secret');
$client->setTransport(new LaravelTransport());
```

Transport, cevap başlıklarını `headers` ile iletmelidir. SDK eksik scope'u `WWW-Authenticate` başlığından tanır.

## Kimlik Doğrulama

SDK, API'ye OAuth2 `client_credentials` grant'i ile bağlanır. Her servis API'de kendi client'ı ile tanımlıdır; token bir kullanıcıyı değil client'ın kendisini temsil eder.

Hangi scope'ların verileceği API'de, client'ın kaydında tanımlıdır. Scope belirtmeyen client'ın token'ı client'a tanınan bütün scope'ları taşır; SDK tarafında liste tutmak gerekmez.

Token'ı belirli scope'larla daraltmak için scope listesi verilebilir. Listede olup client'a tanınmamış scope'lar token'a yazılmaz.

```php
$client = new Aqtivite('client-id', 'client-secret', ['admin:venues:read']);

$client->setScopes(['admin:tickets:read']);
```

Token ilk istekte alınır. Şu durumlarda yenisi alınır:

- Süresinin dolmasına 60 saniye kaldığında.
- API `401` döndürdüğünde.
- API `403` ile `insufficient_scope` döndürdüğünde. Client'a sonradan tanınan bir scope, eski token'da bulunmaz; SDK yeni token alıp isteği bir kez tekrarlar.

Token'ı elle almak için:

```php
$token = $client->issueToken();
```

### Mevcut Token ile Başlama

Daha önce saklanmış bir token yüklenebilir. Süresi dolmuşsa SDK yenisini alır.

```php
$client->setToken('access-token', new DateTimeImmutable('2026-10-05 12:00:00'));
```

### Token Bilgisi

```php
$token = $client->getToken();
$token->accessToken;
$token->expiresAt;   // DateTimeImmutable|null
$token->tokenType;
$token->scopes;       // API'nin token cevabında bildirdiği scope'lar
$token->isExpired();
```

### Yeni Token Alındığında Bildirim

Yeni bir token alındığında callback tetiklenir. Bu sayede token kalıcı olarak saklanabilir.

```php
$client->onTokenIssued(function (Token $token) {
    Cache::put('aqtivite_token', [
        'access_token' => $token->accessToken,
        'expires_at' => $token->expiresAt?->format(DATE_ATOM),
    ]);
});
```

## Kullanım

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

// Etkinlik Kategorileri
$client->user()->eventCategories()->get();
$client->user()->eventCategories()->find(1);

// Seanslar
$client->user()->occurrences()->get(filter: ['event_id' => 6999]);
$client->user()->occurrences()->find(166);

// Organizatörler
$client->user()->organizers()->get();
$client->user()->organizers()->find('aqtivite');

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
use Aqtivite\Php\Exceptions\MissingScopeException;

try {
    $response = $client->user()->users()->get();
} catch (AuthenticationException $e) {
    // Kimlik doğrulama hatası
    echo $e->getMessage();
} catch (MissingScopeException $e) {
    // Yeni token'la da eksik kalan scope'lar
    echo implode(' ', $e->scopes);
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
