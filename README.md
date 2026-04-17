# SEO Panel – AI-Powered Social Pixels & Analytics Manager

## 📌 Overview

**SEO Panel** is a powerful Laravel-based dashboard to manage and inject marketing **pixels and analytics tags** from various social platforms (e.g., Facebook, TikTok, Google, Snapchat, Twitter, Pinterest) dynamically based on admin settings. It also features **AI integrations** to generate content/plugins as part of SEO automation.

---

## 🚀 Features

* Facebook Meta Pixel integration
* TikTok Pixel integration
* Google Analytics & Google Tag Manager
* Snapchat Pixel
* Twitter (X) Ads Pixel
* Pinterest Tag
* Fully manageable from admin settings
* Pixel IDs stored dynamically in the database
* AI-powered plugins (via OpenAI or custom)
* Blogs
* Offers
* Items
* Contact Us
* Subscribes
* Jobs & Applications
* Multiple Database
* Sitemap
* Custom Pages

---

## ⚙️ Requirements

* PHP >= 8.2
* Laravel 12+
* MySQL
* Node.js + NPM

---

## 📂 .env Configuration

Make sure your `.env` file includes the following (example values):

```env
APP_NAME="SEO Panel"
APP_ENV=local
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

#to change lang ( ar - en - both )
APP_LANG=both
#api key
API_KEY=mW9h5nzgivvehulnOg7tugjRtiK8Kbf9
#tenants [ on - off]
APP_TENANTS_SETTING=on
#website URL
WEBSITE_URL=http://example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seo_panel
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=mycompound.developer@gmail.com
MAIL_PASSWORD=lbvrqgxfwuqsxylh
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="seo-panel@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 🛠️ Installation Steps

1. **Clone the repository:**

```bash
git clone https://github.com/Promp-Tech/seo-panel.git
cd seo-panel
```

2. **Install PHP dependencies:**

```bash
composer install
```

3. **Install Node dependencies:**

```bash
npm install && npm run dev
```

4. **Set up environment:**

```bash
cp .env.example .env
php artisan key:generate
```

5. **Run migrations and seeders:**

```bash
php artisan migrate --seed
```

6. **Run the queue worker (for AI and background tasks):**

```bash
php artisan queue:work
```

7. **Serve the application:**
   
```bash
php artisan serve
```
8. **Test Dashboard && website:**

## Dashboard
```bash
http://127.0.0.1:8000/admin
```
## Website
```bash
http://127.0.0.1:8000
```
---

## 🌎 For Login

* Email: admin@seo-panel.com
* Password: seo$2025@panel

---

## 🛸 For Test AI Use

* Name: Gemini
* Key: AIzaSyBLNwORmTKsg9xocGla7i3OCxGtYRAk2YY

## 🪐 Can Use Api With Postman To List Data

```bash
php artisan api-key:generate
```
* Collection: https://documenter.getpostman.com/view/25605546/2sB2x8Eqwa

## 🔍 Pixel Management

You can enable or disable any tracking pixel directly from the admin dashboard.

Supported Pixels:

| Pixel          | Setting Key        | Usage Example                         |
| -------------- |--------------------|---------------------------------------|
| Facebook Pixel | `facebook_pixel`   | `get_setting('facebook_pixel_id')`    |
| TikTok Pixel   | `tiktok_analytics` | `get_setting('tiktok_analytics_id')`  |
| Google GA      | `google_analytics` | `get_setting('google_analytics_id')`  |
| GTM            | `google_manager`   | `get_setting('google_manager_id')`    |
| Snapchat Pixel | `snapchat_pixel`   | `get_setting('snapchat_pixel_id')`    |
| Twitter Pixel  | `twitter_pixel`    | `get_setting('twitter_pixel_id')`     |
| Pinterest Tag  | `pinterest_tag`    | `get_setting('pinterest_tag_id')`     |

> These settings are automatically injected in your front-end views using Blade conditions.

Example Blade Snippet:

* Facebook Meta Pixel:

```blade
<!-- Facebook Meta Pixel -->
@if(get_setting('facebook_pixel') == 1)
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod ?
        n.callMethod.apply(n,arguments) : n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', "{{ get_setting('facebook_pixel_id') }}");
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id={{ get_setting('facebook_pixel_id') }}&ev=PageView&noscript=1"
    /></noscript>
@endif
```

* TikTok Pixel:

```blade
<!-- TikTok Pixel -->
@if(get_setting('tiktok_analytics') == 1)
    <script>
        !function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];
        ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"],
        ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};
        for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);
        ttq.load=function(e,n){var r="https://analytics.tiktok.com/i18n/pixel/events.js";
        var o=n&&n.partner;ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=r,ttq._t=ttq._t||{},ttq._t[e]=+new Date,
        ttq._o=ttq._o||{},ttq._o[e]=n||{};n=document.createElement("script");
        n.type="text/javascript",n.async=!0,n.src=r+"?sdkid="+e+"&lib="+t;
        e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(n,e)};
        ttq.load("{{ get_setting('tiktok_analytics_id') }}"); ttq.page();
    </script>
