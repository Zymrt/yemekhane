# Yemekhane Backend

Bu depo, belediye yemekhane otomasyonu için hazırlanan Laravel tabanlı REST API'yi içerir. Son değişikliklerle birlikte kimlik doğrulama yapısı JWT'den çerez tabanlı oturum jetonlarına taşındı. Aşağıdaki adımlar yeni sistemi yerel ortamınıza veya sunucunuza uygularken yol gösterecektir.

## Gereksinimler

- PHP 8.1+
- Composer 2+
- Node.js 18+ (ön-yapılandırılmış ön yüzü derlemek için)
- MongoDB 5+ (kimlik doğrulama ve uygulama verileri için)

## Kurulum

1. **Kaynak kodunu güncelleyin**
   ```bash
   git pull
   ```
2. **PHP bağımlılıklarını yükleyin**
   ```bash
   composer install
   ```
3. **Env dosyanızı oluşturun**
   ```bash
   cp .env.example .env
   ```
4. **Uygulama anahtarını üretin**
   ```bash
   php artisan key:generate
   ```
5. **MongoDB bağlantı ayarlarını yapın**
   `.env` dosyanızda aşağıdaki alanları güncellediğinizden emin olun:
   ```env
   DB_CONNECTION=mongodb
   DB_HOST=127.0.0.1
   DB_PORT=27017
   DB_DATABASE=belediye_yemekhane
   DB_USERNAME= # gerekiyorsa doldurun
   DB_PASSWORD= # gerekiyorsa doldurun
   ```
6. **Token sürelerini yapılandırın (isteğe bağlı)**
   Yeni oturum sistemi `config/token.php` dosyasındaki TTL değerlerini kullanır. Varsayılanlar sizin için uygunsa değiştirmeniz gerekmez; aksi durumda `.env` dosyanıza aşağıdaki anahtarları ekleyin:
   ```env
   ACCESS_TOKEN_TTL=60    # dakika
   REFRESH_TOKEN_TTL=7    # gün
   ```
7. **Önbellek temizleme**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

## Yeni oturum sistemi nasıl çalışır?

- Giriş yapan her kullanıcı için MongoDB'de `session_tokens` koleksiyonunda bir kayıt oluşturulur.
- Yanıtla birlikte tarayıcıya `access_token` ve `refresh_token` isimli HttpOnly çerezler gönderilir.
- Her API isteği `token.auth` ara katmanıyla korunur ve çerezlerdeki jetonlar otomatik kontrol edilir.
- `POST /api/refresh` uç noktası, kullanıcı etkileşimde olduğu sürece jetonları döndürür ve çerezleri yeniler.
- Çıkış işlemi (`POST /api/logout`) hem veritabanı kaydını siler hem de çerezleri temizler.

## Ön yüz (Nuxt) için yapılması gerekenler

- Tüm `fetch`/`$fetch` çağrılarının `credentials: 'include'` parametresiyle gönderildiğinden emin olun.
- Sunucu tarafında CORS ayarlarına `Access-Control-Allow-Credentials: true` eklenmiş olmalıdır.
- Tarayıcıda eski JWT kalıntılarını temizlemek için localStorage veya sessionStorage üzerinde saklanan `token` benzeri anahtarlar kaldırılmalıdır.

## Sorun giderme

| Problem | Çözüm |
| --- | --- |
| Oturum açtıktan sonra çerezler oluşmuyor | Alan adınız HTTPS kullanmıyorsa `APP_URL` değerinin doğru olduğundan emin olun ve tarayıcı geliştirici araçlarında çerez engellerini kontrol edin. |
| İstekler 401 dönüyor | `session_tokens` koleksiyonunda kaydın oluştuğunu doğrulayın ve istemci isteğinin çerez gönderdiğinden emin olun. |
| TTL değerleri beklenenden kısa | `.env` dosyanızdaki `ACCESS_TOKEN_TTL` ve `REFRESH_TOKEN_TTL` değerlerini artırın ve `php artisan config:clear` çalıştırın. |

## Koleksiyon yönetimi

Yeni sisteme geçerken eski JWT tabanlı kayıtlarınızı temizlemek için MongoDB kabuğunda aşağıdaki komutu çalıştırabilirsiniz:
```javascript
use belediye_yemekhane;
db.session_tokens.drop();
```
Bu komut mevcut oturumları siler; kullanıcılar yeniden giriş yapmak zorunda kalır.

## Testler

Varsayılan Laravel testleri aşağıdaki komutla çalıştırılabilir:
```bash
php artisan test
```
(Çalıştırmadan önce `vendor/` bağımlılıklarının kurulu olduğundan emin olun.)