@endif
```

* Google Analytics:

```blade
<!-- Google Analytics -->
@if(get_setting('google_analytics') == 1)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ get_setting('google_analytics_id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', "{{ get_setting('google_analytics_id') }}");
    </script>
@endif
```

* Google Tag Manager:

```blade
<!-- Google Tag Manager -->
@if(get_setting('google_manager') == 1)
    <script>
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;
        j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;
        f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer', "{{ get_setting('google_manager_id') }}");
    </script>
@endif

```

* Snapchat Pixel:

```blade
<!-- Snapchat Pixel -->
@if(get_setting('snapchat_pixel') == 1)
    <script type='text/javascript'>
        (function(e,t,n){if(e.snaptr)return;var a=e.snaptr=function(){
        a.handleRequest?a.handleRequest.apply(a,arguments):a.queue.push(arguments)};
        a.queue=[];var s='script';r=t.createElement(s);r.async=!0;r.src=n;
        var u=t.getElementsByTagName(s)[0];u.parentNode.insertBefore(r,u);
        })(window,document,'https://sc-static.net/scevent.min.js');
        snaptr('init', "{{ get_setting('snapchat_pixel_id') }}");
        snaptr('track', 'PAGE_VIEW');
    </script>
@endif

```

* Twitter(X) Pixel:

```blade
<!-- Twitter Pixel -->
@if(get_setting('twitter_pixel') == 1)
    <script>
        !function(e,t,n,s,u,a){e.twq||(s=e.twq=function(){
        s.exe?s.exe.apply(s,arguments):s.queue.push(arguments)},
        s.version='1.1',s.queue=[],u=t.createElement(n),u.async=!0,
        u.src='https://static.ads-twitter.com/uwt.js',
        a=t.getElementsByTagName(n)[0],a.parentNode.insertBefore(u,a))}(window,document,'script');
        twq('init', "{{ get_setting('twitter_pixel_id') }}");
        twq('track', 'PageView');
    </script>
@endif

```

* Pinterest Tag:

```blade
<!-- Pinterest Tag -->
@if(get_setting('pinterest_tag') == 1)
    <script>
        !function(e){if(!window.pintrk){window.pintrk=function(){
        window.pintrk.queue.push(Array.prototype.slice.call(arguments))};
        var n=window.pintrk;n.queue=[],n.version="3.0";
        var t=document.createElement("script");
        t.async=!0,t.src=e;
        var r=document.getElementsByTagName("script")[0];
        r.parentNode.insertBefore(t,r)}}("https://s.pinimg.com/ct/core.js");
        pintrk('load', "{{ get_setting('pinterest_tag_id') }}");
        pintrk('page');
    </script>
@endif

```

---

## 🤨 AI Plugin Support

SEO Panel includes support for OpenAI-powered features, including:

* ✍️ SEO meta title/description generation
* 🔍 Keyword suggestions
* 📜 Content generation (blog snippets, product descriptions, etc.)
* 🤖 Custom plugins (you can create your own)

> All AI settings can be configured via admin dashboard.

---

## 📦 Deployment

For production build:

```bash
npm run build
php artisan config:cache
php artisan optimize:clear
```

Recommended tools:

* **Supervisor** for running queue workers
* **Redis** (if using for cache/queue)
* **SSL** (HTTPS required for some pixels)

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](./LICENSE) file for details.

---

## 👨‍💼 Developer Notes

* Make sure to run `php artisan optimize:clear` after changing config values.
* Pixels only render if enabled and ID is present.
* You can customize AI structure under `/AI Settings`.
* You can customize plugin structure under `/Social Integration`.
* You can customize blogs structure under `/Blogs System`.
* You can customize offers structure under `/Offers System`.
* You can customize Items structure under `/Items System`.
* You can customize Jobs structure under `/Jobs System`.
* You can customize Contact Us structure under `/Contact Us`.
* You can customize Subscribes structure under `/Subscribes`.
* You can customize Custom Pages structure under `/Custom Pages`.

---

## 📬 Contact

For support or business inquiries:

* 🌐 Website: [https://my-compound.com/](https://my-compound.com/)
* 📧 Email: [mohamedsaeed00451@gmail.com](mailto:mohamedsaeed00451@gmail.com)
* 💼 GitHub: [github.com/mohamedsaeed00451](https://github.com/mohamedsaeed00451)

---

**Made with Promp-Tech by Mohamed Saeed**
# rehltna
